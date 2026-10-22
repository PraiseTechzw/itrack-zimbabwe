<?php
$title = 'Inventory';
$products = $products ?? [];
$totalUnits = array_sum(array_map(static fn($p) => (int)($p['opening_stock'] ?? 0), $products));
$inventoryValue = array_sum(array_map(static fn($p) => (int)($p['opening_stock'] ?? 0) * (float)($p['cost_price'] ?? 0), $products));
$lowStock = count(array_filter($products, static fn($p) => (int)($p['opening_stock'] ?? 0) <= (int)($p['reorder_level'] ?? 0)));
$categories = array_unique(array_map(static fn($p) => $p['item_category'] ?? 'Tracker', $products));
?>
<style>
.inventory-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:25px 28px;margin-bottom:20px;border-radius:18px;background:linear-gradient(115deg,#17243d,#334d7a);color:#fff}.inventory-hero h1{font-size:25px;font-weight:800;margin:0 0 6px}.inventory-hero p{color:#c3cee0;margin:0;font-size:13px}.inventory-hero .btn{white-space:nowrap}.inventory-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:20px}.inventory-stat{padding:17px 19px;border:1px solid #e6eaf0;border-radius:14px;background:#fff}.inventory-stat span{display:block;color:#78869a;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em}.inventory-stat strong{display:block;margin-top:7px;color:#203653;font-size:24px}.inventory-tools{display:flex;gap:10px;align-items:center;margin-bottom:15px}.inventory-tools .form-control{max-width:340px}.inventory-table{min-width:800px}.inventory-table thead th{font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#78869a;background:#f7f9fc;white-space:nowrap}.inventory-table tbody td{font-size:12px;color:#4d5e75}.inventory-item{font-weight:750;color:#263b59!important}.inventory-category{display:inline-flex;border-radius:999px;padding:5px 9px;background:#edf2f8;color:#50627c;font-size:10px;font-weight:700}.stock-pill{display:inline-flex;min-width:38px;justify-content:center;padding:5px 8px;border-radius:999px;background:#eaf7ef;color:#198754;font-weight:750}.stock-pill.low{background:#fff3df;color:#aa6c00}.inventory-empty{text-align:center;padding:50px 20px;color:#8391a5}.inventory-empty i{display:block;font-size:28px;margin-bottom:10px;color:#aab5c4}@media(max-width:650px){.inventory-hero{align-items:flex-start;flex-direction:column;padding:21px}.inventory-stats{grid-template-columns:1fr}.inventory-tools{align-items:stretch;flex-direction:column}.inventory-tools .form-control{max-width:none}}
</style>
<section class="inventory-hero">
    <div><h1>Inventory</h1><p>Track stock levels, unit prices, and equipment available for installations.</p></div>
    <div class="d-flex gap-2"><a class="btn btn-light" href="/index.php?controller=inventory&action=createBulk"><i class="fa-solid fa-layer-group me-2"></i>Bulk add</a><a class="btn btn-primary" href="/index.php?controller=inventory&action=create"><i class="fa-solid fa-plus me-2"></i>Add item</a></div>
</section>
<div class="inventory-stats">
    <div class="inventory-stat"><span>Product types</span><strong><?= count($products) ?></strong></div>
    <div class="inventory-stat"><span>Total units in stock</span><strong><?= number_format($totalUnits) ?></strong></div>
    <div class="inventory-stat"><span>Stock value · low stock</span><strong>$<?= number_format($inventoryValue, 2) ?></strong><small class="text-secondary"><?= $lowStock ?> item<?= $lowStock === 1 ? '' : 's' ?> at reorder level</small></div>
</div>
<div class="card shadow-sm border-0"><div class="card-body p-3 p-lg-4">
    <div class="inventory-tools"><input class="form-control" id="inventory-search" type="search" placeholder="Search item, type, or serial / IMEI"><select class="form-select" id="inventory-category" style="max-width:220px"><option value="">All categories</option><?php foreach ($categories as $category): ?><option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select><span class="ms-auto small text-secondary" id="inventory-count"><?= count($products) ?> items</span></div>
    <div class="table-responsive"><table class="table inventory-table align-middle mb-0"><thead><tr><th>Category</th><th>Item type</th><th>Serial / IMEI</th><th>Unit</th><th>Cost</th><th>Selling</th><th>In stock</th><th></th></tr></thead><tbody id="inventory-rows">
    <?php foreach ($products as $product): $category = $product['item_category'] ?? 'Tracker'; $name = $product['item_type'] ?? $product['device_type'] ?? '—'; $stock = (int)($product['opening_stock'] ?? 0); $reorder = (int)($product['reorder_level'] ?? 0); ?>
    <tr data-category="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" data-search="<?= htmlspecialchars(strtolower($category.' '.$name.' '.($product['imei'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"><td><span class="inventory-category"><?= htmlspecialchars($category) ?></span></td><td class="inventory-item"><?= htmlspecialchars($name) ?></td><td><?= htmlspecialchars(($product['imei'] ?? '') !== '' ? $product['imei'] : '—') ?></td><td><?= htmlspecialchars($product['unit'] ?? 'pcs') ?></td><td>$<?= number_format((float)($product['cost_price'] ?? 0), 2) ?></td><td>$<?= number_format((float)($product['selling_price'] ?? 0), 2) ?></td><td><span class="stock-pill <?= $stock <= $reorder ? 'low' : '' ?>"><?= number_format($stock) ?></span></td><td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="/index.php?controller=inventory&action=edit&id=<?= (int)$product['id'] ?>">Edit</a> <a class="btn btn-sm btn-outline-danger" href="/index.php?controller=inventory&action=delete&id=<?= (int)$product['id'] ?>" data-confirm="Delete this product?">Delete</a></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php if (!$products): ?><div class="inventory-empty"><i class="fa-solid fa-box-open"></i><strong>No inventory items yet</strong><div>Add an item or use bulk add to enter several stock lines.</div><a class="btn btn-primary btn-sm mt-3" href="/index.php?controller=inventory&action=createBulk">Bulk add inventory</a></div><?php endif; ?>
    <div class="inventory-empty d-none" id="inventory-no-results">No items match your search.</div></div>
</div></div>
<script>
const invSearch=document.getElementById('inventory-search'),invCategory=document.getElementById('inventory-category'),invRows=[...document.querySelectorAll('#inventory-rows tr')];
function filterInventory(){const q=invSearch.value.trim().toLowerCase(),cat=invCategory.value;let shown=0;invRows.forEach(row=>{const match=(!q||row.dataset.search.includes(q))&&(!cat||row.dataset.category===cat);row.classList.toggle('d-none',!match);if(match)shown++;});document.getElementById('inventory-count').textContent=shown+' item'+(shown===1?'':'s');document.getElementById('inventory-no-results').classList.toggle('d-none',shown>0||invRows.length===0);}
invSearch.addEventListener('input',filterInventory);invCategory.addEventListener('change',filterInventory);
</script>
