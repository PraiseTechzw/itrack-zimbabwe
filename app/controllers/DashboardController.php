<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/models/Client.php';
require_once dirname(__DIR__) . '/models/GPSDevice.php';
require_once dirname(__DIR__) . '/models/Permission.php';
require_once dirname(__DIR__) . '/models/AppSetting.php';
require_once dirname(__DIR__) . '/helpers/settings.php';
require_once dirname(__DIR__) . '/services/LowStockAlerter.php';

class DashboardController extends Controller
{
    private User $userModel;
    private Product $productModel;
    private Client $clientModel;
    private GPSDevice $gpsModel;
    private Permission $permissionModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->clientModel = new Client();
        $this->gpsModel = new GPSDevice();
        $this->permissionModel = new Permission();
    }

    private function getRoleModules(string $role): array
    {
        $this->permissionModel->ensureModulePermissions();
        $customModules = $this->permissionModel->modulesForRole($role);
        $allModules = [];

        foreach ($this->permissionModel->moduleDefinitions() as $module) {
            $allModules[] = [
                'label' => $module['label'],
                'route' => $module['route'],
                'icon' => $module['icon'],
                'roles' => $module['default_roles'],
            ];
        }

        if ($customModules) {
            return array_values(array_filter($allModules, fn($module) => in_array($module['route'], $customModules, true)));
        }

        return array_values(array_filter($allModules, fn($module) => in_array($role, $module['roles'], true)));
    }

    public function index(): void
    {
        $this->requireLogin();

        $role = $_SESSION['user']['role'] ?? 'Staff';
        $role = $role === 'admin' ? 'Administrator' : $role;
        $settings = (new AppSetting())->all();
        $reorderLevel = (int)($settings['default_reorder_level'] ?? 5);
        $lowStockItems = setting_bool('low_stock_alerts_enabled')
            ? $this->productModel->lowStockItems($reorderLevel, 8)
            : [];
        if (setting_bool('low_stock_alerts_enabled') && $lowStockItems !== []) {
            LowStockAlerter::maybeSend($settings, $this->productModel->lowStockItems($reorderLevel, 50));
        }
        $summary = [
            'users' => $this->userModel->dashboardSummary()['total_users'] ?? 0,
            'products' => $this->productModel->countProducts(),
            'clients' => $this->clientModel->countClients(),
            'gps_devices' => $this->gpsModel->countDevices(),
            'low_stock' => setting_bool('low_stock_alerts_enabled') ? $this->productModel->lowStockCount($reorderLevel) : 0,
            'inventory_value' => $this->productModel->inventoryValue(),
        ];

        $modules = $this->getRoleModules($role);
        $this->view('dashboard/index', [
            'summary' => $summary,
            'modules' => $modules,
            'role' => $role,
            'lowStockItems' => setting_bool('low_stock_show_dashboard') ? $lowStockItems : [],
            'settingsUrl' => '/index.php?controller=settings',
        ]);
    }
}
