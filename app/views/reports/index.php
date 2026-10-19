<section class="reports-hero mb-4"><div class="reports-hero-copy"><span class="reports-hero-kicker"><i class="fa-solid fa-chart-pie me-2" aria-hidden="true"></i>iTrack · Reporting center</span><h1>Reports &amp; insights</h1><p>Turn your business activity into clear, useful decisions.</p></div><div class="reports-hero-meta"><span class="reports-hero-count"><strong><?= number_format($reportCount ?? count($reports ?? [])) ?></strong><small>saved reports</small></span><a href="#create-report" class="btn reports-hero-button"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>Create report</a></div><div class="reports-hero-art" aria-hidden="true"><i class="fa-solid fa-chart-column"></i><i class="fa-solid fa-chart-line"></i><i class="fa-solid fa-chart-pie"></i></div></section>
<?php $reportShortcuts = ['Sales summary'=>['fa-chart-line','Sales performance and customer activity'], 'Inventory valuation'=>['fa-boxes-stacked','Stock quantity and value'], 'Receivables'=>['fa-file-invoice-dollar','Invoices and outstanding balances'], 'GPS activity'=>['fa-location-dot','Device and installation activity'], 'Cash book'=>['fa-book-open','Recorded cash movements']]; ?>
<section class="report-shortcuts mb-4" aria-label="Choose a report type"><div class="report-shortcuts-heading"><div><span class="eyebrow">Start with a dataset</span><h2>What do you want to review?</h2></div><span>Choose a report to set up</span></div><div class="report-shortcut-grid"><?php foreach (($reportTypes ?? []) as $type): $shortcut = $reportShortcuts[$type] ?? ['fa-chart-simple','View report data']; ?><button type="button" class="report-shortcut" data-report-type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"><span class="report-shortcut-icon"><i class="fa-solid <?= htmlspecialchars($shortcut[0], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span><span class="report-shortcut-copy"><strong><?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($shortcut[1], ENT_QUOTES, 'UTF-8') ?></small></span><i class="fa-solid fa-arrow-right report-shortcut-arrow" aria-hidden="true"></i></button><?php endforeach; ?></div></section>
<section class="report-insights card mb-4" aria-labelledby="report-insights-title">
	<div class="card-body">
		<div class="report-insights-layout">
			<div class="report-insights-intro">
				<div class="report-insights-icon" aria-hidden="true"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
				<div class="eyebrow mb-2">Decision support</div>
				<h2 id="report-insights-title">AI business insights</h2>
				<p>Ask a question about recent sales, stock levels, or purchase orders. Get a concise summary to support your operational decisions.</p>
				<div class="report-insights-topics" aria-label="Available insight topics">
					<span><i class="fa-solid fa-chart-line" aria-hidden="true"></i>Sales</span>
					<span><i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>Inventory</span>
					<span><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>Purchases</span>
				</div>
			</div>
			<div class="report-insights-workspace">
				<form id="ai-insights-form" class="vstack gap-3" action="?controller=reports&amp;action=ask" method="post">
					<?= $this->csrfField() ?>
					<label class="form-label mb-0" for="ai-insights-question">What would you like to know?</label>
					<textarea class="form-control" id="ai-insights-question" name="question" rows="3" maxlength="1000" placeholder="For example: What should I pay attention to in recent sales and stock levels?" required></textarea>
					<div class="report-prompt-list" aria-label="Suggested questions"><button type="button" data-question="Compare sales in the last 30 days with the previous 30 days and summarize the main changes.">Sales trend</button><button type="button" data-question="Which products need attention based on current stock and reorder levels?">Low stock</button><button type="button" data-question="Summarize purchase orders by status and point out any pending commitments.">Purchase orders</button></div>
					<div class="report-insights-form-footer">
						<span class="report-insights-privacy"><i class="fa-solid fa-lock" aria-hidden="true"></i>Aggregated data only. Avoid personal or confidential information.</span>
						<button class="btn btn-primary flex-shrink-0" id="ai-insights-submit" type="submit"><i class="fa-solid fa-paper-plane me-2" aria-hidden="true"></i>Get insights</button>
					</div>
					<span class="small text-secondary" id="ai-insights-status" role="status" aria-live="polite"></span>
				</form>
			</div>
		</div>
		<div class="report-insights-answer d-none" id="ai-insights-answer" role="region" aria-label="AI response" aria-live="polite">
			<div class="report-insights-answer-heading"><span class="report-insights-answer-icon"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i></span><div><div class="eyebrow">Analysis</div><h3 class="mb-0">Business insights</h3></div></div>
			<div class="ai-insights-markdown"></div>
		</div>
	</div>
</section>
<div class="ai-toast-stack" id="ai-toast-stack" aria-live="polite" aria-relevant="additions"></div>
<?php if (!empty($reportError)): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($reportError, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<div class="row g-4">
	<div class="col-lg-4" id="create-report">
		<div class="card report-generation-card"><div class="card-body">
			<div class="report-card-heading"><span class="report-card-icon"><i class="fa-solid fa-sliders" aria-hidden="true"></i></span><div><h2>Create a report</h2><p>Choose a dataset and reporting period.</p></div></div>
			<form method="post" action="?controller=reports&amp;action=generate" class="vstack gap-3" id="report-generation-form">
				<?= $this->csrfField() ?>
				<div><label class="form-label" for="report-name">Report name</label><input class="form-control" id="report-name" name="report_name" placeholder="e.g. October sales review" maxlength="120" required></div>
				<div><label class="form-label" for="report-type">Report type</label><select class="form-select" id="report-type" name="report_type" required><?php foreach (($reportTypes ?? []) as $type): ?><option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
				<label class="form-label mb-0" for="report-period">Reporting period</label>
				<select class="form-select" id="report-period" name="period" required><?php foreach (($reportPeriods ?? []) as $key => $label): ?><option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $key === 'custom' ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
				<fieldset class="row g-2 border-0 p-0 m-0" id="report-date-range">
					<div class="col-sm-6"><label class="form-label mb-1" for="report-start-date">Start date</label><input class="form-control" type="date" id="report-start-date" name="start_date"></div>
					<div class="col-sm-6"><label class="form-label mb-1" for="report-end-date">End date</label><input class="form-control" type="date" id="report-end-date" name="end_date"></div>
				</fieldset>
				<div class="form-text" id="report-date-help">Choose a reporting period. Custom ranges must fall within the available dates. Inventory valuation is always a current stock snapshot.</div>
				<button class="btn btn-primary"><i class="fa-solid fa-file-circle-plus me-2" aria-hidden="true"></i>Generate report</button>
			</form>
		</div></div>
	</div>
	<div class="col-lg-8">
		<div class="card report-history-card"><div class="card-body">
			<div class="report-card-heading"><span class="report-card-icon report-card-icon-muted"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></span><div><h2>Saved reports</h2><p>Open a preview or download a copy.</p></div></div>
			<div class="table-responsive"><table class="table report-history-table"><thead><tr><th>Name</th><th>Type</th><th>Period</th><th>Generated by</th><th>Date</th><th>Files</th></tr></thead><tbody>
				<?php foreach ($reports as $r):
					$period = $reportPeriods[$r['period'] ?? 'all'] ?? $reportPeriods['all'];
					if (!empty($r['period_label'])) $period = $r['period_label'];
					$dateQuery = ($r['period'] ?? '') === 'custom'
						? '&amp;start_date=' . urlencode($r['start_date'] ?? '') . '&amp;end_date=' . urlencode($r['end_date'] ?? '')
						: '';
					$reportTypeQuery = '&amp;type=' . urlencode($r['report_type']);
					$reportPeriodQuery = '&amp;period=' . urlencode($r['period'] ?? 'all') . $dateQuery;
				?>
					<tr><td class="report-name-cell"><?= htmlspecialchars($r['report_name'], ENT_QUOTES, 'UTF-8') ?></td><td><span class="report-type-badge"><?= htmlspecialchars($r['report_type'], ENT_QUOTES, 'UTF-8') ?></span></td><td><?= htmlspecialchars($period, ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($r['generated_by_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($r['generated_at'], ENT_QUOTES, 'UTF-8') ?></td><td>
						<div class="report-file-actions"><a class="btn btn-sm btn-primary" href="?controller=reports&amp;action=preview<?= $reportTypeQuery . $reportPeriodQuery ?>">Preview</a><a class="btn btn-sm btn-outline-secondary" href="?controller=reports&amp;action=export<?= $reportTypeQuery . $reportPeriodQuery ?>&amp;format=xlsx" aria-label="Download Excel">XLSX</a><a class="btn btn-sm btn-outline-secondary" href="?controller=reports&amp;action=export<?= $reportTypeQuery . $reportPeriodQuery ?>&amp;format=pdf" aria-label="Download PDF">PDF</a><a class="btn btn-sm btn-outline-secondary" href="?controller=reports&amp;action=export<?= $reportTypeQuery . $reportPeriodQuery ?>&amp;format=csv" aria-label="Download CSV">CSV</a></div>
					</td></tr>
				<?php endforeach; ?>
				<?php if (empty($reports)): ?><tr><td colspan="6"><div class="report-empty-state"><i class="fa-regular fa-folder-open" aria-hidden="true"></i><strong>No saved reports yet</strong><span>Create a report to preview it and download a copy.</span></div></td></tr><?php endif; ?>
			</tbody></table></div>
			<?php if (($reportPageCount ?? 1) > 1): $firstReport = (($reportPage - 1) * 10) + 1; $lastReport = min($reportPage * 10, $reportCount); ?>
				<div class="report-pagination"><span>Showing <?= number_format($firstReport) ?>–<?= number_format($lastReport) ?> of <?= number_format($reportCount) ?> reports</span><nav aria-label="Saved report pages"><ul class="pagination pagination-sm mb-0">
					<li class="page-item <?= $reportPage <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?controller=reports&amp;page=<?= max(1, $reportPage - 1) ?>" aria-label="Previous page">‹</a></li>
					<?php for ($pageNumber = max(1, $reportPage - 2); $pageNumber <= min($reportPageCount, $reportPage + 2); $pageNumber++): ?><li class="page-item <?= $pageNumber === $reportPage ? 'active' : '' ?>"><a class="page-link" href="?controller=reports&amp;page=<?= $pageNumber ?>"><?= $pageNumber ?></a></li><?php endfor; ?>
					<li class="page-item <?= $reportPage >= $reportPageCount ? 'disabled' : '' ?>"><a class="page-link" href="?controller=reports&amp;page=<?= min($reportPageCount, $reportPage + 1) ?>" aria-label="Next page">›</a></li>
				</ul></nav></div>
			<?php endif; ?>
		</div></div>
	</div>
</div>
<script>
(() => {
	const reportType = document.getElementById('report-type');
	const startDate = document.getElementById('report-start-date');
	const endDate = document.getElementById('report-end-date');
	const period = document.getElementById('report-period');
	const dateHelp = document.getElementById('report-date-help');
	const dateBounds = <?= json_encode($reportDateBounds ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
	const today = <?= json_encode($reportToday ?? date('Y-m-d')) ?>;
	const reportName = document.getElementById('report-name');
	let lastSuggestedName = '';
	const suggestReportName = () => {
		const label = reportType.value === 'Inventory valuation' ? 'Current stock' : (period.options[period.selectedIndex]?.text || 'Report');
		const suggested = `${reportType.value} · ${label}`;
		if (!reportName.value || reportName.value === lastSuggestedName) reportName.value = suggested;
		lastSuggestedName = suggested;
	};
	document.querySelectorAll('[data-report-type]').forEach(button => button.addEventListener('click', () => {
		reportType.value = button.dataset.reportType;
		updateDateFields();
		document.getElementById('create-report').scrollIntoView({ behavior: 'smooth', block: 'center' });
		button.blur();
	}));

	const updateDateFields = () => {
		const isInventory = reportType.value === 'Inventory valuation';
		const isCustom = !isInventory && period.value === 'custom';
		period.disabled = isInventory;
		startDate.disabled = !isCustom;
		endDate.disabled = !isCustom;
		startDate.required = isCustom;
		endDate.required = isCustom;
		dateHelp.textContent = isInventory
			? 'Inventory valuation is always a current stock snapshot.'
			: (isCustom ? 'Choose a start and end date within the available records.' : 'The selected period is calculated from the current date.');
		const bounds = dateBounds[reportType.value] || { min: today, max: today };
		if (startDate.value && startDate.value < bounds.min) startDate.value = bounds.min;
		if (startDate.value && startDate.value > bounds.max) startDate.value = bounds.max;
		if (endDate.value && endDate.value > bounds.max) endDate.value = bounds.max;
		if (endDate.value && startDate.value && endDate.value < startDate.value) endDate.value = startDate.value;
		startDate.min = bounds.min;
		endDate.min = startDate.value || startDate.min;
		startDate.max = bounds.max;
		endDate.max = bounds.max;
		if (isCustom && !startDate.value) {
			const recentStart = new Date(`${bounds.max}T00:00:00`);
			recentStart.setDate(recentStart.getDate() - 29);
			const recentDate = `${recentStart.getFullYear()}-${String(recentStart.getMonth() + 1).padStart(2, '0')}-${String(recentStart.getDate()).padStart(2, '0')}`;
			startDate.value = recentDate < bounds.min ? bounds.min : recentDate;
			endDate.value = bounds.max;
		}
		suggestReportName();
	};

	reportType.addEventListener('change', updateDateFields);
	period.addEventListener('change', updateDateFields);
	document.querySelectorAll('.report-prompt-list [data-question]').forEach(button => button.addEventListener('click', () => {
		document.getElementById('ai-insights-question').value = button.dataset.question;
		document.getElementById('ai-insights-question').focus();
	}));
	startDate.addEventListener('change', () => {
		endDate.min = startDate.value || startDate.min;
		if (endDate.value && endDate.value < endDate.min) endDate.value = endDate.min;
	});
	updateDateFields();
})();
</script>
<style>
.reports-page-heading{display:flex;align-items:center;justify-content:space-between;gap:20px}.reports-count{display:flex;align-items:center;gap:12px;padding:11px 15px;border:1px solid #e3e8f0;border-radius:12px;background:#fff;color:#53647d}.reports-count>i{font-size:18px;color:#5267ce}.reports-count span{display:grid;line-height:1.2}.reports-count strong{font-size:17px;color:#203653}.reports-count small{font-size:10px;color:#7e8ba0}.report-insights{border-radius:16px}.reports-page .card,.report-card-heading{border-radius:14px}.report-card-heading{display:flex;align-items:center;gap:12px;margin-bottom:20px}.report-card-heading h2{font-size:17px;font-weight:750;color:#203653;margin:0}.report-card-heading p{font-size:12px;color:#75839a;margin:3px 0 0}.report-card-icon{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;background:#eef1ff;color:#5367d5}.report-card-icon-muted{background:#f0f3f7;color:#65758d}.report-history-table{min-width:760px;font-size:12px;vertical-align:middle}.report-history-table thead th{font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#78859a;background:#f7f9fc;border-bottom-color:#e8edf3;padding:11px 10px;white-space:nowrap}.report-history-table tbody td{padding:12px 10px;border-bottom-color:#edf0f5;color:#506079}.report-history-table tbody tr:last-child td{border-bottom:0}.report-name-cell{font-weight:700;color:#203653!important;min-width:145px}.report-type-badge{display:inline-flex;padding:5px 8px;border-radius:7px;background:#eef2f8;color:#465a75;font-size:10px;font-weight:700;white-space:nowrap}.report-file-actions{display:flex;gap:5px}.report-file-actions .btn{font-size:10px}.report-empty-state{min-height:180px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:7px;color:#8290a4}.report-empty-state i{font-size:25px;color:#aab5c4}.report-empty-state strong{font-size:13px;color:#465a75}.report-empty-state span{font-size:11px}@media(max-width:700px){.reports-page-heading{align-items:flex-start;flex-direction:column}.reports-count{width:100%}}
.reports-hero{position:relative;overflow:hidden;display:flex;justify-content:space-between;align-items:center;gap:24px;min-height:190px;padding:30px 34px;border-radius:20px;background:linear-gradient(115deg,#111d36 0%,#1b2d50 58%,#39478e 100%);color:#fff;box-shadow:0 18px 42px rgba(22,39,76,.18)}.reports-hero-copy,.reports-hero-meta{position:relative;z-index:1}.reports-hero-kicker{display:inline-flex;align-items:center;padding:6px 10px;border:1px solid #ffffff30;border-radius:999px;background:#ffffff0d;color:#c9d3ef;font-size:10px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}.reports-hero h1{margin:16px 0 7px;font-size:clamp(28px,3vw,39px);font-weight:850;letter-spacing:-.045em}.reports-hero p{margin:0;color:#bac7df;font-size:13px}.reports-hero-meta{display:flex;align-items:center;gap:18px}.reports-hero-count{display:grid;padding-right:18px;border-right:1px solid #ffffff30;text-align:right}.reports-hero-count strong{font-size:24px;line-height:1.1}.reports-hero-count small{color:#bac7df;font-size:10px}.reports-hero-button{background:#fff;color:#253761;white-space:nowrap}.reports-hero-button:hover{background:#eef1ff;color:#253761}.reports-hero-art{position:absolute;right:23%;top:-94px;width:250px;height:250px;border:1px solid #ffffff0c;border-radius:50%;color:#ffffff0b}.reports-hero-art i{position:absolute;font-size:80px}.reports-hero-art i:first-child{left:30px;top:65px}.reports-hero-art i:nth-child(2){left:115px;top:23px}.reports-hero-art i:last-child{left:115px;top:120px}.report-shortcuts{padding:20px;border:1px solid #e5eaf1;border-radius:16px;background:#fff;box-shadow:0 8px 25px rgba(24,44,75,.04)}.report-shortcuts-heading{display:flex;align-items:end;justify-content:space-between;gap:16px;margin-bottom:15px}.report-shortcuts-heading h2{margin:4px 0 0;color:#203653;font-size:16px;font-weight:800;letter-spacing:-.025em}.report-shortcuts-heading>span{color:#8995a7;font-size:10px}.report-shortcut-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px}.report-shortcut{display:flex;align-items:center;gap:10px;min-width:0;padding:12px;border:1px solid #e7ebf2;border-radius:12px;background:#fff;text-align:left;transition:.18s ease}.report-shortcut:hover,.report-shortcut:focus-visible{border-color:#b9b6f5;background:#fafaff;box-shadow:0 5px 15px #4f46e514;transform:translateY(-2px);outline:0}.report-shortcut-icon{width:34px;height:34px;flex:0 0 34px;display:grid;place-items:center;border-radius:10px;background:#f0f1ff;color:#565bd0}.report-shortcut-copy{display:grid;min-width:0;gap:3px}.report-shortcut-copy strong{overflow:hidden;color:#283b57;font-size:10px;text-overflow:ellipsis;white-space:nowrap}.report-shortcut-copy small{display:-webkit-box;overflow:hidden;color:#8290a4;font-size:9px;line-height:1.35;-webkit-box-orient:vertical;-webkit-line-clamp:2}.report-shortcut-arrow{margin-left:auto;color:#a7b0bf;font-size:10px}.report-card-heading{display:flex;align-items:center;gap:12px;margin-bottom:20px}.report-card-heading h2{font-size:17px;font-weight:750;color:#203653;margin:0}.report-card-heading p{font-size:12px;color:#75839a;margin:3px 0 0}.report-card-icon{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;background:#eef1ff;color:#5367d5}.report-card-icon-muted{background:#f0f3f7;color:#65758d}.report-history-table{min-width:760px;font-size:12px;vertical-align:middle}.report-history-table thead th{font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#78859a;background:#f7f9fc;border-bottom-color:#e8edf3;padding:11px 10px;white-space:nowrap}.report-history-table tbody td{padding:12px 10px;border-bottom-color:#edf0f5;color:#506079}.report-history-table tbody tr:last-child td{border-bottom:0}.report-name-cell{font-weight:700;color:#203653!important;min-width:145px}.report-type-badge{display:inline-flex;padding:5px 8px;border-radius:7px;background:#eef2f8;color:#465a75;font-size:10px;font-weight:700;white-space:nowrap}.report-file-actions{display:flex;gap:5px}.report-file-actions .btn{font-size:10px}.report-empty-state{min-height:180px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:7px;color:#8290a4}.report-empty-state i{font-size:25px;color:#aab5c4}.report-empty-state strong{font-size:13px;color:#465a75}.report-empty-state span{font-size:11px}.report-generation-card,.report-history-card{height:100%;border:1px solid #e5eaf1;border-radius:15px;box-shadow:0 8px 25px rgba(24,44,75,.04)}.report-generation-card .card-body,.report-history-card .card-body{padding:22px}.report-generation-card .form-label{font-size:12px;font-weight:700;color:#45566e}.report-generation-card .btn-primary{padding:10px 14px;font-weight:700}.report-insights{box-shadow:0 8px 25px rgba(24,44,75,.04)}.report-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:16px;margin-top:4px;border-top:1px solid #edf0f5;color:#7a879a;font-size:11px}.report-pagination .page-link{color:#4d60c8;border-color:#e4e9f0}.report-pagination .active .page-link{background:#5267d5;border-color:#5267d5;color:#fff}.report-prompt-list{display:flex;flex-wrap:wrap;gap:7px}.report-prompt-list button{border:1px solid #e1e6f0;border-radius:999px;background:#fff;padding:5px 10px;color:#52617a;font-size:10px;font-weight:700;transition:.15s ease}.report-prompt-list button:hover,.report-prompt-list button:focus-visible{border-color:#7770df;background:#f5f4ff;color:#5147c8;outline:0}@media(max-width:1100px){.report-shortcut-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:700px){.reports-hero{align-items:flex-start;flex-direction:column;padding:25px 21px}.reports-hero-meta{width:100%;justify-content:space-between}.reports-hero-count{text-align:left}.reports-hero-art{right:-5px}.report-shortcuts{padding:16px}.report-shortcuts-heading{align-items:flex-start;flex-direction:column;gap:4px}.report-shortcut-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.report-generation-card .card-body,.report-history-card .card-body{padding:16px}}@media(max-width:420px){.report-shortcut-grid{grid-template-columns:1fr}.report-shortcut-copy strong{font-size:11px}.report-shortcut-copy small{font-size:10px}}
.report-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:16px;margin-top:4px;border-top:1px solid #edf0f5;color:#7a879a;font-size:11px}.report-pagination .page-link{color:#4d60c8;border-color:#e4e9f0}.report-pagination .active .page-link{background:#5267d5;border-color:#5267d5;color:#fff}@media(max-width:520px){.report-pagination{align-items:flex-start;flex-direction:column}}
.report-prompt-list{display:flex;flex-wrap:wrap;gap:7px}.report-prompt-list button{border:1px solid #e1e6f0;border-radius:999px;background:#fff;padding:5px 10px;color:#52617a;font-size:10px;font-weight:700;transition:.15s ease}.report-prompt-list button:hover,.report-prompt-list button:focus-visible{border-color:#7770df;background:#f5f4ff;color:#5147c8;outline:0}
</style>
<script>
(() => {
	const form = document.getElementById('ai-insights-form');
	const submit = document.getElementById('ai-insights-submit');
	const status = document.getElementById('ai-insights-status');
	const answer = document.getElementById('ai-insights-answer');
	const answerContent = answer.querySelector('.ai-insights-markdown');
	const toastStack = document.getElementById('ai-toast-stack');

	const showToast = (message, type = 'success') => {
		const toast = document.createElement('div');
		toast.className = `ai-toast ai-toast-${type}`;
		toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
		const icon = document.createElement('i');
		icon.className = type === 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check';
		icon.setAttribute('aria-hidden', 'true');
		const text = document.createElement('span');
		text.textContent = message;
		const close = document.createElement('button');
		close.type = 'button';
		close.className = 'ai-toast-close';
		close.setAttribute('aria-label', 'Dismiss notification');
		close.textContent = '\u00d7';
		close.addEventListener('click', () => toast.remove());
		toast.append(icon, text, close);
		toastStack.append(toast);
		window.setTimeout(() => toast.remove(), 6000);
	};

	const appendInlineMarkdown = (parent, text) => {
		const pattern = /(\*\*[^*]+\*\*|__[^_]+__|`[^`]+`|\*[^*]+\*|_[^_]+_)/g;
		let cursor = 0;

		for (const match of text.matchAll(pattern)) {
			parent.append(document.createTextNode(text.slice(cursor, match.index)));
			const token = match[0];
			const isBold = token.startsWith('**') || token.startsWith('__');
			const isCode = token.startsWith('`');
			const element = document.createElement(isBold ? 'strong' : isCode ? 'code' : 'em');
			element.textContent = token.slice(isBold ? 2 : 1, -(isBold ? 2 : 1));
			parent.append(element);
			cursor = match.index + token.length;
		}

		parent.append(document.createTextNode(text.slice(cursor)));
	};

	const renderMarkdown = (markdown) => {
		answerContent.replaceChildren();
		let paragraphLines = [];
		let listElement = null;
		let listType = '';

		const flushParagraph = () => {
			if (!paragraphLines.length) return;
			const paragraph = document.createElement('p');
			paragraph.className = 'mb-2';
			appendInlineMarkdown(paragraph, paragraphLines.join(' '));
			answerContent.append(paragraph);
			paragraphLines = [];
		};

		const flushList = () => {
			listElement = null;
			listType = '';
		};

		for (const line of markdown.split(/\r?\n/)) {
			const trimmed = line.trim();
			if (!trimmed) {
				flushParagraph();
				flushList();
				continue;
			}

			const headingMatch = trimmed.match(/^(#{1,3})\s+(.+)$/);
			if (headingMatch) {
				flushParagraph();
				flushList();
				const heading = document.createElement(headingMatch[1].length === 1 ? 'h5' : 'h6');
				heading.className = 'mt-3 mb-2';
				appendInlineMarkdown(heading, headingMatch[2]);
				answerContent.append(heading);
				continue;
			}

			const listMatch = trimmed.match(/^([-*+]\s+|\d+[.)]\s+)(.+)$/);
			if (listMatch) {
				flushParagraph();
				const nextListType = /^\d/.test(listMatch[1]) ? 'ol' : 'ul';
				if (listElement && listType !== nextListType) flushList();
				if (!listElement) {
					listType = nextListType;
					listElement = document.createElement(listType);
					listElement.className = 'mb-2 ps-4';
					answerContent.append(listElement);
				}
				const item = document.createElement('li');
				appendInlineMarkdown(item, listMatch[2]);
				listElement.append(item);
				continue;
			}

			flushList();
			paragraphLines.push(trimmed);
		}

		flushParagraph();
	};

	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		submit.disabled = true;
		const originalButtonContent = submit.innerHTML;
		submit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2" aria-hidden="true"></i>Generating';
		status.textContent = 'Generating insights…';

		try {
			const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' } });
			let result;
			try {
				result = await response.json();
			} catch {
				throw new Error('The server returned an unreadable response. Please refresh and try again.');
			}
			if (!response.ok || !result.ok) throw new Error(typeof result.error === 'string' ? result.error : 'Unable to generate insights.');
			if (typeof result.answer !== 'string' || !result.answer.trim()) throw new Error('The AI service returned an empty response. Please try again.');
			renderMarkdown(result.answer);
			answer.classList.remove('d-none');
			status.textContent = '';
			showToast('AI insights are ready.');
		} catch (error) {
			const message = error instanceof TypeError
				? 'Could not connect to the server. Check your connection and try again.'
				: (error.message || 'Unable to generate insights. Please try again.');
			status.textContent = message;
			showToast(message, 'error');
		} finally {
			submit.disabled = false;
			submit.innerHTML = originalButtonContent;
		}
	});
})();
</script>
<style>
	.report-insights { overflow:hidden; border-color:#dfe5ef; background:linear-gradient(115deg,#fff 0%,#fff 53%,#f7f8ff 100%); }
	.report-insights .card-body { padding:clamp(20px,3vw,30px); }
	.report-insights-layout { display:grid; grid-template-columns:minmax(0,.85fr) minmax(0,1.15fr); gap:clamp(24px,4vw,54px); align-items:center; }
	.report-insights-intro { position:relative; }
	.report-insights-icon { width:44px; height:44px; display:grid; place-items:center; margin-bottom:18px; border:1px solid #e0defe; border-radius:13px; background:#f1f0ff; color:#5147d9; font-size:17px; }
	.report-insights-intro .eyebrow { color:#5b55b7; }
	.report-insights-intro h2 { margin:0; color:#14233a; font-size:clamp(20px,2vw,25px); font-weight:800; letter-spacing:-.035em; }
	.report-insights-intro > p { max-width:460px; margin:10px 0 18px; color:#647188; font-size:13px; line-height:1.65; }
	.report-insights-topics { display:flex; flex-wrap:wrap; gap:8px; }
	.report-insights-topics span { display:inline-flex; align-items:center; gap:7px; padding:6px 9px; border:1px solid #e4e8f0; border-radius:7px; background:rgba(255,255,255,.8); color:#4d5b70; font-size:11px; font-weight:650; }
	.report-insights-topics i { color:#6961d8; font-size:11px; }
	.report-insights-workspace { padding:18px; border:1px solid #e4e8f1; border-radius:13px; background:rgba(255,255,255,.88); box-shadow:0 8px 24px rgba(31,45,75,.045); }
	.report-insights-workspace .form-label { color:#27364d; font-size:13px; font-weight:750; }
	.report-insights-workspace textarea { min-height:100px; resize:vertical; }
	.report-insights-form-footer { display:flex; align-items:center; justify-content:space-between; gap:14px; }
	.report-insights-privacy { display:flex; align-items:flex-start; gap:7px; color:#778398; font-size:10px; line-height:1.45; }
	.report-insights-privacy i { margin-top:2px; color:#65758c; }
	.report-insights-answer { margin-top:22px; padding:20px 22px; border:1px solid #dfe5ef; border-left:3px solid #5b52d6; border-radius:12px; background:#fff; color:#334258; font-size:13px; line-height:1.7; }
	.report-insights-answer-heading { display:flex; align-items:center; gap:11px; margin-bottom:14px; padding-bottom:13px; border-bottom:1px solid #edf0f5; }
	.report-insights-answer-heading h3 { color:#1d2d43; font-size:14px; font-weight:800; }
	.report-insights-answer-heading .eyebrow { font-size:9px; }
	.report-insights-answer-icon { width:34px; height:34px; display:grid; place-items:center; border-radius:10px; background:#f1f0ff; color:#5147d9; }
	.report-insights-answer h5,.report-insights-answer h6 { color:#1d2d43; font-weight:750; }
	.report-insights-answer code { padding:2px 5px; border-radius:4px; background:#f1f3f8; color:#4338a9; }
	.ai-toast-stack { position: fixed; right: 22px; bottom: 22px; z-index: 1080; display: grid; gap: 10px; width: min(390px, calc(100vw - 28px)); }
	.ai-toast { display: grid; grid-template-columns: 20px minmax(0, 1fr) 28px; align-items: start; gap: 10px; padding: 14px 12px; border: 1px solid #d8e7df; border-left: 4px solid #16845b; border-radius: 8px; background: #fff; color: #20352c; box-shadow: 0 10px 30px rgba(20, 35, 28, .16); font-size: 13px; animation: ai-toast-in .18s ease-out; }
	.ai-toast > i { color: #16845b; margin-top: 2px; }
	.ai-toast-error { border-color: #f0d4d4; border-left-color: #bd3f45; color: #49282a; }
	.ai-toast-error > i { color: #bd3f45; }
	.ai-toast-close { border: 0; background: transparent; color: #6c7882; font-size: 20px; line-height: 1; padding: 0; }
	.ai-toast-close:hover { color: #17202a; }
	.report-insights-answer p:last-child, .report-insights-answer ul:last-child, .report-insights-answer ol:last-child { margin-bottom: 0 !important; }
	@keyframes ai-toast-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
	@media (max-width: 767.98px) { .report-insights-layout { grid-template-columns:1fr; gap:20px; } .report-insights-icon { margin-bottom:14px; } }
	@media (max-width: 575.98px) { .report-insights-form-footer { align-items:stretch; flex-direction:column; } .report-insights-form-footer .btn { width:100%; } .report-insights-answer { padding:16px; } }
	@media (prefers-reduced-motion: reduce) { .ai-toast { animation: none; } }
</style>
