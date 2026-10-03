<?php
$headerUser = currentUser() ?? [];
$headerName = $headerUser['name'] ?? 'User';
$headerInitial = strtoupper(substr((string)$headerName, 0, 1));
$headerController = strtolower($_GET['controller'] ?? 'dashboard');
$headerLabels = ['dashboard'=>'Overview','inventory'=>'Inventory','clients'=>'Clients','supplier'=>'Suppliers','users'=>'Users','gps'=>'GPS Devices','accounting'=>'Accounting','purchases'=>'Purchases','sales'=>'Sales','requisition'=>'Requisitions','reports'=>'Reports','notification'=>'Notifications','settings'=>'Settings'];
$headerSection = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'dashboard.php' ? 'Overview' : ($headerLabels[$headerController] ?? ucfirst($headerController));
?>
<header class="topbar">
    <button class="menu-toggle" type="button" aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button>
    <div class="crumb"><span class="d-none d-sm-inline">Workspace</span><i class="fa-solid fa-chevron-right mx-2" style="font-size:9px;color:#aab4c4"></i><strong><?= htmlspecialchars($headerSection) ?></strong></div>
    <div class="top-actions">
        <a class="icon-button" href="/index.php?controller=notification" aria-label="Notifications"><i class="fa-regular fa-bell"></i><span class="notification-badge"></span></a>
        <a class="icon-button d-none d-sm-grid" href="/index.php?controller=settings" aria-label="Settings"><i class="fa-solid fa-gear"></i></a>
        <div class="user-pill"><div class="avatar"><?= htmlspecialchars($headerInitial) ?></div><span><?= htmlspecialchars($headerName) ?></span><a class="icon-button border-0 ms-1" href="/logout.php" aria-label="Sign out"><i class="fa-solid fa-arrow-right-from-bracket"></i></a></div>
    </div>
</header>
