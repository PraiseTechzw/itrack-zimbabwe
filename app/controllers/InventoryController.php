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
            $rawImeis = trim((string) ($_POST['imeis'] ?? $_POST['imei'] ?? ''));
            $imeis = preg_split('/[\\s,;]+/', $rawImeis, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $imeis = array_values(array_unique(array_map('trim', $imeis)));
            $deviceType = trim((string) ($_POST['device_type'] ?? ''));
            $duplicateImeis = [];
            foreach ($imeis as $imei) {
                if ($this->productModel->imeiExists($imei)) {
                    $duplicateImeis[] = $imei;
                }
            }
            $hasInputDuplicates = count($imeis) !== count(preg_split('/[\\s,;]+/', $rawImeis, -1, PREG_SPLIT_NO_EMPTY) ?: []);
            if (!in_array($deviceType, $deviceTypes, true) || count($imeis) === 0 || $hasInputDuplicates || $duplicateImeis !== []) {
                $this->view('inventory/form', [
                    'title' => 'Create Product',
                    'mode' => 'create',
                    'product' => $_POST,
                    'deviceTypes' => $deviceTypes,
                    'error' => !in_array($deviceType, $deviceTypes, true)
                        ? 'Choose a valid device type.'
                        : (count($imeis) === 0 ? 'Enter at least one device IMEI.'
                            : ($hasInputDuplicates ? 'The IMEI list contains duplicates. Each device must have a unique IMEI.'
                                : ('Already registered IMEI' . (count($duplicateImeis) === 1 ? '' : 's') . ': ' . implode(', ', $duplicateImeis)))),
                ]);
                return;
            }
            $this->productModel->createMany($imeis, $_POST);
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
