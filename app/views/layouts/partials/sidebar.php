<?php if (isLoggedIn()): ?>
    <?php
    $currentController = strtolower($_GET['controller'] ?? 'dashboard');
    $scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptName === 'dashboard.php') {
        $currentController = 'dashboard';
    }
    $isActive = fn(string $name): string => $currentController === strtolower($name) ? 'active' : '';
    ?>
    <nav class="col-md-3 col-lg-2 d-md-block sidebar">
        <div class="top-profile">
            <div class="brand-title">iTrack Zimbabwe</div>
            <p class="mb-1">Welcome back,</p>
            <span><?= htmlspecialchars(currentUser()['name'] ?? 'User') ?></span>
            <div class="status"><i class="fa-solid fa-circle text-success"></i> Online</div>
        </div>
        <div class="px-3">
            <div class="sidebar-item mb-2">
                <a class="nav-link <?= $isActive('dashboard') ?>" href="/itrack-zimbabwe/public/dashboard.php"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</a>
            </div>
        </div>
        <div class="sidebar-section px-3">
            <div class="nav-title">Management</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link <?= $isActive('inventory') ?>" href="/itrack-zimbabwe/public/index.php?controller=inventory"><i class="fa-solid fa-boxes-stacked me-2"></i>Inventory</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('clients') ?>" href="/itrack-zimbabwe/public/index.php?controller=clients"><i class="fa-solid fa-handshake-angle me-2"></i>Clients</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('supplier') ?>" href="/itrack-zimbabwe/public/index.php?controller=supplier"><i class="fa-solid fa-truck-fast me-2"></i>Suppliers</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('users') ?>" href="/itrack-zimbabwe/public/index.php?controller=users"><i class="fa-solid fa-users me-2"></i>Users</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('gps') ?>" href="/itrack-zimbabwe/public/index.php?controller=gps"><i class="fa-solid fa-location-dot me-2"></i>GPS Devices</a></li>
            </ul>
        </div>
        <div class="sidebar-section px-3">
            <div class="nav-title">Operations</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link <?= $isActive('accounting') ?>" href="/itrack-zimbabwe/public/index.php?controller=accounting"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Accounting</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('purchases') ?>" href="/itrack-zimbabwe/public/index.php?controller=purchases"><i class="fa-solid fa-cart-shopping me-2"></i>Purchases</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('sales') ?>" href="/itrack-zimbabwe/public/index.php?controller=sales"><i class="fa-solid fa-chart-line me-2"></i>Sales</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('requisition') ?>" href="/itrack-zimbabwe/public/index.php?controller=requisition"><i class="fa-solid fa-clipboard-list me-2"></i>Requisitions</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('reports') ?>" href="/itrack-zimbabwe/public/index.php?controller=reports"><i class="fa-solid fa-file-lines me-2"></i>Reports</a></li>
            </ul>
        </div>
        <div class="sidebar-section px-3">
            <div class="nav-title">Support</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item"><a class="nav-link <?= $isActive('notification') ?>" href="/itrack-zimbabwe/public/index.php?controller=notification"><i class="fa-solid fa-bell me-2"></i>Notifications</a></li>
                <li class="nav-item"><a class="nav-link <?= $isActive('settings') ?>" href="/itrack-zimbabwe/public/index.php?controller=settings"><i class="fa-solid fa-gear me-2"></i>Settings</a></li>
            </ul>
        </div>
        <div class="px-3 mt-4">
            <a class="btn btn-outline-light w-100" href="/itrack-zimbabwe/public/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
        </div>
    </nav>
<?php endif; ?>
