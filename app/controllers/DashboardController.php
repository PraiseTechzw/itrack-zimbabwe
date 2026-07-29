<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Product.php';

class DashboardController extends Controller
{
    private User $userModel;
    private Product $productModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
    }

    public function index(): void
    {
        $this->requireLogin();
        $summary = [
            'users' => $this->userModel->dashboardSummary()['total_users'] ?? 0,
            'products' => $this->productModel->countProducts(),
            'low_stock' => $this->productModel->lowStockCount(),
            'inventory_value' => $this->productModel->inventoryValue(),
        ];

        $this->view('dashboard/index', ['summary' => $summary]);
    }
}
