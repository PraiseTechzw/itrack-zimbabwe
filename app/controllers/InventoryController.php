<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/GPSDevice.php';
require_once dirname(__DIR__) . '/helpers/settings.php';
require_once dirname(__DIR__) . '/services/XlsxReport.php';

class InventoryController extends Controller
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    /** Shared validation for single-item create/edit so both paths enforce the same rules. */
    private static function itemInputError(string $category, string $type, string $identifier, bool $duplicate, array $data): ?string
    {
        $types = Product::itemTypes();
        if (!isset($types[$category]) || !in_array($type, $types[$category], true)) return 'Choose a valid item category and type.';
        $identifierError = Product::identifierError($category, $identifier);
        if ($identifierError !== null) return $identifierError;
        if ($duplicate) return 'That serial number / IMEI is already registered.';
        $cost = $data['cost_price'] ?? 0;
        $selling = $data['selling_price'] ?? 0;
        if ((!is_numeric($cost) && $cost !== '') || (float) $cost < 0
            || (!is_numeric($selling) && $selling !== '') || (float) $selling < 0) {
            return 'Enter valid non-negative cost and selling prices.';
        }
        try {
            Product::vatRate($data['vat_rate'] ?? null);
        } catch (InvalidArgumentException $exception) {
            return $exception->getMessage();
        }
        if (isset($data['reorder_level']) && $data['reorder_level'] !== ''
            && (!is_numeric($data['reorder_level']) || (int) $data['reorder_level'] < 0)) {
            return 'Reorder level must be zero or a positive whole number.';
        }
        return null;
    }

    public function index(): void
    {
        $this->requireModuleAccess('inventory', ['Administrator','Procurement Officer','Store Officer','Staff']);
        $defaultReorderLevel = setting_int('default_reorder_level', 5);
        $this->view('inventory/index', [
            'products' => $this->productModel->all(),
            'deinstallations' => (new GPSDevice())->deinstallations(),
            'lowStockItems' => setting_bool('low_stock_alerts_enabled') && setting_bool('low_stock_show_inventory')
                ? $this->productModel->lowStockItems($defaultReorderLevel, 10)
                : [],
            'defaultReorderLevel' => $defaultReorderLevel,
        ]);
    }

    public function export(): void
    {
        $this->requireModuleAccess('inventory', ['Administrator','Procurement Officer','Store Officer','Staff']);
        $installedQuantities = $this->productModel->installedQuantities();
        $rows = array_map(static fn(array $product): array => [
            'inventory_id' => (int)$product['id'],
            'category' => (string)($product['item_category'] ?? ''),
            'item_type' => (string)($product['item_type'] ?? $product['device_type'] ?? ''),
            'serial_imei_sim' => (string)($product['imei'] ?? $product['sim_number'] ?? ''),
            'sku' => (string)($product['sku'] ?? ''),
            'unit' => (string)($product['unit'] ?? 'pcs'),
            'quantity_in_stock' => (int)($product['opening_stock'] ?? 0),
            'quantity_installed' => (int)($installedQuantities[(int)$product['id']] ?? 0),
            'cost_price' => (float)($product['cost_price'] ?? 0),
            'selling_price' => (float)($product['selling_price'] ?? 0),
            'vat_rate' => (float)($product['vat_rate'] ?? 15),
            'selling_price_incl_vat' => round((float)($product['selling_price'] ?? 0) * (1 + (float)($product['vat_rate'] ?? 15) / 100), 2),
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
            $duplicateIdentifier = $identifier !== '' && $this->productModel->imeiExists($identifier);
            $error = self::itemInputError($category, $type, $identifier, $duplicateIdentifier, $_POST);
            if ($error !== null) {
                $this->view('inventory/form', [
                    'title' => 'Create Product',
                    'mode' => 'create',
                    'product' => $_POST,
                    'deviceTypes' => $deviceTypes,
                    'itemTypes' => $types,
                    'error' => $error,
                ]);
                return;
            }
            $data = $_POST;
            $data['quantity'] = 1;
            if (($data['reorder_level'] ?? '') === '') $data['reorder_level'] = setting_int('default_reorder_level', 5);
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
                    'hard drive', 'hard drives' => 'Hard drives',
                    'tracker', 'trackers' => 'Tracker',
                    default => trim((string)($row['item_category'] ?? '')),
                };
                $category = trim((string)($row['item_category'] ?? ''));
                $type = trim((string)($row['item_type'] ?? ''));
                $identifier = trim((string)($row['identifier'] ?? ''));
                $needsIdentifier = (bool) Product::identifierRules($category)['required'];
                // Every row is one physical device, regardless of any submitted quantity.
                $row['quantity'] = 1;
                $quantity = 1;
                try {
                    $vatRate = Product::vatRate($row['vat_rate'] ?? null);
                } catch (InvalidArgumentException) {
                    $vatRate = -1.0;
                }
                if (!in_array($type, $types[$category] ?? [], true)
                    || Product::identifierError($category, $identifier) !== null
                    || ($identifier !== '' && (isset($identifiers[$identifier]) || $this->productModel->imeiExists($identifier)))
                    || !is_numeric($row['cost_price'] ?? 0) || !is_numeric($row['selling_price'] ?? 0)
                    || (float)($row['cost_price'] ?? 0) < 0 || (float)($row['selling_price'] ?? 0) < 0 || $vatRate < 0
                    || (($row['reorder_level'] ?? '') !== '' && (!is_numeric($row['reorder_level']) || (int)$row['reorder_level'] < 0))) $valid = false;
                if ($identifier !== '') $identifiers[$identifier] = true;
                $row['unit'] = trim((string)($row['unit'] ?? 'pcs')) ?: 'pcs';
                $row['vat_rate'] = $vatRate >= 0 ? $vatRate : setting_float('default_vat_rate', 15);
                if (($row['reorder_level'] ?? '') === '') $row['reorder_level'] = setting_int('default_reorder_level', 5);
            }
            unset($row);
            if (!$valid) {
                $this->view('inventory/bulk', ['itemTypes' => $types, 'rows' => $_POST['items'] ?? [], 'error' => 'Add 1–' . $maximumRows . ' valid rows. Each row is one device. FLS, cameras, hard drives, and SIM lines require a unique identifier. SIM lines are limited to 10 digits. Use valid prices and a VAT rate between 0 and 100.']);
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
            $error = self::itemInputError($category, $type, $identifier, $identifier !== '' && $this->productModel->imeiExists($identifier, $id), $_POST);
            if ($error !== null) {
                $this->view('inventory/form', [
                    'title' => 'Edit Product',
                    'mode' => 'edit',
                    'product' => array_merge($product, $_POST),
                    'deviceTypes' => $deviceTypes,
                    'error' => $error,
                    'itemTypes' => Product::itemTypes(),
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
