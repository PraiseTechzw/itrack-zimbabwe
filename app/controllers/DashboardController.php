<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Client.php';
require_once dirname(__DIR__) . '/models/GPSDevice.php';

class DashboardController extends Controller
{
    private User $userModel;
    private Product $productModel;
    private Client $clientModel;
    private GPSDevice $gpsModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->clientModel = new Client();
        $this->gpsModel = new GPSDevice();
    }

    public function index(): void
    {
        $this->requireLogin();
        $summary = [
            'users' => $this->userModel->dashboardSummary()['total_users'] ?? 0,
            'products' => $this->productModel->countProducts(),
            'clients' => $this->clientModel->countClients(),
            'gps_devices' => $this->gpsModel->countDevices(),
            'low_stock' => $this->productModel->lowStockCount(),
            'inventory_value' => $this->productModel->inventoryValue(),
        ];

        $this->view('dashboard/index', ['summary' => $summary]);
    }
}
