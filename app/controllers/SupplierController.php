<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Supplier.php';

class SupplierController extends Controller
{
    private Supplier $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new Supplier();
    }

    public function index(): void
    {
        $this->requireLogin();

        $this->view('suppliers/index', [
            'title' => 'Suppliers',
            'suppliers' => $this->supplierModel->all(),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('suppliers/form', [
                    'title' => 'Create Supplier',
                    'mode' => 'create',
                    'error' => 'Invalid security token',
                    'supplier' => $_POST,
                ]);
                return;
            }

            $this->supplierModel->create($_POST);
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=supplier');
        }

        $this->view('suppliers/form', [
            'title' => 'Create Supplier',
            'mode' => 'create',
            'supplier' => [],
        ]);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $supplier = $this->supplierModel->find($id);

        if (!$supplier) {
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=supplier');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('suppliers/form', [
                    'title' => 'Edit Supplier',
                    'mode' => 'edit',
                    'error' => 'Invalid security token',
                    'supplier' => $supplier,
                ]);
                return;
            }

            $this->supplierModel->update($id, $_POST);
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=supplier');
        }

        $this->view('suppliers/form', [
            'title' => 'Edit Supplier',
            'mode' => 'edit',
            'supplier' => $supplier,
        ]);
    }

    public function delete(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->supplierModel->delete($id);
        }

        $this->redirect('/itrack-zimbabwe/public/index.php?controller=supplier');
    }
}
