<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/services/XlsxReport.php';

class InventoryController extends Controller
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function index(): void
    {
        $this->requireModuleAccess('inventory', ['Administrator','Procurement Officer','Store Officer','Staff']);
        $this->view('inventory/index', ['products' => $this->productModel->all()]);
    }

    public function export(): void
    {
        $this->requireModuleAccess('inventory', ['Administrator','Procurement Officer','Store Officer','Staff']);
        $rows = array_map(static fn(array $product): array => [
            'inventory_id' => (int)$product['id'],
            'category' => (string)($product['item_category'] ?? ''),
            'item_type' => (string)($product['item_type'] ?? $product['device_type'] ?? ''),
            'serial_imei_sim' => (string)($product['imei'] ?? $product['sim_number'] ?? ''),
            'sku' => (string)($product['sku'] ?? ''),
            'unit' => (string)($product['unit'] ?? 'pcs'),
            'quantity_in_stock' => (int)($product['opening_stock'] ?? 0),
            'cost_price' => (float)($product['cost_price'] ?? 0),
            'selling_price' => (float)($product['selling_price'] ?? 0),
            'reorder_level' => (int)($product['reorder_level'] ?? 0),
            'status' => (string)($product['status'] ?? ''),
        ], $this->productModel->all());
        $workbook = (new XlsxReport())->build($rows, 'Inventory');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="itrack-inventory.xlsx"');
        header('Content-Length: ' . strlen($workbook));
        echo $workbook;
    }

    public function create(): void
    {
        $this->requireLogin();
        $deviceTypes = Product::deviceTypes();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('inventory/form', ['title' => 'Create Product', 'mode' => 'create', 'product' => $_POST, 'deviceTypes' => $deviceTypes, 'error' => 'Invalid security token']);
                return;
            }
            $category = trim((string) ($_POST['item_category'] ?? ''));
            $types = Product::itemTypes();
            $type = trim((string) ($_POST['item_type'] ?? ''));
            $identifier = trim((string) ($_POST['identifier'] ?? ''));
            $needsIdentifier = in_array($category, ['FLS', 'Cameras', 'Sim cards'], true);
            $duplicateIdentifier = $identifier !== '' && $this->productModel->imeiExists($identifier);
            if (!isset($types[$category]) || !in_array($type, $types[$category] ?? [], true) || !preg_match('/^\\d*$/', $identifier) || ($needsIdentifier && $identifier === '') || $duplicateIdentifier) {
                $this->view('inventory/form', [
                    'title' => 'Create Product',
                    'mode' => 'create',
                    'product' => $_POST,
                    'deviceTypes' => $deviceTypes,
                    'itemTypes' => $types,
                    'error' => $duplicateIdentifier ? 'That serial number / IMEI is already registered.' : ($needsIdentifier && $identifier === '' ? 'Enter the unique numeric serial, IMEI, or SIM line number for this item.' : 'Choose a valid item and type, and use digits only for the serial / IMEI.'),
                ]);
                return;
            }
            $data = $_POST;
            $data['quantity'] = 1;
            $this->productModel->create($data);
            $this->redirect('/index.php?controller=inventory');
        }

        $this->view('inventory/form', ['title' => 'Create Product', 'mode' => 'create', 'deviceTypes' => $deviceTypes, 'itemTypes' => Product::itemTypes()]);
    }

    public function createBulk(): void
    {
        $this->requireModuleAccess('inventory', ['Administrator','Procurement Officer','Store Officer','Staff']);
        $types = Product::itemTypes();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('inventory/bulk', ['itemTypes' => $types, 'rows' => $_POST['items'] ?? [], 'error' => 'Invalid security token']);
                return;
            }
            $incomingRows = $_POST['items'] ?? [];
            if (!is_array($incomingRows)) {
                $this->view('inventory/bulk', ['itemTypes' => $types, 'rows' => [], 'error' => 'Submit valid inventory rows.']);
                return;
            }
            $rows = array_values(array_filter($incomingRows, static fn($row) => is_array($row) && (trim((string)($row['item_type'] ?? '')) !== '' || (int)($row['quantity'] ?? 0) > 0)));
            $maximumRows = 50;
            $valid = count($rows) > 0 && count($rows) <= $maximumRows;
            $identifiers = [];
            foreach ($rows as &$row) {
                $categoryValue = strtolower(trim((string)($row['item_category'] ?? '')));
                $row['item_category'] = match ($categoryValue) {
                    'fls' => 'FLS',
                    'camera', 'cameras' => 'Cameras',
                    'sim card', 'sim cards', 'sim cards / lines', 'lines', 'line' => 'Sim cards',
                    'tracker', 'trackers' => 'Tracker',
                    default => trim((string)($row['item_category'] ?? '')),
                };
                $category = trim((string)($row['item_category'] ?? ''));
                $type = trim((string)($row['item_type'] ?? ''));
                $identifier = trim((string)($row['identifier'] ?? ''));
                $needsIdentifier = in_array($category, ['FLS', 'Cameras', 'Sim cards'], true);
                $quantity = (int)($row['quantity'] ?? 0);
                if (!in_array($type, $types[$category] ?? [], true) || $quantity < 1 || ($needsIdentifier && ($quantity !== 1 || $identifier === '')) || ($identifier !== '' && $quantity !== 1) || !preg_match('/^\\d*$/', $identifier) || ($identifier !== '' && (isset($identifiers[$identifier]) || $this->productModel->imeiExists($identifier))) || !is_numeric($row['cost_price'] ?? 0) || !is_numeric($row['selling_price'] ?? 0) || (float)($row['cost_price'] ?? 0) < 0 || (float)($row['selling_price'] ?? 0) < 0) $valid = false;
                if ($identifier !== '') $identifiers[$identifier] = true;
                $row['unit'] = trim((string)($row['unit'] ?? 'pcs')) ?: 'pcs';
            }
            unset($row);
            if (!$valid) {
                $this->view('inventory/bulk', ['itemTypes' => $types, 'rows' => $_POST['items'] ?? [], 'error' => 'Add 1–' . $maximumRows . ' valid rows. FLS, cameras, and SIM lines require a unique numeric identifier and quantity 1. Use a valid item category/type and non-negative prices.']);
                return;
            }
            $this->productModel->createMany($rows, []);
            $this->redirect('/index.php?controller=inventory');
            return;
        }
        $this->view('inventory/bulk', ['itemTypes' => $types]);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $deviceTypes = Product::deviceTypes();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $product = $this->productModel->find($id);
        if (!$product) {
            $this->redirect('/index.php?controller=inventory');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('inventory/form', ['title' => 'Edit Product', 'mode' => 'edit', 'product' => array_merge($product, $_POST), 'deviceTypes' => $deviceTypes, 'error' => 'Invalid security token']);
                return;
            }
            $category = trim((string) ($_POST['item_category'] ?? ''));
            $type = trim((string) ($_POST['item_type'] ?? ''));
            $identifier = trim((string) ($_POST['identifier'] ?? ''));
            $needsIdentifier = in_array($category, ['FLS', 'Cameras', 'Sim cards'], true);
            if (!in_array($type, Product::itemTypes()[$category] ?? [], true) || !preg_match('/^\\d*$/', $identifier) || ($needsIdentifier && $identifier === '') || ($identifier !== '' && $this->productModel->imeiExists($identifier, $id))) {
                $this->view('inventory/form', [
                    'title' => 'Edit Product',
                    'mode' => 'edit',
                    'product' => array_merge($product, $_POST),
                    'deviceTypes' => $deviceTypes,
                    'error' => 'Choose a valid item/type. FLS, cameras, and SIM lines require a unique numeric identifier.', 'itemTypes' => Product::itemTypes(),
                ]);
                return;
            }
            $data = $_POST;
            $data['quantity'] = (int)($product['opening_stock'] ?? 0);
            $this->productModel->update($id, $data);
            $this->redirect('/index.php?controller=inventory');
        }

        $product['identifier'] = $product['imei'] ?? '';
        $product['quantity'] = $product['opening_stock'] ?? 0;
        $this->view('inventory/form', ['title' => 'Edit Product', 'mode' => 'edit', 'product' => $product, 'deviceTypes' => $deviceTypes, 'itemTypes' => Product::itemTypes()]);
    }

    public function delete(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->productModel->delete($id);
        }
        $this->redirect('/index.php?controller=inventory');
    }

    public function apiList(): void
    {
        $this->json(['products' => $this->productModel->all()]);
    }
}
