<?php
$selectedAccess = $matrix[$selectedRole] ?? [];
$moduleCount = count($modules ?? []);
$enabledCount = count($selectedAccess);
$groups = [];
foreach ($modules as $module) $groups[$module['group']][] = $module;
?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
    <div><div class="eyebrow mb-2">Administration / Access control</div><h1 class="page-title">Role permissions</h1><p class="page-subtitle">Shape each team’s workspace by choosing the modules they can access.</p></div>
    <div class="d-flex gap-2"><a class="btn btn-light" href="/index.php?controller=users"><i class="fa-solid fa-users me-2"></i>Manage users</a><button class="btn btn-primary" form="permission-form"><i class="fa-solid fa-floppy-disk me-2"></i>Save changes</button></div>
</div>
<?php if ($saved): ?><div class="alert alert-success d-flex align-items-center gap-2 mb-4"><i class="fa-solid fa-circle-check"></i><span>Permissions updated for <strong><?= htmlspecialchars($selectedRole) ?></strong>.</span></div><?php endif; ?>
<div class="row g-4">
    <div class="col-xl-3">
        <div class="card h-100"><div class="card-body"><div class="eyebrow mb-2">Choose a role</div><h2 class="h5 mb-3">Access profiles</h2><div class="vstack gap-2">
            <?php foreach ($roles as $role): $roleEnabled = count($matrix[$role] ?? []); ?><a class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none <?= $role === $selectedRole ? 'bg-light border border-primary-subtle' : 'border' ?>" href="/index.php?controller=permissions&role=<?= urlencode($role) ?>"><span class="module-icon" style="width:34px;height:34px"><i class="fa-solid <?= $role === 'Administrator' ? 'fa-crown' : 'fa-user-shield' ?>"></i></span><span class="min-w-0"><strong class="d-block text-dark small"><?= htmlspecialchars($role) ?></strong><small class="text-muted"><?= $roleEnabled ?> of <?= $moduleCount ?> modules</small></span><i class="fa-solid fa-chevron-right ms-auto text-muted" style="font-size:10px"></i></a><?php endforeach; ?>
        </div></div></div>
    </div>
    <div class="col-xl-9">
        <form id="permission-form" method="post" action="/index.php?controller=permissions&action=save">
            <?= $this->csrfField() ?><input type="hidden" name="role" value="<?= htmlspecialchars($selectedRole, ENT_QUOTES, 'UTF-8') ?>">
            <div class="card mb-4"><div class="card-body"><div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center"><div><div class="eyebrow mb-2">Editing profile</div><h2 class="h5 mb-1"><?= htmlspecialchars($selectedRole) ?></h2><p class="text-muted small mb-0">Changes apply to everyone assigned this role.</p></div><div class="text-md-end"><div class="metric-value" style="font-size:27px;margin:0;color:#4f46e5"><span id="enabled-count"><?= $enabledCount ?></span><span class="text-muted" style="font-size:14px;font-weight:600"> / <?= $moduleCount ?></span></div><div class="metric-meta">modules enabled</div></div></div></div></div>
            <?php foreach ($groups as $group => $items): ?><div class="card mb-3"><div class="card-body"><div class="section-heading mb-3"><div><div class="eyebrow mb-1"><?= htmlspecialchars($group) ?></div><h2 class="h6 mb-0">Workspace modules</h2></div><button class="btn btn-light btn-sm select-group" type="button" data-group="<?= htmlspecialchars($group) ?>">Toggle all</button></div><div class="row g-2"><?php foreach ($items as $module): $checked = in_array($module['key'], $selectedAccess, true); ?><div class="col-md-6"><label class="permission-option d-flex align-items-start gap-3 p-3 rounded-3 border <?= $checked ? 'is-enabled' : '' ?>"><input class="form-check-input mt-1 module-checkbox" type="checkbox" name="modules[]" value="<?= htmlspecialchars($module['key']) ?>" data-group="<?= htmlspecialchars($group) ?>" <?= $checked ? 'checked' : '' ?>><span class="module-icon" style="width:35px;height:35px;flex:0 0 35px"><i class="fa-solid <?= htmlspecialchars($module['icon']) ?>"></i></span><span><strong class="d-block small text-dark"><?= htmlspecialchars($module['label']) ?></strong><small class="text-muted"><?= htmlspecialchars($module['description']) ?></small></span></label></div><?php endforeach; ?></div></div></div><?php endforeach; ?>
            <div class="d-flex justify-content-end gap-2 mt-3"><a class="btn btn-light" href="/index.php?controller=permissions&role=<?= urlencode($selectedRole) ?>">Discard</a><button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Save permissions</button></div>
        </form>
    </div>
</div>
<style>
.permission-option { cursor:pointer; transition:.18s ease; background:#fff; min-height:86px; } .permission-option:hover { border-color:#c7c3ff !important; background:#fafaff; transform:translateY(-1px); } .permission-option.is-enabled { border-color:#aaa5ff !important; background:#f8f8ff; box-shadow:inset 3px 0 0 #5b52e8; } .permission-option small { font-size:11px; line-height:1.35; display:block; margin-top:3px; } .permission-option .form-check-input { width:18px; height:18px; } .permission-option .form-check-input:checked { background-color:#4f46e5; border-color:#4f46e5; }
</style>
<script>
(() => { const boxes = [...document.querySelectorAll('.module-checkbox')]; const count = () => { document.getElementById('enabled-count').textContent = boxes.filter(box => box.checked).length; boxes.forEach(box => box.closest('.permission-option')?.classList.toggle('is-enabled', box.checked)); }; boxes.forEach(box => box.addEventListener('change', count)); document.querySelectorAll('.select-group').forEach(button => button.addEventListener('click', () => { const group = button.dataset.group; const groupBoxes = boxes.filter(box => box.dataset.group === group); const shouldEnable = groupBoxes.some(box => !box.checked); groupBoxes.forEach(box => box.checked = shouldEnable); count(); })); })();
</script>
