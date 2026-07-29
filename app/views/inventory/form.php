<?php $title = 'Create Product'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h4 mb-4">Create Product</h2>
                <form method="post" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cost Price</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Opening Stock</label>
                        <input type="number" name="opening_stock" class="form-control" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reorder Level</label>
                        <input type="number" name="reorder_level" class="form-control" value="0">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
