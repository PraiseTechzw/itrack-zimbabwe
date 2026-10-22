<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Product.php';

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
            $needsIdentifier = in_array($category, ['Tracker', 'FLS', 'Cameras'], true);
            $duplicateIdentifier = $identifier !== '' && $this->productModel->imeiExists($identifier);
            $invalidTrackedQuantity = $needsIdentifier && (int)($_POST['quantity'] ?? 0) > 1;
            if (!isset($types[$category]) || !in_array($type, $types[$category] ?? [], true) || !preg_match('/^\\d*$/', $identifier) || ($needsIdentifier && $identifier === '') || $duplicateIdentifier || $invalidTrackedQuantity || (int) ($_POST['quantity'] ?? 0) < 1) {
                $this->view('inventory/form', [
                    'title' => 'Create Product',
                    'mode' => 'create',
                    'product' => $_POST,
                    'deviceTypes' => $deviceTypes,
                    'itemTypes' => $types,
                    'error' => $duplicateIdentifier ? 'That serial number / IMEI is already registered.' : ($invalidTrackedQuantity ? 'Serialized trackers, FLS units, and cameras must be added one unit per inventory row.' : ($needsIdentifier && $identifier === '' ? 'Enter the IMEI or serial number for this inventory item.' : 'Choose a valid item and type, enter a positive quantity, and use digits only for the serial / IMEI.')),
                ]);
                return;
            }
            $this->productModel->create($_POST);
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
            $rows = array_values(array_filter($_POST['items'] ?? [], static fn($row) => is_array($row) && (trim((string)($row['item_type'] ?? '')) !== '' || (int)($row['quantity'] ?? 0) > 0)));
            $valid = count($rows) > 0 && count($rows) <= 50;
            $identifiers = [];
            foreach ($rows as $row) {
                $category = trim((string)($row['item_category'] ?? ''));
                $type = trim((string)($row['item_type'] ?? ''));
                $identifier = trim((string)($row['identifier'] ?? ''));
                $needsIdentifier = in_array($category, ['Tracker', 'FLS', 'Cameras'], true);
                if (!in_array($type, $types[$category] ?? [], true) || (int)($row['quantity'] ?? 0) < 1 || ($needsIdentifier && ((int)($row['quantity'] ?? 0) !== 1 || $identifier === '')) || !preg_match('/^\\d*$/', $identifier) || ($identifier !== '' && (isset($identifiers[$identifier]) || $this->productModel->imeiExists($identifier))) || !is_numeric($row['cost_price'] ?? 0) || !is_numeric($row['selling_price'] ?? 0) || (float)($row['cost_price'] ?? 0) < 0 || (float)($row['selling_price'] ?? 0) < 0) $valid = false;
                if ($identifier !== '') $identifiers[$identifier] = true;
            }
            if (!$valid) {
                $this->view('inventory/bulk', ['itemTypes' => $types, 'rows' => $_POST['items'] ?? [], 'error' => 'Add 1–50 valid rows. Trackers, FLS units, and cameras need a unique numeric serial / IMEI and quantity 1.']);
                return;
            }
            foreach ($rows as &$row) $row['unit'] = trim((string)($row['unit'] ?? 'pcs')) ?: 'pcs';
            unset($row);
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
            $needsIdentifier = in_array($category, ['Tracker', 'FLS', 'Cameras'], true);
            $invalidTrackedQuantity = $needsIdentifier && (int)($_POST['quantity'] ?? 0) > 1;
            if (!in_array($type, Product::itemTypes()[$category] ?? [], true) || !preg_match('/^\\d*$/', $identifier) || ($needsIdentifier && $identifier === '') || $invalidTrackedQuantity || ($identifier !== '' && $this->productModel->imeiExists($identifier, $id))) {
                $this->view('inventory/form', [
                    'title' => 'Edit Product',
                    'mode' => 'edit',
                    'product' => array_merge($product, $_POST),
                    'deviceTypes' => $deviceTypes,
                    'error' => $invalidTrackedQuantity ? 'Serialized trackers, FLS units, and cameras must be one unit per inventory row.' : 'Choose a valid item/type. Trackers, FLS units, and cameras require a unique numeric serial / IMEI.', 'itemTypes' => Product::itemTypes(),
                ]);
                return;
            }
            $productName = trim((string) ($_POST['device_type'] ?? ''));
            $this->productModel->update($id, $_POST);
            $updatedProduct = $this->productModel->find($id);
            if ($updatedProduct !== null && (int) ($updatedProduct['opening_stock'] ?? 0) <= (int) ($updatedProduct['reorder_level'] ?? 0)) {
                notifyTeam(['Administrator', 'Store Officer', 'Procurement Officer'], 'Low stock alert', 'Product ' . $productName . ' is at or below its reorder level.');
            }
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
