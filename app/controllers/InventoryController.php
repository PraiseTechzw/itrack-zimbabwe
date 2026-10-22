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
            $duplicateIdentifier = $identifier !== '' && $this->productModel->imeiExists($identifier);
            if (!isset($types[$category]) || !in_array($type, $types[$category] ?? [], true) || !preg_match('/^\\d*$/', $identifier) || $duplicateIdentifier || (int) ($_POST['quantity'] ?? 0) < 1) {
                $this->view('inventory/form', [
                    'title' => 'Create Product',
                    'mode' => 'create',
                    'product' => $_POST,
                    'deviceTypes' => $deviceTypes,
                    'itemTypes' => $types,
                    'error' => $duplicateIdentifier ? 'That serial number / IMEI is already registered.' : 'Choose a valid item and type, enter a positive quantity, and use digits only for the optional serial / IMEI.',
                ]);
                return;
            }
            $this->productModel->create($_POST);
            $this->redirect('/index.php?controller=inventory');
        }

        $this->view('inventory/form', ['title' => 'Create Product', 'mode' => 'create', 'deviceTypes' => $deviceTypes, 'itemTypes' => Product::itemTypes()]);
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
            if (!in_array($type, Product::itemTypes()[$category] ?? [], true) || !preg_match('/^\\d*$/', $identifier) || ($identifier !== '' && $this->productModel->imeiExists($identifier, $id))) {
                $this->view('inventory/form', [
                    'title' => 'Edit Product',
                    'mode' => 'edit',
                    'product' => array_merge($product, $_POST),
                    'deviceTypes' => $deviceTypes,
                    'error' => 'Choose a valid item/type and enter digits only for an optional identifier. Identifiers must be unique.', 'itemTypes' => Product::itemTypes(),
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
