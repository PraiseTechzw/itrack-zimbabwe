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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('inventory/form', ['error' => 'Invalid security token']);
                return;
            }
            $productName = trim((string) ($_POST['name'] ?? ''));
            $productId = (int) $this->productModel->create($_POST);
            if ($productId > 0 && (int) ($_POST['opening_stock'] ?? 0) <= (int) ($_POST['reorder_level'] ?? 0)) {
                notifyTeam(['Administrator', 'Store Officer', 'Procurement Officer'], 'Low stock alert', 'Product ' . $productName . ' is at or below its reorder level.');
            }
            $this->redirect('/index.php?controller=inventory');
        }

        $this->view('inventory/form', ['title' => 'Create Product', 'mode' => 'create']);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $product = $this->productModel->find($id);
        if (!$product) {
            $this->redirect('/index.php?controller=inventory');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('inventory/form', ['product' => $product, 'error' => 'Invalid security token']);
                return;
            }
            $productName = trim((string) ($_POST['name'] ?? ''));
            $this->productModel->update($id, $_POST);
            if ((int) ($_POST['opening_stock'] ?? 0) <= (int) ($_POST['reorder_level'] ?? 0)) {
                notifyTeam(['Administrator', 'Store Officer', 'Procurement Officer'], 'Low stock alert', 'Product ' . $productName . ' is at or below its reorder level.');
            }
            $this->redirect('/index.php?controller=inventory');
        }

        $this->view('inventory/form', ['title' => 'Edit Product', 'mode' => 'edit', 'product' => $product]);
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
