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
        $this->requireLogin();
        $this->view('inventory/index', ['products' => $this->productModel->all()]);
    }

    public function create(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->productModel->create($_POST);
            $this->redirect('/itrack-zimbabwe/public/dashboard.php');
        }
        $this->view('inventory/form');
    }

    public function apiList(): void
    {
        $this->json(['products' => $this->productModel->all()]);
    }
}
