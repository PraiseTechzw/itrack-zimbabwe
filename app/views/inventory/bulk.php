<?php
$title = 'Bulk add inventory';
$itemTypes = $itemTypes ?? \Product::itemTypes();
$rows = $rows ?? [['item_category' => 'Tracker', 'item_type' => '', 'quantity' => 1, 'unit' => 'pcs', 'cost_price' => '0', 'selling_price' => '0']];
$esc = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<style>
.bulk-hero{padding:22px 25px;margin-bottom:18px;border-radius:16px;background:linear-gradient(115deg,#17243d,#334d7a);color:#fff}.bulk-hero h1{margin:0 0 5px;font-size:23px;font-weight:800}.bulk-hero p{margin:0;color:#c3cee0;font-size:12px}.bulk-row{display:grid;grid-template-columns:1.05fr 1.4fr .7fr .7fr .85fr .85fr 38px;gap:10px;align-items:end;padding:13px;border:1px solid #e7ebf1;border-radius:12px;background:#fff}.bulk-row+.bulk-row{margin-top:10px}.bulk-row label{font-size:10px;font-weight:700;color:#6e7c90;margin-bottom:5px}.bulk-row .form-control,.bulk-row .form-select{font-size:12px}.bulk-remove{height:38px}@media(max-width:900px){.bulk-row{grid-template-columns:repeat(2,minmax(0,1fr))}.bulk-remove{grid-column:2;justify-self:end}}@media(max-width:500px){.bulk-row{grid-template-columns:1fr 1fr}.bulk-row>div:nth-child(2){grid-column:span 2}}
</style>
<div class="bulk-hero"><h1>Bulk add inventory</h1><p>Create several stock lines at once. Enter a quantity for each product type; serial numbers can be added later by editing individual items.</p></div>
<div class="card border-0 shadow-sm"><div class="card-body p-3 p-lg-4">
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= $esc($error) ?></div><?php endif; ?>
<form method="post" id="bulk-form"><?= $this->csrfField() ?><div id="bulk-rows">
<?php foreach ($rows as $index => $row): ?>
<div class="bulk-row">
    <div><label>Category</label><select class="form-select bulk-category" name="items[<?= (int)$index ?>][item_category]" required><?php foreach ($itemTypes as $category => $types): ?><option value="<?= $esc($category) ?>" <?= ($row['item_category'] ?? '') === $category ? 'selected' : '' ?>><?= $esc($category) ?></option><?php endforeach; ?></select></div>
    <div><label>Item type</label><select class="form-select bulk-type" name="items[<?= (int)$index ?>][item_type]" required><?php foreach ($itemTypes[$row['item_category'] ?? ''] ?? [] as $type): ?><option value="<?= $esc($type) ?>" <?= ($row['item_type'] ?? '') === $type ? 'selected' : '' ?>><?= $esc($type) ?></option><?php endforeach; ?></select></div>
    <div><label>Quantity</label><input class="form-control" type="number" min="1" name="items[<?= (int)$index ?>][quantity]" value="<?= $esc($row['quantity'] ?? 1) ?>" required></div>
    <div><label>Unit</label><input class="form-control" name="items[<?= (int)$index ?>][unit]" value="<?= $esc($row['unit'] ?? 'pcs') ?>" maxlength="20"></div>
    <div><label>Cost price ($)</label><input class="form-control" type="number" min="0" step="0.01" name="items[<?= (int)$index ?>][cost_price]" value="<?= $esc($row['cost_price'] ?? 0) ?>" required></div>
    <div><label>Selling price ($)</label><input class="form-control" type="number" min="0" step="0.01" name="items[<?= (int)$index ?>][selling_price]" value="<?= $esc($row['selling_price'] ?? 0) ?>" required></div>
    <button class="btn btn-outline-danger bulk-remove" type="button" aria-label="Remove row"><i class="fa-solid fa-trash"></i></button>
</div>
<?php endforeach; ?></div>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"><button class="btn btn-outline-primary" type="button" id="add-bulk-row"><i class="fa-solid fa-plus me-2"></i>Add another row</button><div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="/index.php?controller=inventory">Cancel</a><button class="btn btn-primary" type="submit"><i class="fa-solid fa-layer-group me-2"></i>Save all items</button></div></div>
</form></div></div>
<script>
const bulkTypes=<?= json_encode($itemTypes, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>,bulkRows=document.getElementById('bulk-rows');
function syncBulkType(row){const select=row.querySelector('.bulk-type'),category=row.querySelector('.bulk-category').value,previous=select.value;select.innerHTML='';(bulkTypes[category]||[]).forEach(value=>{const option=document.createElement('option');option.value=value;option.textContent=value;option.selected=value===previous;select.append(option);});}
function addBulkRow(){if(bulkRows.children.length>=50)return;const index=Date.now()+Math.floor(Math.random()*1000),categories=Object.keys(bulkTypes);const row=document.createElement('div');row.className='bulk-row';row.innerHTML=`<div><label>Category</label><select class="form-select bulk-category" name="items[${index}][item_category]" required>${categories.map(c=>`<option value="${c}">${c}</option>`).join('')}</select></div><div><label>Item type</label><select class="form-select bulk-type" name="items[${index}][item_type]" required></select></div><div><label>Quantity</label><input class="form-control" type="number" min="1" name="items[${index}][quantity]" value="1" required></div><div><label>Unit</label><input class="form-control" name="items[${index}][unit]" value="pcs" maxlength="20"></div><div><label>Cost price ($)</label><input class="form-control" type="number" min="0" step="0.01" name="items[${index}][cost_price]" value="0" required></div><div><label>Selling price ($)</label><input class="form-control" type="number" min="0" step="0.01" name="items[${index}][selling_price]" value="0" required></div><button class="btn btn-outline-danger bulk-remove" type="button" aria-label="Remove row"><i class="fa-solid fa-trash"></i></button>`;bulkRows.append(row);syncBulkType(row);}
document.getElementById('add-bulk-row').addEventListener('click',addBulkRow);bulkRows.addEventListener('change',event=>{if(event.target.matches('.bulk-category'))syncBulkType(event.target.closest('.bulk-row'));});bulkRows.addEventListener('click',event=>{const button=event.target.closest('.bulk-remove');if(button&&bulkRows.children.length>1)button.closest('.bulk-row').remove();});bulkRows.querySelectorAll('.bulk-row').forEach(row=>syncBulkType(row));
</script>
