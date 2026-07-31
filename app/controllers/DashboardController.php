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

    private function getRoleModules(string $role): array
    {
        $allModules = [
            ['label' => 'Inventory', 'route' => 'inventory', 'icon' => 'fa-boxes-stacked', 'roles' => ['Administrator','Procurement Officer','Store Officer','Staff']],
            ['label' => 'Clients', 'route' => 'clients', 'icon' => 'fa-handshake-angle', 'roles' => ['Administrator','Sales Officer','Finance Officer','Staff']],
            ['label' => 'Suppliers', 'route' => 'supplier', 'icon' => 'fa-truck-fast', 'roles' => ['Administrator','Procurement Officer','Store Officer']],
            ['label' => 'Users', 'route' => 'users', 'icon' => 'fa-users', 'roles' => ['Administrator','Director','Finance Officer']],
            ['label' => 'GPS Devices', 'route' => 'gps', 'icon' => 'fa-location-dot', 'roles' => ['Administrator','Technician','Store Officer']],
            ['label' => 'Accounting', 'route' => 'accounting', 'icon' => 'fa-file-invoice-dollar', 'roles' => ['Administrator','Finance Officer']],
            ['label' => 'Purchases', 'route' => 'purchases', 'icon' => 'fa-cart-shopping', 'roles' => ['Administrator','Procurement Officer','Store Officer']],
            ['label' => 'Sales', 'route' => 'sales', 'icon' => 'fa-chart-line', 'roles' => ['Administrator','Sales Officer']],
            ['label' => 'Requisitions', 'route' => 'requisition', 'icon' => 'fa-clipboard-list', 'roles' => ['Administrator','Procurement Officer','Store Officer']],
            ['label' => 'Reports', 'route' => 'reports', 'icon' => 'fa-file-lines', 'roles' => ['Administrator','Finance Officer','Director']],
            ['label' => 'Notifications', 'route' => 'notification', 'icon' => 'fa-bell', 'roles' => ['Administrator','Director','Finance Officer','Procurement Officer','Store Officer','Sales Officer','Technician','Staff']],
            ['label' => 'Settings', 'route' => 'settings', 'icon' => 'fa-gear', 'roles' => ['Administrator','Director']],
        ];

        return array_filter($allModules, fn($module) => in_array($role, $module['roles'], true));
    }

    public function index(): void
    {
        $this->requireLogin();

        $role = $_SESSION['user']['role'] ?? 'Staff';
        $summary = [
            'users' => $this->userModel->dashboardSummary()['total_users'] ?? 0,
            'products' => $this->productModel->countProducts(),
            'clients' => $this->clientModel->countClients(),
            'gps_devices' => $this->gpsModel->countDevices(),
            'low_stock' => $this->productModel->lowStockCount(),
            'inventory_value' => $this->productModel->inventoryValue(),
        ];

        $modules = $this->getRoleModules($role);

        $this->view('dashboard/index', [
            'summary' => $summary,
            'modules' => $modules,
            'role' => $role,
        ]);
    }
}
