<?php
$title = 'Accounting';
$summaryCards = [
    ['label' => 'Invoiced', 'value' => '$' . number_format((float) ($summaries['invoice_total'] ?? 0), 2), 'meta' => number_format((int) ($summaries['invoices'] ?? 0)) . ' invoices', 'icon' => 'fa-file-invoice', 'tone' => '#4f46e5', 'href' => 'invoices'],
    ['label' => 'Payments received', 'value' => '$' . number_format((float) ($summaries['payment_total'] ?? 0), 2), 'meta' => number_format((int) ($summaries['payments'] ?? 0)) . ' payments', 'icon' => 'fa-arrow-down-to-bracket', 'tone' => '#16845b', 'href' => 'payments'],
    ['label' => 'Expenses', 'value' => '$' . number_format((float) ($summaries['expense_total'] ?? 0), 2), 'meta' => number_format((int) ($summaries['expenses'] ?? 0)) . ' expense records', 'icon' => 'fa-arrow-up-from-bracket', 'tone' => '#bd3f45', 'href' => 'expenses'],
    ['label' => 'Cash book balance', 'value' => '$' . number_format((float) ($summaries['cash_balance'] ?? 0), 2), 'meta' => 'Latest recorded balance', 'icon' => 'fa-book-open', 'tone' => '#b7791f', 'href' => 'cashBook'],
];
$accountingWorkflows = [
    ['label' => 'Invoices', 'detail' => number_format((int) ($summaries['invoices'] ?? 0)) . ' recorded', 'icon' => 'fa-file-invoice', 'action' => 'invoices', 'create' => 'invoiceCreate'],
    ['label' => 'Payments', 'detail' => number_format((int) ($summaries['payments'] ?? 0)) . ' recorded', 'icon' => 'fa-money-bill-transfer', 'action' => 'payments', 'create' => 'paymentCreate'],
    ['label' => 'Expenses', 'detail' => number_format((int) ($summaries['expenses'] ?? 0)) . ' recorded', 'icon' => 'fa-receipt', 'action' => 'expenses', 'create' => 'expenseCreate'],
    ['label' => 'Petty cash', 'detail' => number_format((int) ($summaries['petty_cash'] ?? 0)) . ' entries · $' . number_format((float) ($summaries['petty_cash_total'] ?? 0), 2), 'icon' => 'fa-wallet', 'action' => 'pettyCash', 'create' => 'pettyCashCreate'],
    ['label' => 'Cash book', 'detail' => 'Track cash movements', 'icon' => 'fa-book-open', 'action' => 'cashBook', 'create' => 'cashBookCreate'],
];
?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
    <div>
        <div class="eyebrow mb-2">Finance workspace</div>
        <h1 class="page-title">Accounts overview</h1>
        <p class="page-subtitle">Monitor invoices, incoming payments, expenses and cash movements.</p>
    </div>
    <a class="btn btn-primary" href="/index.php?controller=accounting&action=invoiceCreate"><i class="fa-solid fa-plus me-2"></i>Create invoice</a>
</div>

<div class="stats-grid mb-4">
    <?php foreach ($summaryCards as $card): ?>
        <a class="card metric-card text-decoration-none" href="/index.php?controller=accounting&action=<?= htmlspecialchars($card['href'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="card-body">
                <div class="metric-head">
                    <span class="metric-label"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="metric-icon" style="color:<?= htmlspecialchars($card['tone'], ENT_QUOTES, 'UTF-8') ?>;background:<?= htmlspecialchars($card['tone'], ENT_QUOTES, 'UTF-8') ?>12"><i class="fa-solid <?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                </div>
                <div class="metric-value"><?= htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="metric-meta"><?= htmlspecialchars($card['meta'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<section class="card mb-4">
    <div class="card-body">
        <div class="section-heading">
            <div><div class="eyebrow">Manage your books</div><h2 class="mt-1">Accounting workflows</h2></div>
            <i class="fa-solid fa-arrow-trend-up" style="color:#4f46e5"></i>
        </div>
        <div class="row g-3">
            <?php foreach ($accountingWorkflows as $workflow): ?>
                <div class="col-sm-6 col-xl">
                    <div class="quick-action h-100">
                        <a class="d-flex align-items-center gap-2 text-decoration-none flex-grow-1" href="/index.php?controller=accounting&action=<?= htmlspecialchars($workflow['action'], ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fa-solid <?= htmlspecialchars($workflow['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                            <span><?= htmlspecialchars($workflow['label'], ENT_QUOTES, 'UTF-8') ?><small class="d-block fw-normal text-muted mt-1"><?= htmlspecialchars($workflow['detail'], ENT_QUOTES, 'UTF-8') ?></small></span>
                        </a>
                        <a class="btn btn-sm btn-light ms-2" href="/index.php?controller=accounting&action=<?= htmlspecialchars($workflow['create'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Create <?= htmlspecialchars(strtolower($workflow['label']), ENT_QUOTES, 'UTF-8') ?>"><i class="fa-solid fa-plus"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="row g-4">
    <div class="col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <div class="section-heading">
                    <div><div class="eyebrow">Receivables</div><h2 class="mt-1">Recent invoices</h2></div>
                    <a class="btn btn-sm btn-light" href="/index.php?controller=accounting&action=invoices">View all</a>
                </div>
                <?php if (empty($recentInvoices)): ?>
                    <p class="text-muted mb-0">No invoices have been created yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Invoice</th><th>Due date</th><th>Status</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentInvoices as $invoice): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($invoice['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($invoice['due_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge text-bg-light"><?= htmlspecialchars(ucfirst((string) ($invoice['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td class="text-end">$<?= number_format((float) ($invoice['total_amount'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <div class="section-heading">
                    <div><div class="eyebrow">Incoming funds</div><h2 class="mt-1">Recent payments</h2></div>
                    <a class="btn btn-sm btn-light" href="/index.php?controller=accounting&action=payments">View all</a>
                </div>
                <?php if (empty($recentPayments)): ?>
                    <p class="text-muted mb-0">No payments have been recorded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Reference</th><th>Method</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentPayments as $payment): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars(($payment['reference'] ?? '') ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($payment['method'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($payment['payment_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-end">$<?= number_format((float) ($payment['amount'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <div class="section-heading">
                    <div><div class="eyebrow">Outgoing funds</div><h2 class="mt-1">Recent expenses</h2></div>
                    <a class="btn btn-sm btn-light" href="/index.php?controller=accounting&action=expenses">View all</a>
                </div>
                <?php if (empty($recentExpenses)): ?>
                    <p class="text-muted mb-0">No expenses have been recorded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Category</th><th>Paid to</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentExpenses as $expense): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($expense['category'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($expense['paid_to'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($expense['payment_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-end">$<?= number_format((float) ($expense['amount'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <div class="section-heading">
                    <div><div class="eyebrow">Cash position</div><h2 class="mt-1">Cash book activity</h2></div>
                    <a class="btn btn-sm btn-light" href="/index.php?controller=accounting&action=cashBook">View all</a>
                </div>
                <?php if (empty($recentCashBook)): ?>
                    <p class="text-muted mb-0">No cash book entries have been recorded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Description</th><th>Date</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentCashBook as $entry): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($entry['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($entry['entry_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-end"><?= (float) ($entry['debit'] ?? 0) > 0 ? '$' . number_format((float) $entry['debit'], 2) : '—' ?></td>
                                        <td class="text-end"><?= (float) ($entry['credit'] ?? 0) > 0 ? '$' . number_format((float) $entry['credit'], 2) : '—' ?></td>
                                        <td class="text-end">$<?= number_format((float) ($entry['balance'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <div class="section-heading">
                    <div><div class="eyebrow">Staff advances</div><h2 class="mt-1">Recent petty cash</h2></div>
                    <a class="btn btn-sm btn-light" href="/index.php?controller=accounting&action=pettyCash">View all</a>
                </div>
                <?php if (empty($recentPettyCash)): ?>
                    <p class="text-muted mb-0">No petty cash entries have been recorded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Type</th><th>Description</th><th>Date</th><th>Status</th><th class="text-end">Amount</th></tr></thead>
                            <tbody>
                                <?php foreach ($recentPettyCash as $entry): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars(ucfirst((string) ($entry['type'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($entry['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($entry['transaction_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge text-bg-light"><?= htmlspecialchars(ucfirst((string) ($entry['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td class="text-end">$<?= number_format((float) ($entry['amount'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
