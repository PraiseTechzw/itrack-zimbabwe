<?php if (isLoggedIn()):
require_once dirname(__DIR__, 3) . '/models/Permission.php';
$currentController = strtolower($_GET['controller'] ?? 'dashboard');
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'dashboard.php') $currentController = 'dashboard';
$role = currentUser()['role'] ?? 'Staff';
$role = $role === 'admin' ? 'Administrator' : $role;
$permissionModel = new Permission();
$permissionModel->ensureModulePermissions();
$customModules = $permissionModel->modulesForRole($role);
$navModules = [
    ['label'=>'Dashboard','route'=>'dashboard','icon'=>'fa-gauge-high','roles'=>['Administrator','Director','Finance Officer','Procurement Officer','Store Officer','Sales Officer','Technician','Staff'],'group'=>'Workspace'],
    ['label'=>'Permissions','route'=>'permissions','icon'=>'fa-shield-halved','roles'=>['Administrator'],'group'=>'Support'],
];
foreach ($permissionModel->moduleDefinitions() as $module) {
    $navModules[] = [
        'label' => $module['label'],
        'route' => $module['route'],
        'icon' => $module['icon'],
        'roles' => $module['default_roles'],
        'group' => $module['group'],
    ];
}
$grouped = [];
foreach ($navModules as $module) {
    if (!in_array($role, $module['roles'], true)) continue;
    if ($module['route'] !== 'dashboard' && $module['route'] !== 'permissions' && $customModules && !in_array($module['route'], $customModules, true)) continue;
    $grouped[$module['group']][] = $module;
}
$user = currentUser() ?? [];
$initials = strtoupper(substr((string)($user['name'] ?? 'U'), 0, 1));
?>
<aside class="sidebar" aria-label="Primary navigation">
    <div class="brand"><div class="brand-mark"><i class="fa-solid fa-route"></i></div><div><div class="brand-title">iTrack<br>Zimbabwe</div><div class="brand-subtitle">Operations OS</div></div></div>
    <div class="profile-chip"><div class="d-flex align-items-center gap-2"><div class="avatar"><?= htmlspecialchars($initials) ?></div><div class="min-w-0"><div class="profile-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div><div class="profile-role"><span class="status-dot"></span><?= htmlspecialchars($role) ?></div></div></div></div>
    <?php foreach ($grouped as $group => $items): ?><div class="nav-title"><?= htmlspecialchars($group) ?></div><ul class="nav flex-column px-1"><?php foreach ($items as $item): ?><li class="nav-item"><a class="nav-link <?= $currentController === strtolower($item['route']) ? 'active' : '' ?>" href="<?= $item['route'] === 'dashboard' ? '/dashboard.php' : '/index.php?controller=' . urlencode($item['route']) ?>"><i class="fa-solid <?= htmlspecialchars($item['icon']) ?>"></i><span><?= htmlspecialchars($item['label']) ?></span></a></li><?php endforeach; ?></ul><?php endforeach; ?>
    <div class="sidebar-footer"><a class="nav-link" href="/logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Sign out</span></a></div>
</aside>
<?php endif; ?>
