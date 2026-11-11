<?php
$activeInstallations = $activeInstallations ?? [];
$deinstallations = $deinstallations ?? [];
$formData = $formData ?? [];
$esc = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div><h1 class="page-title">Deinstallations</h1><p class="page-subtitle">Record devices and cameras removed from vehicles. Returned equipment is put back into inventory.</p></div>
    <a class="btn btn-outline-secondary" href="/index.php?controller=gps">Back to installations</a>
</div>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0"><div class="card-body p-3 p-lg-4">
            <h2 class="h5 mb-3">Record a vehicle removal</h2>
            <?php if (!empty($error)): ?><div class="alert alert-danger" role="alert"><?= $esc($error) ?></div><?php endif; ?>
            <?php if (!$activeInstallations): ?><div class="alert alert-info mb-0">There are no active installations to deinstall.</div><?php else: ?>
            <form method="post" action="/index.php?controller=gps&action=deinstall" class="vstack gap-3" id="deinstallation-form">
                <?= $this->csrfField() ?>
                <div><label class="form-label" for="gps-device-id">Installed device</label><select class="form-select" name="gps_device_id" id="gps-device-id" required><option value="">Choose installation</option><?php foreach ($activeInstallations as $device): $trackerIdentifier = ($device['package_type'] ?? '') === 'Camera only' ? '' : ($device['imei'] ?? ''); ?><option value="<?= (int)$device['id'] ?>" data-type="<?= $esc($device['device_type'] ?? '') ?>" data-imei="<?= $esc($trackerIdentifier) ?>" data-camera="<?= $esc($device['camera_serial'] ?? '') ?>" data-registration="<?= $esc($device['registration_number'] ?? '') ?>" data-chassis="<?= $esc($device['chassis_number'] ?? '') ?>" <?= (string)($formData['gps_device_id'] ?? '') === (string)$device['id'] ? 'selected' : '' ?>><?= $esc(($device['device_type'] ?? 'Device').' · '.($trackerIdentifier ?: ($device['camera_serial'] ?? 'Camera'))) ?><?= !empty($device['registration_number']) ? ' · '. $esc($device['registration_number']) : '' ?></option><?php endforeach; ?></select></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="removed-device-type">Device type</label><input class="form-control" id="removed-device-type" readonly></div>
                    <div class="col-md-6"><label class="form-label" for="removed-imei">Tracker IMEI / serial</label><input class="form-control" id="removed-imei" readonly></div>
                    <div class="col-md-6"><label class="form-label" for="removed-camera">Camera removed</label><input class="form-control" id="removed-camera" readonly placeholder="No camera assigned"></div>
                    <div class="col-md-6"><label class="form-label" for="removed-at">Removal date</label><input class="form-control" type="date" name="removed_at" id="removed-at" value="<?= $esc($formData['removed_at'] ?? date('Y-m-d')) ?>" required></div>
                    <div class="col-md-6"><label class="form-label" for="registration-number">Vehicle registration number</label><input class="form-control" name="registration_number" id="registration-number" maxlength="100" value="<?= $esc($formData['registration_number'] ?? '') ?>" required></div>
                    <div class="col-md-6"><label class="form-label" for="chassis-number">Vehicle chassis number</label><input class="form-control" name="chassis_number" id="chassis-number" maxlength="100" value="<?= $esc($formData['chassis_number'] ?? '') ?>" required></div>
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
            <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Client / vehicle</th><th>Device</th><th>Camera removed</th><th>Reason</th></tr></thead><tbody>
            <?php foreach ($deinstallations as $record): ?><tr><td class="text-nowrap"><?= $esc($record['removed_at']) ?></td><td><?= $esc($record['client_name'] ?? '—') ?><small class="d-block text-secondary"><?= $esc($record['registration_number'].' · '.$record['chassis_number']) ?></small></td><td><?= $esc($record['device_type'].' · '.($record['imei'] ?? '')) ?></td><td><?= $esc($record['camera_serial'] ?: '—') ?></td><td><?= $esc($record['reason'] ?: '—') ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
            <?php endif; ?>
        </div></div>
    </div>
</div>
<script>
const deinstallSelect=document.getElementById('gps-device-id'),registrationField=document.getElementById('registration-number'),chassisField=document.getElementById('chassis-number');
function syncDeinstallationFields(){const option=deinstallSelect?.selectedOptions[0];if(!option){document.getElementById('removed-device-type').value='';document.getElementById('removed-imei').value='';document.getElementById('removed-camera').value='';return;}document.getElementById('removed-device-type').value=option.dataset.type||'';document.getElementById('removed-imei').value=option.dataset.imei||'';document.getElementById('removed-camera').value=option.dataset.camera||'';if(!registrationField.value)registrationField.value=option.dataset.registration||'';if(!chassisField.value)chassisField.value=option.dataset.chassis||'';}
deinstallSelect?.addEventListener('change',()=>{registrationField.value='';chassisField.value='';syncDeinstallationFields();});syncDeinstallationFields();
</script>
