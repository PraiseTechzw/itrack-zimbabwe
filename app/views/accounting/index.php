<?php $title = 'Accounting'; ?>
<div class="row g-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Invoices</h6>
                <h3 class="fw-bold"><?= (int) ($summaries['invoices'] ?? 0) ?></h3>
                <p class="mb-0 text-muted">Total invoices recorded in the system.</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Payments</h6>
                <h3 class="fw-bold"><?= (int) ($summaries['payments'] ?? 0) ?></h3>
                <p class="mb-0 text-muted">Recorded customer payments.</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Expenses</h6>
                <h3 class="fw-bold"><?= (int) ($summaries['expenses'] ?? 0) ?></h3>
                <p class="mb-0 text-muted">Expense entries tracked through accounting.</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Cash Book Balance</h6>
                <h3 class="fw-bold">$<?= number_format((float) ($summaries['cash_balance'] ?? 0), 2) ?></h3>
                <p class="mb-0 text-muted">Latest balance from cash book entries.</p>
            </div>
        </div>
    </div>
</div>
<div class="row g-4 mt-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Recent invoices</h5>
                </div>
                <?php if (empty($recentInvoices)): ?>
                    <p class="text-muted mb-0">No invoices have been created yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInvoices as $invoice): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($invoice['invoice_number'] ?? '') ?></td>
                                        <td>$<?= number_format((float) ($invoice['total_amount'] ?? 0), 2) ?></td>
                                        <td><?= htmlspecialchars($invoice['status'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($invoice['created_at'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Recent payments</h5>
                </div>
                <?php if (empty($recentPayments)): ?>
                    <p class="text-muted mb-0">No payments have been recorded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPayments as $payment): ?>
                                    <tr>
                                        <td>$<?= number_format((float) ($payment['amount'] ?? 0), 2) ?></td>
                                        <td><?= htmlspecialchars($payment['method'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($payment['payment_date'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="card mt-4 border-0">
    <div class="card-body">
        <h5 class="card-title">Accounting module</h5>
        <p class="text-muted">Invoices, payments, expenses, petty cash, and cash book records are visible here for finance users. Expand these lists into full CRUD workflows as needed.</p>
    </div>
</div>