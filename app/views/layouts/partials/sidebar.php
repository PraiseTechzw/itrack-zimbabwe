<?php if (isLoggedIn()): ?>
    <?php
    $currentController = strtolower($_GET['controller'] ?? 'dashboard');
    $scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptName === 'dashboard.php') {
        $currentController = 'dashboard';
    }
    $role = currentUser()['role'] ?? 'Staff';

    $modules = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fa-gauge-high', 'roles' => ['Administrator','Director','Finance Officer','Procurement Officer','Store Officer','Sales Officer','Technician','Staff'], 'group' => 'Dashboard'],
        ['label' => 'Inventory', 'route' => 'inventory', 'icon' => 'fa-boxes-stacked', 'roles' => ['Administrator','Procurement Officer','Store Officer','Staff'], 'group' => 'Management'],
        ['label' => 'Clients', 'route' => 'clients', 'icon' => 'fa-handshake-angle', 'roles' => ['Administrator','Sales Officer','Finance Officer','Staff'], 'group' => 'Management'],
        ['label' => 'Suppliers', 'route' => 'supplier', 'icon' => 'fa-truck-fast', 'roles' => ['Administrator','Procurement Officer','Store Officer'], 'group' => 'Management'],
        ['label' => 'Users', 'route' => 'users', 'icon' => 'fa-users', 'roles' => ['Administrator','Director','Finance Officer'], 'group' => 'Management'],
        ['label' => 'GPS Devices', 'route' => 'gps', 'icon' => 'fa-location-dot', 'roles' => ['Administrator','Technician','Store Officer'], 'group' => 'Management'],
        ['label' => 'Accounting', 'route' => 'accounting', 'icon' => 'fa-file-invoice-dollar', 'roles' => ['Administrator','Finance Officer'], 'group' => 'Operations'],
        ['label' => 'Purchases', 'route' => 'purchases', 'icon' => 'fa-cart-shopping', 'roles' => ['Administrator','Procurement Officer','Store Officer'], 'group' => 'Operations'],
        ['label' => 'Sales', 'route' => 'sales', 'icon' => 'fa-chart-line', 'roles' => ['Administrator','Sales Officer'], 'group' => 'Operations'],
        ['label' => 'Requisitions', 'route' => 'requisition', 'icon' => 'fa-clipboard-list', 'roles' => ['Administrator','Procurement Officer','Store Officer'], 'group' => 'Operations'],
        ['label' => 'Reports', 'route' => 'reports', 'icon' => 'fa-file-lines', 'roles' => ['Administrator','Finance Officer','Director'], 'group' => 'Operations'],
        ['label' => 'Notifications', 'route' => 'notification', 'icon' => 'fa-bell', 'roles' => ['Administrator','Director','Finance Officer','Procurement Officer','Store Officer','Sales Officer','Technician','Staff'], 'group' => 'Support'],
        ['label' => 'Settings', 'route' => 'settings', 'icon' => 'fa-gear', 'roles' => ['Administrator','Director'], 'group' => 'Support'],
    ];

    $active = fn(string $route): string => $currentController === strtolower($route) ? 'active' : '';

    $grouped = [];
    foreach ($modules as $module) {
        if (!in_array($role, $module['roles'], true)) {
            continue;
        }
        $grouped[$module['group']][] = $module;
    }
    ?>
    <nav class="col-md-3 col-lg-2 d-md-block sidebar">
        <div class="top-profile">
            <div class="brand-title">iTrack Zimbabwe</div>
            <p class="mb-1">Welcome back,</p>
            <span><?= htmlspecialchars(currentUser()['name'] ?? 'User') ?></span>
            <div class="status"><i class="fa-solid fa-circle text-success"></i> <?= htmlspecialchars($role) ?></div>
        </div>

        <?php foreach ($grouped as $group => $items): ?>
            <div class="sidebar-section">
                <div class="nav-title"><?= htmlspecialchars($group) ?></div>
                <ul class="nav flex-column px-3">
                    <?php foreach ($items as $item): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $active($item['route']) ?>" href="<?= $item['route'] === 'dashboard' ? '/itrack-zimbabwe/public/dashboard.php' : '/itrack-zimbabwe/public/index.php?controller=' . urlencode($item['route']) ?>">
                                <i class="fa-solid <?= htmlspecialchars($item['icon']) ?>"></i>
                                <?= htmlspecialchars($item['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <div class="px-3 mt-4">
            <a class="btn btn-outline-light w-100" href="/itrack-zimbabwe/public/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
        </div>
    </nav>
<?php endif; ?>
