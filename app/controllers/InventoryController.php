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
            $submittedImeis = $_POST['imeis'] ?? $_POST['imei'] ?? [];
            $simNumbers = $_POST['sim_numbers'] ?? [];
            $imeiRows = is_array($submittedImeis) ? $submittedImeis : (preg_split('/[\\s,;]+/', trim((string) $submittedImeis), -1, PREG_SPLIT_NO_EMPTY) ?: []);
            $tokens = $imeiRows;
            $tokens = array_values(array_filter(array_map(static fn ($value): string => trim((string) $value), $tokens), static fn ($value): bool => $value !== ''));
            $imeis = array_values(array_unique($tokens));
            $devices = [];
            foreach ($imeiRows as $index => $submittedImei) {
                $imei = trim((string) $submittedImei);
                if ($imei !== '') {
                    $devices[] = ['imei' => $imei, 'sim_number' => trim((string) ($simNumbers[$index] ?? ''))];
                }
            }
            $deviceType = trim((string) ($_POST['device_type'] ?? ''));
            $duplicateImeis = [];
            foreach ($imeis as $imei) {
                if ($this->productModel->imeiExists($imei)) {
                    $duplicateImeis[] = $imei;
                }
            }
            $hasInputDuplicates = count($imeis) !== count($tokens);
            $invalidImeis = array_filter($imeis, static fn ($imei): bool => !preg_match('/^\\d{15}$/', $imei));
            if (!in_array($deviceType, $deviceTypes, true) || count($imeis) === 0 || $hasInputDuplicates || $invalidImeis !== [] || $duplicateImeis !== []) {
                $error = !in_array($deviceType, $deviceTypes, true) ? 'Choose a valid device type.'
                    : (count($imeis) === 0 ? 'Enter at least one device IMEI.'
                        : ($hasInputDuplicates ? 'The IMEI list contains duplicates. Each device must have a unique IMEI.'
                            : ($invalidImeis !== [] ? 'Each IMEI must contain exactly 15 digits.'
                                : 'One or more IMEIs are already registered: ' . implode(', ', $duplicateImeis))));
                $this->view('inventory/form', [
                    'title' => 'Create Product',
                    'mode' => 'create',
                    'product' => $_POST,
                    'deviceTypes' => $deviceTypes,
                    'error' => $error,
                ]);
                return;
            }
            $this->productModel->createMany($devices, $_POST);
            $this->redirect('/index.php?controller=inventory');
        }

        $this->view('inventory/form', ['title' => 'Create Product', 'mode' => 'create', 'deviceTypes' => $deviceTypes]);
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
            $imei = trim((string) ($_POST['imei'] ?? ''));
            $deviceType = trim((string) ($_POST['device_type'] ?? ''));
            if (!in_array($deviceType, $deviceTypes, true) || $imei === '' || $this->productModel->imeiExists($imei, $id)) {
                $this->view('inventory/form', [
                    'title' => 'Edit Product',
                    'mode' => 'edit',
                    'product' => array_merge($product, $_POST),
                    'deviceTypes' => $deviceTypes,
                    'error' => !in_array($deviceType, $deviceTypes, true)
                        ? 'Choose a valid device type.'
                        : ($imei === '' ? 'Enter the device IMEI.' : 'That IMEI is already registered to another product.'),
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

        $this->view('inventory/form', ['title' => 'Edit Product', 'mode' => 'edit', 'product' => $product, 'deviceTypes' => $deviceTypes]);
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
