<?php
$requisitions = $requisitions ?? [];
$clients = $clients ?? [];
$deviceTypes = $deviceTypes ?? [];
$itemTypes = $itemTypes ?? [];
$formData = $formData ?? [];
$canApprove = $canApprove ?? false;
$esc = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$requesterEmail = (string)($_SESSION['user']['email'] ?? '');
$requestType = (string)($formData['request_type'] ?? 'installation');
$selected = static fn(string $field, string $value, string $default = ''): string =>
    (string)($formData[$field] ?? $default) === $value ? 'selected' : '';
?>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div><h1 class="page-title">Requisitions</h1><p class="page-subtitle">Request installations or other items and capture signed approvals.</p></div>
</div>
<?php if (!empty($error)): ?><div class="alert alert-danger" role="alert"><?= $esc($error) ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-xl-8 order-2 order-xl-1">
        <div class="card shadow-sm border-0"><div class="card-body p-3 p-lg-4">
            <h2 class="h5 mb-3">Request history</h2>
            <?php if (!$requisitions): ?><p class="text-secondary mb-0">No requisitions have been submitted yet.</p><?php else: ?>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th># / Date</th><th>Requestor / Client</th><th>Request</th><th>Reason / Amount</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($requisitions as $requisition):
                    $installationItems = json_decode((string)($requisition['installation_items'] ?? ''), true);
                    $items = json_decode((string)($requisition['items_json'] ?? ''), true);
                    $isInstallation = ($requisition['request_type'] ?? 'other') === 'installation';
                ?>
                    <tr>
                        <td class="text-nowrap"><strong>#<?= (int)$requisition['id'] ?></strong><small class="d-block text-secondary">Request: <?= $esc($requisition['requested_date'] ?? '') ?></small><small class="d-block text-secondary">Required: <?= $esc($requisition['required_date'] ?? '—') ?></small></td>
                        <td><?= $esc($requisition['requester_name'] ?? 'Unknown') ?><small class="d-block text-secondary"><?= $esc($requisition['requester_email'] ?? '') ?></small><small class="d-block text-secondary"><?= $esc($requisition['department'] ?? '') ?> · <?= $esc($requisition['location'] ?? '') ?></small><?php if (!empty($requisition['beneficiary_name'])): ?><small class="d-block text-secondary">For <?= $esc($requisition['beneficiary_name']) ?></small><?php endif; ?><?php if (!empty($requisition['client_name'])): ?><small class="d-block text-secondary"><?= $esc($requisition['client_name']) ?></small><?php endif; ?><small class="d-block text-secondary"><?= $esc(ucfirst((string)($requisition['request_type'] ?? 'other'))) ?></small></td>
                        <td>
                            <?php if ($isInstallation && is_array($installationItems)): ?>
                                <div>Device: <?= $esc($installationItems['device']['type'] ?? '') ?> × <?= (int)($installationItems['device']['quantity'] ?? 0) ?></div>
                                <?php if (!empty($installationItems['device']['description'])): ?><small class="d-block text-secondary"><?= $esc($installationItems['device']['description']) ?></small><?php endif; ?>
                                <?php foreach (['camera' => 'Camera', 'fls' => 'FLS', 'sim_line' => 'SIM line'] as $key => $label): if (!empty($installationItems[$key]['type'])): ?><div><?= $esc($label) ?>: <?= $esc($installationItems[$key]['type']) ?> × <?= (int)($installationItems[$key]['quantity'] ?? 0) ?></div><?php if (!empty($installationItems[$key]['description'])): ?><small class="d-block text-secondary"><?= $esc($installationItems[$key]['description']) ?></small><?php endif; ?><?php endif; endforeach; ?>
                            <?php elseif (is_array($items)): ?>
                                <?php foreach ($items as $item): ?><div><?= $esc($item['name'] ?? '') ?> × <?= (int)($item['quantity'] ?? 0) ?></div><?php if (!empty($item['description'])): ?><small class="d-block text-secondary"><?= $esc($item['description']) ?></small><?php endif; ?><?php endforeach; ?>
                            <?php else: ?><span class="text-secondary">No item details</span><?php endif; ?>
                            <?php if (!empty($requisition['requester_signature'])): ?><small class="d-block mt-2 text-secondary">Requestor signature</small><img src="<?= $esc($requisition['requester_signature']) ?>" alt="Requestor signature" style="max-width:145px;max-height:42px;border:1px solid #e3e7ed;border-radius:4px"><?php endif; ?>
                        </td>
                        <td><?= $esc($requisition['purpose'] ?? '') ?><strong class="d-block mt-1">$<?= number_format((float)($requisition['total_amount'] ?? 0), 2) ?></strong></td>
                        <td>
                            <span class="badge text-bg-<?= $requisition['status'] === 'approved' ? 'success' : ($requisition['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= $esc(ucfirst((string)$requisition['status'])) ?></span>
                            <?php if (!empty($requisition['approver_signature'])): ?><small class="d-block mt-2 text-secondary"><?= $esc($requisition['approver_name'] ?? 'Approver') ?> signature</small><img src="<?= $esc($requisition['approver_signature']) ?>" alt="Approver signature" style="max-width:145px;max-height:42px;border:1px solid #e3e7ed;border-radius:4px"><?php endif; ?>
                            <?php if (!empty($requisition['dispatcher_name']) || !empty($requisition['dispatcher_signature'])): ?><small class="d-block mt-2 text-secondary">Dispatched by: <?= $esc($requisition['dispatcher_name'] ?? '') ?></small><?php if (!empty($requisition['dispatcher_signature'])): ?><img src="<?= $esc($requisition['dispatcher_signature']) ?>" alt="Dispatcher signature" style="max-width:145px;max-height:42px;border:1px solid #e3e7ed;border-radius:4px"><?php endif; ?><?php endif; ?>
                            <?php if (!empty($requisition['receiver_name']) || !empty($requisition['receiver_signature'])): ?><small class="d-block mt-2 text-secondary">Received by: <?= $esc($requisition['receiver_name'] ?? '') ?><?= !empty($requisition['receiver_date']) ? ' · '.$esc($requisition['receiver_date']) : '' ?></small><?php if (!empty($requisition['receiver_signature'])): ?><img src="<?= $esc($requisition['receiver_signature']) ?>" alt="Receiver signature" style="max-width:145px;max-height:42px;border:1px solid #e3e7ed;border-radius:4px"><?php endif; ?><?php endif; ?>
                            <?php if ($canApprove && $requisition['status'] === 'pending'): ?><button class="btn btn-sm btn-outline-primary d-block mt-2 open-approval" type="button" data-id="<?= (int)$requisition['id'] ?>">Review</button><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div></div>
    </div>
    <div class="col-xl-4 order-1 order-xl-2">
        <div class="card shadow-sm border-0"><div class="card-body p-3 p-lg-4">
            <h2 class="h5 mb-1">New requisition</h2><p class="small text-secondary mb-3">Sign the request before submitting it for approval.</p>
            <form method="post" action="/index.php?controller=requisition&action=create" id="requisition-form" class="vstack gap-3">
                <?= $this->csrfField() ?>
                <div><label class="form-label" for="request-type">Request type</label><select class="form-select" name="request_type" id="request-type" required><option value="installation" <?= $selected('request_type', 'installation', 'installation') ?>>Installation</option><option value="other" <?= $selected('request_type', 'other') ?>>Other items</option></select></div>
                <div class="row g-2">
                    <div class="col-6"><label class="form-label" for="requester-name">Requestor</label><input class="form-control" id="requester-name" value="<?= $esc($_SESSION['user']['name'] ?? '') ?>" readonly></div>
                    <div class="col-6"><label class="form-label" for="requested-date">Request date</label><input class="form-control" type="date" name="requested_date" id="requested-date" value="<?= $esc($formData['requested_date'] ?? date('Y-m-d')) ?>" required></div>
                </div>
                <div><label class="form-label" for="requester-email">Requestor email</label><input class="form-control" type="email" id="requester-email" value="<?= $esc($requesterEmail) ?>" readonly><div class="form-text">Taken from your staff account.</div></div>
                <div class="row g-2">
                    <div class="col-6"><label class="form-label" for="location">Location</label><input class="form-control" name="location" id="location" maxlength="150" value="<?= $esc($formData['location'] ?? '') ?>" required></div>
                    <div class="col-6"><label class="form-label" for="required-date">Date required</label><input class="form-control" type="date" name="required_date" id="required-date" value="<?= $esc($formData['required_date'] ?? date('Y-m-d')) ?>" required></div>
                </div>
                <div id="installation-fields" class="vstack gap-3">
                    <?php if (!$clients): ?><div class="alert alert-warning py-2 mb-0">No active clients are available. Add a client before requesting an installation.</div><?php endif; ?>
                    <div><label class="form-label" for="client-id">Client</label><select class="form-select" name="client_id" id="client-id" required><option value="">Choose client</option><?php foreach ($clients as $client): ?><option value="<?= (int)$client['id'] ?>" <?= (string)($formData['client_id'] ?? '') === (string)$client['id'] ? 'selected' : '' ?>><?= $esc($client['company_name']) ?></option><?php endforeach; ?></select></div>
                    <div class="row g-2">
                        <div class="col-8"><label class="form-label" for="installation-device">Device</label><select class="form-select" name="installation_device_type" id="installation-device" required><option value="">Choose device</option><?php foreach ($deviceTypes as $type): ?><option value="<?= $esc($type) ?>" <?= $selected('installation_device_type', $type) ?>><?= $esc($type) ?></option><?php endforeach; ?></select></div>
                        <div class="col-4"><label class="form-label" for="installation-device-quantity">Qty</label><input class="form-control" type="number" min="1" step="1" name="installation_device_quantity" id="installation-device-quantity" value="<?= $esc($formData['installation_device_quantity'] ?? '1') ?>" required></div>
                    </div>
                    <div><label class="form-label" for="installation-device-description">Device description</label><input class="form-control" name="installation_device_description" id="installation-device-description" maxlength="500" value="<?= $esc($formData['installation_device_description'] ?? '') ?>"></div>
                    <?php foreach (['camera' => 'Cameras', 'fls' => 'FLS', 'sim_line' => 'Sim cards'] as $key => $category): $field = 'installation_'.$key.'_type'; $quantityField = 'installation_'.$key.'_quantity'; ?>
                    <div class="row g-2">
                        <div class="col-8"><label class="form-label" for="<?= $esc($field) ?>"><?= $esc($key === 'sim_line' ? 'SIM line' : ucfirst($key)) ?> <span class="text-secondary fw-normal">(optional)</span></label><select class="form-select optional-install-item" name="<?= $esc($field) ?>" id="<?= $esc($field) ?>"><option value="">None</option><?php foreach ($itemTypes[$category] as $type): ?><option value="<?= $esc($type) ?>" <?= $selected($field, $type) ?>><?= $esc($type) ?></option><?php endforeach; ?></select></div>
                        <div class="col-4"><label class="form-label" for="<?= $esc($quantityField) ?>">Qty</label><input class="form-control optional-install-quantity" type="number" min="1" step="1" name="<?= $esc($quantityField) ?>" id="<?= $esc($quantityField) ?>" value="<?= $esc($formData[$quantityField] ?? '') ?>" placeholder="—"></div>
                    </div>
                    <div><label class="form-label" for="installation-<?= $esc($key) ?>-description"><?= $esc($key === 'sim_line' ? 'SIM line' : ucfirst($key)) ?> description</label><input class="form-control" name="installation_<?= $esc($key) ?>_description" id="installation-<?= $esc($key) ?>-description" maxlength="500" value="<?= $esc($formData['installation_'.$key.'_description'] ?? '') ?>"></div>
                    <?php endforeach; ?>
                </div>
                <div id="other-fields" class="vstack gap-3">
                    <div><label class="form-label" for="beneficiary-name">Person requesting for</label><input class="form-control" name="beneficiary_name" id="beneficiary-name" maxlength="150" value="<?= $esc($formData['beneficiary_name'] ?? '') ?>"></div>
                    <div><label class="form-label">Requested items</label><div id="requisition-items">
                        <?php $requestItems = is_array($formData['items'] ?? null) ? $formData['items'] : [['name' => '', 'quantity' => 1]]; foreach ($requestItems as $index => $item): ?>
                        <div class="row g-2 mb-2 requisition-item"><div class="col-md-4"><input class="form-control" name="items[<?= (int)$index ?>][name]" placeholder="Item" value="<?= $esc($item['name'] ?? '') ?>"></div><div class="col-md-5"><input class="form-control" name="items[<?= (int)$index ?>][description]" placeholder="Description" maxlength="500" value="<?= $esc($item['description'] ?? '') ?>"></div><div class="col-md-3"><input class="form-control" type="number" min="1" step="1" name="items[<?= (int)$index ?>][quantity]" placeholder="Qty" value="<?= $esc($item['quantity'] ?? '') ?>"></div></div>
                        <?php endforeach; ?>
                    </div><button class="btn btn-sm btn-outline-secondary" type="button" id="add-requisition-item">Add another item</button></div>
                </div>
                <div><label class="form-label" for="purpose">Reason</label><textarea class="form-control" name="purpose" id="purpose" rows="2" required><?= $esc($formData['purpose'] ?? '') ?></textarea></div>
                <div><label class="form-label" for="total-amount">Amount ($)</label><input class="form-control" type="number" min="0" step="0.01" name="total_amount" id="total-amount" value="<?= $esc($formData['total_amount'] ?? '') ?>" required></div>
                <fieldset class="border rounded p-3">
                    <legend class="float-none w-auto px-2 fs-6 mb-1">Dispatch and receipt <span class="text-secondary fw-normal">(optional)</span></legend>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label" for="dispatcher-name">Dispatcher name</label><input class="form-control" name="dispatcher_name" id="dispatcher-name" maxlength="150" value="<?= $esc($formData['dispatcher_name'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label" for="receiver-name">Receiver name</label><input class="form-control" name="receiver_name" id="receiver-name" maxlength="150" value="<?= $esc($formData['receiver_name'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label" for="receiver-date">Date received</label><input class="form-control" type="date" name="receiver_date" id="receiver-date" value="<?= $esc($formData['receiver_date'] ?? '') ?>"></div>
                    </div>
                    <?php foreach (['dispatcher' => 'Dispatcher', 'receiver' => 'Receiver'] as $role => $label): ?>
                    <div class="signature-control mt-3">
                        <div class="d-flex justify-content-between align-items-center"><label class="form-label mb-1" for="<?= $role ?>-signature-pad"><?= $label ?> signature</label><button class="btn btn-sm btn-link p-0 clear-signature" type="button" data-canvas="<?= $role ?>-signature-pad">Clear</button></div>
                        <canvas id="<?= $role ?>-signature-pad" class="signature-pad" width="640" height="140" aria-label="Draw <?= strtolower($label) ?> signature"></canvas>
                        <input type="hidden" name="<?= $role ?>_signature" id="<?= $role ?>-signature" value="">
                    </div>
                    <?php endforeach; ?>
                </fieldset>
                <div class="signature-control">
                    <div class="d-flex justify-content-between align-items-center"><label class="form-label mb-1" for="requester-signature-pad">Requestor digital signature</label><button class="btn btn-sm btn-link p-0 clear-signature" type="button" data-canvas="requester-signature-pad">Clear</button></div>
                    <canvas id="requester-signature-pad" class="signature-pad" width="640" height="140" aria-label="Draw requestor signature"></canvas>
                    <input type="hidden" name="requester_signature" id="requester-signature" value="">
                </div>
                <button class="btn btn-primary" type="submit">Submit requisition</button>
            </form>
        </div></div>
    </div>
</div>
<?php if ($canApprove): ?>
<dialog id="approval-dialog" class="app-confirm-dialog" aria-labelledby="approval-title">
    <div class="app-confirm-dialog-body">
        <h2 id="approval-title">Review requisition</h2>
        <p>Sign below, then approve or reject this request.</p>
        <form method="post" action="/index.php?controller=requisition&action=decide" id="approval-form">
            <?= $this->csrfField() ?>
            <input type="hidden" name="id" id="approval-requisition-id">
            <input type="hidden" name="approver_signature" id="approver-signature">
            <canvas id="approver-signature-pad" class="signature-pad" width="640" height="140" aria-label="Draw approver signature"></canvas>
            <label class="form-label mt-3" for="approval-comments">Comments</label><textarea class="form-control mb-3" name="comments" id="approval-comments" rows="2"></textarea>
            <div class="d-flex justify-content-end gap-2"><button type="button" class="btn btn-light" id="close-approval">Cancel</button><button type="submit" name="status" value="rejected" class="btn btn-outline-danger">Reject</button><button type="submit" name="status" value="approved" class="btn btn-success">Approve</button></div>
        </form>
    </div>
</dialog>
<?php endif; ?>
<style>
.signature-pad{display:block;width:100%;height:100px;border:1px solid #dfe5ee;border-radius:9px;background:#fff;touch-action:none}.signature-control .clear-signature{font-size:12px}
</style>
<script>
const requestTypeSelect=document.getElementById('request-type'),installationFields=document.getElementById('installation-fields'),otherFields=document.getElementById('other-fields'),clientField=document.getElementById('client-id'),deviceField=document.getElementById('installation-device'),beneficiaryField=document.getElementById('beneficiary-name');
function syncRequisitionType(){const installation=requestTypeSelect.value==='installation';installationFields.classList.toggle('d-none',!installation);otherFields.classList.toggle('d-none',installation);clientField.required=installation;deviceField.required=installation;beneficiaryField.required=!installation;document.querySelectorAll('.optional-install-item').forEach(select=>{const quantity=select.closest('.row').querySelector('.optional-install-quantity');quantity.required=select.value!=='';if(select.value===''&&quantity.value!=='')quantity.value='';});}
requestTypeSelect.addEventListener('change',syncRequisitionType);document.querySelectorAll('.optional-install-item').forEach(select=>select.addEventListener('change',syncRequisitionType));syncRequisitionType();
document.getElementById('add-requisition-item')?.addEventListener('click',()=>{const rows=document.getElementById('requisition-items'),index=rows.querySelectorAll('.requisition-item').length;if(index>=50)return;const row=document.createElement('div');row.className='row g-2 mb-2 requisition-item';row.innerHTML=`<div class="col-md-4"><input class="form-control" name="items[${index}][name]" placeholder="Item"></div><div class="col-md-5"><input class="form-control" name="items[${index}][description]" placeholder="Description" maxlength="500"></div><div class="col-md-3"><input class="form-control" type="number" min="1" step="1" name="items[${index}][quantity]" placeholder="Qty"></div>`;rows.append(row);});
function setupSignaturePad(canvasId,inputId){const canvas=document.getElementById(canvasId),input=document.getElementById(inputId);if(!canvas||!input)return null;const context=canvas.getContext('2d');context.fillStyle='#fff';context.fillRect(0,0,canvas.width,canvas.height);context.strokeStyle='#17243d';context.lineWidth=3;context.lineCap='round';let drawing=false,hasStroke=false;const point=event=>{const bounds=canvas.getBoundingClientRect();return{x:(event.clientX-bounds.left)*canvas.width/bounds.width,y:(event.clientY-bounds.top)*canvas.height/bounds.height};};canvas.addEventListener('pointerdown',event=>{drawing=true;hasStroke=true;canvas.setPointerCapture(event.pointerId);const p=point(event);context.beginPath();context.moveTo(p.x,p.y);});canvas.addEventListener('pointermove',event=>{if(!drawing)return;const p=point(event);context.lineTo(p.x,p.y);context.stroke();});canvas.addEventListener('pointerup',()=>{drawing=false;});canvas.addEventListener('pointercancel',()=>{drawing=false;});document.querySelector(`.clear-signature[data-canvas="${canvasId}"]`)?.addEventListener('click',()=>{context.clearRect(0,0,canvas.width,canvas.height);context.fillStyle='#fff';context.fillRect(0,0,canvas.width,canvas.height);context.strokeStyle='#17243d';hasStroke=false;input.value='';});return{save:()=>{if(!hasStroke)return false;input.value=canvas.toDataURL('image/png');return true;}};}
const requesterSignature=setupSignaturePad('requester-signature-pad','requester-signature'),dispatcherSignature=setupSignaturePad('dispatcher-signature-pad','dispatcher-signature'),receiverSignature=setupSignaturePad('receiver-signature-pad','receiver-signature');document.getElementById('requisition-form').addEventListener('submit',event=>{if(!requesterSignature.save()){event.preventDefault();window.alert('Please add the requestor signature.');return;}dispatcherSignature.save();receiverSignature.save();});
<?php if ($canApprove): ?>
const approvalDialog=document.getElementById('approval-dialog'),approverSignature=setupSignaturePad('approver-signature-pad','approver-signature');document.querySelectorAll('.open-approval').forEach(button=>button.addEventListener('click',()=>{document.getElementById('approval-requisition-id').value=button.dataset.id;approvalDialog.showModal();}));document.getElementById('close-approval').addEventListener('click',()=>approvalDialog.close());document.getElementById('approval-form').addEventListener('submit',event=>{if(!approverSignature.save()){event.preventDefault();window.alert('Please add the approver signature.');}});
<?php endif; ?>
</script>
