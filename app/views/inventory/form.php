<?php
$mode = $mode ?? 'create';
$product = $product ?? [];
$title = $mode === 'edit' ? 'Edit Item' : 'Add Inventory Item';
$itemTypes = $itemTypes ?? \Product::itemTypes();
$category = (string)($product['item_category'] ?? 'Tracker');
$identifierRules = [];
foreach ($itemTypes as $optionCategory => $_) {
    $rule = \Product::identifierRules((string) $optionCategory);
    $identifierRules[$optionCategory] = [
        'required' => $rule['required'],
        'label' => $optionCategory === 'Sim cards' ? 'SIM line number (max 10 digits)'
            : ($optionCategory === 'Hard drives' ? 'Hard drive serial (letters and numbers)'
            : ($rule['required'] ? 'Serial number / IMEI (digits only)' : 'Serial number / IMEI (optional)')),
        'hint' => $rule['hint'],
        'maxlength' => $optionCategory === 'Sim cards' ? 10 : 100,
        'pattern' => $optionCategory === 'Hard drives' ? '[A-Za-z0-9-]*' : '[0-9]*',
    ];
}
$currentRule = $identifierRules[$category] ?? $identifierRules['Tracker'];
?>
<div class="row justify-content-center"><div class="col-lg-8"><div class="card shadow-sm border-0"><div class="card-body p-3 p-lg-4">
<h2 class="h4 mb-1"><?= htmlspecialchars($title) ?></h2><p class="text-secondary small mb-4">Add one physical inventory item. To upload stock in bulk, use the Excel/CSV import.</p>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" class="row g-3"><?= $this->csrfField() ?>
<div class="col-md-6"><label class="form-label" for="item-category">Category</label><select class="form-select" name="item_category" id="item-category" required><?php foreach ($itemTypes as $optionCategory => $types): ?><option value="<?= htmlspecialchars($optionCategory) ?>" <?= $category === $optionCategory ? 'selected' : '' ?>><?= htmlspecialchars($optionCategory === 'Sim cards' ? 'SIM cards / Lines' : $optionCategory) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label" for="item-type">Type</label><select class="form-select" name="item_type" id="item-type" required></select></div>
<div class="col-md-6"><label class="form-label" id="identifier-label" for="item-identifier"><?= htmlspecialchars($currentRule['label']) ?> <?= $currentRule['required'] ? '<span class="text-danger">*</span>' : '<span class="text-secondary fw-normal">(optional)</span>' ?></label><input class="form-control" id="item-identifier" name="identifier" inputmode="numeric" pattern="<?= htmlspecialchars($currentRule['pattern'], ENT_QUOTES, 'UTF-8') ?>" maxlength="<?= (int)$currentRule['maxlength'] ?>" value="<?= htmlspecialchars((string)($product['identifier'] ?? $product['imei'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" <?= $currentRule['required'] ? 'required' : '' ?>><div class="form-text" id="identifier-help"><?= htmlspecialchars($currentRule['hint']) ?></div></div>
<div class="col-md-6"><label class="form-label" for="cost-price">Cost price</label><input class="form-control" id="cost-price" type="number" step="0.01" min="0" name="cost_price" value="<?= htmlspecialchars((string)($product['cost_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></div>
<div class="col-md-6"><label class="form-label" for="selling-price">Selling price (excl. VAT)</label><input class="form-control" id="selling-price" type="number" step="0.01" min="0" name="selling_price" value="<?= htmlspecialchars((string)($product['selling_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"></div>
<div class="col-md-6"><label class="form-label" for="vat-rate">VAT rate (%)</label><input class="form-control" id="vat-rate" type="number" step="0.001" min="0" max="100" name="vat_rate" value="<?= htmlspecialchars((string)($product['vat_rate'] ?? 15), ENT_QUOTES, 'UTF-8') ?>"><div class="form-text">Applied to the selling price for reporting and the inventory export.</div></div>
<div class="col-12"><button class="btn btn-primary" type="submit">Save item</button> <a class="btn btn-outline-secondary" href="/index.php?controller=inventory">Back to inventory</a></div></form>
</div></div></div></div>
<script>
const inventoryTypes=<?= json_encode($itemTypes, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>,identifierRules=<?= json_encode($identifierRules, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>,categorySelect=document.getElementById('item-category'),typeSelect=document.getElementById('item-type'),identifierInput=document.getElementById('item-identifier');let preferredType=<?= json_encode($product['item_type'] ?? '') ?>;
function syncInventoryCategory(){const category=categorySelect.value,rule=identifierRules[category]||identifierRules.Tracker;identifierInput.required=rule.required;identifierInput.maxLength=rule.maxlength;identifierInput.pattern=rule.pattern;identifierInput.inputMode=category==='Sim cards'||category==='Hard drives'?'text':'numeric';document.getElementById('identifier-label').innerHTML=rule.label+' '+(rule.required?'<span class="text-danger">*</span>':'<span class="text-secondary fw-normal">(optional)</span>');document.getElementById('identifier-help').textContent=rule.hint;typeSelect.innerHTML='';(inventoryTypes[category]||[]).forEach(value=>{const option=document.createElement('option');option.value=value;option.textContent=value;option.selected=value===preferredType;typeSelect.append(option);});preferredType='';}
categorySelect.addEventListener('change',syncInventoryCategory);syncInventoryCategory();
</script>
