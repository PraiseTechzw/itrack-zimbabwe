<?php
$equipmentOptions = $equipmentOptions ?? [];
$deinstallations = $deinstallations ?? [];
$formData = $formData ?? [];
$esc = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$selectedReference = (string)($formData['installed_equipment'] ?? '');
?>
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div><h1 class="page-title">Deinstallations</h1><p class="page-subtitle">Pick a tracker or camera from an active installation. Its equipment is returned to inventory.</p></div>
    <a class="btn btn-outline-secondary" href="/index.php?controller=gps">Back to installations</a>
</div>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0"><div class="card-body p-3 p-lg-4">
            <h2 class="h5 mb-3">Record a vehicle removal</h2>
            <?php if (!empty($error)): ?><div class="alert alert-danger" role="alert"><?= $esc($error) ?></div><?php endif; ?>
            <?php if (!$equipmentOptions): ?><div class="alert alert-info mb-0">There are no installed devices or cameras to deinstall.</div><?php else: ?>
            <form method="post" action="/index.php?controller=gps&action=deinstall" class="vstack gap-3" id="deinstallation-form">
                <?= $this->csrfField() ?>
                <div><label class="form-label" for="installed-equipment">Installed device or camera</label><select class="form-select" name="installed_equipment" id="installed-equipment" required><option value="">Choose a device or camera</option><?php foreach ($equipmentOptions as $option): ?><option value="<?= $esc($option['ref']) ?>" data-serial="<?= $esc($option['serial'] ?? '') ?>" data-registration="<?= $esc($option['registration'] ?? '') ?>" data-chassis="<?= $esc($option['chassis'] ?? '') ?>" <?= $selectedReference === (string)$option['ref'] ? 'selected' : '' ?>><?= $esc($option['label']) ?></option><?php endforeach; ?></select><div class="form-text">Select the specific tracker or camera being removed.</div></div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label" for="removed-item">Equipment selected</label><input class="form-control" id="removed-item" readonly placeholder="Choose a device or camera"></div>
                    <div class="col-12"><label class="form-label" for="removed-at">Removal date</label><input class="form-control" type="date" name="removed_at" id="removed-at" value="<?= $esc($formData['removed_at'] ?? date('Y-m-d')) ?>" required></div>
                    <div class="col-12"><label class="form-label" for="registration-number">Vehicle registration number</label><input class="form-control" name="registration_number" id="registration-number" maxlength="100" value="<?= $esc($formData['registration_number'] ?? '') ?>" required></div>
                    <div class="col-12"><label class="form-label" for="chassis-number">Vehicle chassis number <span class="text-secondary fw-normal">(optional)</span></label><input class="form-control" name="chassis_number" id="chassis-number" maxlength="100" value="<?= $esc($formData['chassis_number'] ?? '') ?>"></div>
                    <div class="col-12"><label class="form-label" for="reason">Reason for removal</label><textarea class="form-control" name="reason" id="reason" rows="2"><?= $esc($formData['reason'] ?? '') ?></textarea></div>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Save deinstallation</button>
            </form>
            <?php endif; ?>
        </div></div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm border-0"><div class="card-body p-3 p-lg-4">
            <h2 class="h5 mb-3">Removal history</h2>
            <?php if (!$deinstallations): ?><p class="text-secondary mb-0">No deinstallations recorded yet.</p><?php else: ?>
            <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Item removed</th><th>Serial / camera</th><th>Hard drive</th><th>Client / vehicle</th><th>Reason</th></tr></thead><tbody>
            <?php foreach ($deinstallations as $record): ?><tr><td class="text-nowrap"><?= $esc($record['removed_at']) ?></td><td><?= $esc(ucfirst((string)($record['removed_item'] ?: 'device'))) ?></td><td><?= $esc(trim((string)($record['imei'] ?? '').' '.(string)($record['camera_serial'] ?? '')) ?: '—') ?></td><td><?= $esc($record['hard_drive_serial'] ?: '—') ?></td><td><?= $esc($record['client_name'] ?? '—') ?><small class="d-block text-secondary"><?= $esc(trim((string)($record['registration_number'] ?? '').' '.(string)($record['chassis_number'] ?? ''))) ?></small></td><td><?= $esc($record['reason'] ?: '—') ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
            <?php endif; ?>
        </div></div>
    </div>
</div>
<script>
const equipmentSelect=document.getElementById('installed-equipment'),registrationField=document.getElementById('registration-number'),chassisField=document.getElementById('chassis-number'),selectedField=document.getElementById('removed-item');
function syncDeinstallationFields(){const option=equipmentSelect?.selectedOptions[0];if(!option||!option.value){if(selectedField)selectedField.value='';return;}selectedField.value=option.textContent.trim();const serial=option.dataset.serial||'';if(serial&&!registrationField.value)registrationField.value=option.dataset.registration||'';if(serial&&!chassisField.value)chassisField.value=option.dataset.chassis||'';}
equipmentSelect?.addEventListener('change',()=>{registrationField.value='';chassisField.value='';syncDeinstallationFields();});syncDeinstallationFields();
</script>
