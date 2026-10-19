<?php
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$rows = $rows ?? [];
$columns = $rows ? array_keys($rows[0]) : [];
$summaryKey = null;
foreach (['total_amount', 'stock_value'] as $candidate) {
    if ($columns && in_array($candidate, $columns, true)) {
        $summaryKey = $candidate;
        break;
    }
}
$summaryTotal = 0.0;
$summaryByCurrency = [];
if ($summaryKey !== null) {
    foreach ($rows as $row) {
        if (!is_numeric($row[$summaryKey] ?? null)) continue;
        $amount = (float) $row[$summaryKey];
        $summaryTotal += $amount;
        if (isset($row['currency'])) {
            $currency = strtoupper((string) ($row['currency'] ?: 'USD'));
            $summaryByCurrency[$currency] = ($summaryByCurrency[$currency] ?? 0) + $amount;
        }
    }
}
$summaryDisplay = number_format($summaryTotal, 2);
if ($summaryByCurrency) {
    $currencyTotals = [];
    foreach ($summaryByCurrency as $currency => $amount) {
        $currencyTotals[] = $currency . ' ' . number_format($amount, 2);
    }
    $summaryDisplay = implode(' · ', $currencyTotals);
}
$period = (string) ($period ?? 'all');
$reportPeriod = (string) ($reportPeriod ?? 'All available records');
$baseUrl = '/index.php?controller=reports&action=export&type=' . urlencode((string) ($reportType ?? 'Sales summary')) . '&period=' . urlencode($period);
if ($period === 'custom') $baseUrl .= '&start_date=' . urlencode((string) ($startDate ?? '')) . '&end_date=' . urlencode((string) ($endDate ?? ''));
?>
<div class="report-toolbar d-flex justify-content-between align-items-center gap-2 flex-wrap mb-4">
    <a class="btn btn-outline-secondary" href="/index.php?controller=reports"><i class="fa-solid fa-arrow-left me-2"></i>Back to reports</a>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="<?= $escape($baseUrl) ?>&amp;format=xlsx">Download Excel</a>
        <a class="btn btn-outline-primary" href="<?= $escape($baseUrl) ?>&amp;format=pdf">Download PDF</a>
        <a class="btn btn-outline-secondary" href="<?= $escape($baseUrl) ?>&amp;format=csv">Download CSV</a>
        <button class="btn btn-primary" type="button" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print</button>
    </div>
</div>
<article class="report-document">
    <header class="report-header">
        <div>
            <div class="report-kicker">iTrack Zimbabwe · Operations reporting</div>
            <h1><?= $escape($reportType ?? 'Business report') ?></h1>
            <p>Enterprise report · Internal business use</p>
        </div>
        <div class="report-date">
            <span>GENERATED</span><strong><?= $escape($generatedAt ?? date('Y-m-d H:i T')) ?></strong>
            <span>REPORT PERIOD</span><strong><?= $escape($reportPeriod) ?></strong>
        </div>
    </header>
    <section class="report-summary" aria-label="Report summary">
        <div class="summary-card"><span>REPORT TYPE</span><strong><?= $escape($reportType ?? '') ?></strong></div>
        <div class="summary-card"><span>RECORDS</span><strong><?= number_format(count($rows)) ?></strong></div>
        <?php if ($summaryKey !== null): ?>
            <div class="summary-card"><span><?= $escape(ucwords(str_replace('_', ' ', $summaryKey))) ?></span><strong><?= $escape($summaryDisplay) ?></strong></div>
        <?php endif; ?>
    </section>
    <section class="report-table-section">
        <div class="table-heading">
            <div><h2>Report detail</h2><p><?= number_format(count($rows)) ?> data records · generated from the current operational database</p></div>
            <span class="report-confidential">CONFIDENTIAL · INTERNAL</span>
        </div>
        <?php if (!$rows): ?>
            <div class="empty-report">No records matched this report. Try again after data has been entered.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table report-data-table">
                    <thead><tr><?php foreach ($columns as $column): ?><th scope="col"><?= $escape(ucwords(str_replace('_', ' ', $column))) ?></th><?php endforeach; ?></tr></thead>
                    <tbody><?php foreach ($rows as $row): ?><tr><?php foreach ($columns as $column): ?><?php $value = $row[$column] ?? ''; ?><td class="<?= is_numeric($value) ? 'numeric' : '' ?>"><?= $escape(ReportFormatter::displayValue((string) $column, $value)) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
    <footer class="report-footer"><span>iTrack Zimbabwe · System-generated report</span><span><?= $escape($generatedAt ?? date('Y-m-d H:i T')) ?></span></footer>
</article>
<style>
.report-toolbar{max-width:1250px;margin:0 auto 18px}.report-document{max-width:1250px;margin:0 auto;background:#fff;border:1px solid #dfe5ed;border-radius:16px;padding:30px 34px;box-shadow:0 16px 45px rgba(20,35,60,.07);color:#1d2d42}.report-header{display:flex;justify-content:space-between;gap:24px;border-bottom:2px solid #243e60;padding-bottom:22px}.report-kicker{font-size:10px;text-transform:uppercase;letter-spacing:.16em;font-weight:800;color:#6d7f96}.report-header h1{font-size:27px;font-weight:850;letter-spacing:-.04em;color:#193553;margin:10px 0 4px}.report-header p{color:#728097;margin:0;font-size:12px}.report-date{display:grid;align-content:start;gap:3px;min-width:190px;text-align:right}.report-date span,.summary-card span{font-size:9px;letter-spacing:.1em;color:#718096;font-weight:800}.report-date strong{font-size:11px;color:#293e59;margin-bottom:7px}.report-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:20px 0 28px}.summary-card{border:1px solid #e2e8f0;border-radius:11px;background:#f7f9fc;padding:12px 14px;display:grid;gap:6px}.summary-card strong{font-size:16px;color:#203c5d}.table-heading{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:13px}.table-heading h2{font-size:15px;font-weight:850;color:#203b5a;margin:0}.table-heading p{font-size:10px;color:#8793a5;margin:4px 0 0}.report-confidential{font-size:8px;font-weight:800;letter-spacing:.08em;color:#66768e;background:#eff3f8;padding:6px 8px;border-radius:6px;white-space:nowrap}.report-data-table{font-size:11px;border-collapse:separate;border-spacing:0}.report-data-table thead th{background:#243e60;color:#fff;text-transform:uppercase;letter-spacing:.045em;font-size:9px;padding:10px 9px;border:0;white-space:nowrap}.report-data-table thead th:first-child{border-top-left-radius:8px}.report-data-table thead th:last-child{border-top-right-radius:8px}.report-data-table tbody td{padding:9px;border-bottom:1px solid #e9edf2;color:#42536b;vertical-align:top}.report-data-table tbody tr:nth-child(even) td{background:#f7f9fc}.report-data-table tbody td:first-child{font-weight:700;color:#233a57}.report-data-table .numeric{text-align:right;font-variant-numeric:tabular-nums}.empty-report{padding:30px;text-align:center;color:#6b788b;border:1px dashed #d3dce8;border-radius:10px}.report-footer{display:flex;justify-content:space-between;gap:20px;border-top:1px solid #e3e8ef;margin-top:24px;padding-top:12px;color:#7d899b;font-size:9px}
@media(max-width:700px){.report-document{padding:20px 14px}.report-header{flex-direction:column}.report-date{text-align:left}.report-summary{grid-template-columns:1fr}.table-heading{align-items:start;flex-direction:column}.report-data-table{min-width:680px}}
@media print{@page{size:A4 landscape;margin:12mm}body{background:white!important}.sidebar,.topbar,.report-toolbar,.notification-toast-stack{display:none!important}.app-shell,.main-area,.content-wrapper{display:block!important;width:auto!important;margin:0!important;padding:0!important}.report-document{max-width:none;border:0;border-radius:0;box-shadow:none;padding:0}.report-header{break-inside:avoid}.report-data-table thead{display:table-header-group}.report-data-table tr{break-inside:avoid}.report-data-table{font-size:8px}.report-data-table thead th,.report-data-table tbody td{padding:5px 6px}.report-footer{break-inside:avoid}}
</style>
