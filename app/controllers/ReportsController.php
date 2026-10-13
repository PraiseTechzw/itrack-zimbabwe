<?php
require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Report.php';
require_once dirname(__DIR__) . '/services/OpenRouterInsights.php';
require_once dirname(__DIR__) . '/services/XlsxReport.php';
require_once dirname(__DIR__) . '/services/PdfReport.php';
require_once dirname(__DIR__) . '/services/ReportFormatter.php';

class ReportsController extends Controller
{
	private Report $model;

	public function __construct()
	{
		$this->model = new Report();
	}

	private function normalizeReportType(string $type): string
	{
		$aliases = ['sales' => 'Sales summary', 'inventory' => 'Inventory valuation', 'receivables' => 'Receivables', 'gps' => 'GPS activity', 'cashbook' => 'Cash book'];
		$type = $aliases[strtolower(trim($type))] ?? trim($type);
		return in_array($type, ['Sales summary', 'Inventory valuation', 'Receivables', 'GPS activity', 'Cash book'], true) ? $type : 'Sales summary';
	}

	private function normalizeReportPeriod(string $period): string
	{
		return in_array($period, ['all', 'today', 'last_7_days', 'last_30_days', 'this_month'], true) ? $period : 'all';
	}

	private function effectiveReportPeriod(string $type, string $period): string
	{
		return $this->normalizeReportType($type) === 'Inventory valuation' ? 'all' : $period;
	}

	private function reportPeriodFromFilters(mixed $filters): string
	{
		$decoded = is_string($filters) ? json_decode($filters, true) : $filters;
		return is_array($decoded) ? $this->normalizeReportPeriod((string) ($decoded['period'] ?? 'all')) : 'all';
	}

	public function index(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		$reports = array_map(function (array $report): array {
			$type = (string) ($report['report_type'] ?? '');
			$report['period'] = $this->effectiveReportPeriod($type, $this->reportPeriodFromFilters($report['filters'] ?? null));
			$report['period_label'] = $this->normalizeReportType($type) === 'Inventory valuation'
				? 'Current stock snapshot'
				: '';
			return $report;
		}, $this->model->all());
		$reportPeriods = [
			'all' => 'All available records',
			'today' => 'Today',
			'last_7_days' => 'Last 7 days',
			'last_30_days' => 'Last 30 days',
			'this_month' => 'This month',
		];
		$this->view('reports/index', [
			'title' => 'Reports',
			'reports' => $reports,
			'reportTypes' => ['Sales summary', 'Inventory valuation', 'Receivables', 'GPS activity', 'Cash book'],
			'reportPeriods' => $reportPeriods,
		]);
	}

	public function preview(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		$type = $this->normalizeReportType((string) ($_GET['type'] ?? 'Sales summary'));
		$period = $this->effectiveReportPeriod($type, $this->normalizeReportPeriod((string) ($_GET['period'] ?? 'all')));
		$periodLabels = [
			'all' => 'All available records',
			'today' => 'Today',
			'last_7_days' => 'Last 7 days',
			'last_30_days' => 'Last 30 days',
			'this_month' => 'This month',
		];
		$this->view('reports/preview', [
			'title' => $type . ' report',
			'reportType' => $type,
			'reportPeriod' => $type === 'Inventory valuation' ? 'Current stock snapshot' : $periodLabels[$period],
			'period' => $period,
			'rows' => ReportFormatter::rows($this->model->exportRows($type, $period)),
			'generatedAt' => date('Y-m-d H:i T'),
		]);
	}

	public function generate(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->validateCsrf()) {
			$data = $_POST;
			$data['filters'] = ['period' => $this->normalizeReportPeriod((string) ($data['filters'] ?? 'all'))];
			$data['generated_by'] = $_SESSION['user']['id'] ?? null;
			$this->model->create($data);
			$userId = (int) ($_SESSION['user']['id'] ?? 0);
			if ($userId > 0) {
				createNotification($userId, 'Report generated', 'A new report was generated successfully.');
			}
		}
		$this->redirect('/index.php?controller=reports');
	}

	public function export(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		$type = $this->normalizeReportType((string) ($_GET['type'] ?? 'Sales summary'));
		$period = $this->effectiveReportPeriod($type, $this->normalizeReportPeriod((string) ($_GET['period'] ?? 'all')));
		$rows = ReportFormatter::rows($this->model->exportRows($type, $period));
		$format = strtolower((string) ($_GET['format'] ?? 'csv'));
		if (in_array($format, ['xlsx', 'excel'], true)) {
				$workbook = (new XlsxReport())->build($rows, $type);
			$safeName = preg_replace('/[^a-z0-9-]+/i', '-', strtolower($type));
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="itrack-' . $safeName . '.xlsx"');
			header('Content-Length: ' . strlen($workbook));
			echo $workbook;
			return;
		}
			if ($format === 'pdf') {
				$pdf = (new PdfReport())->build($rows, $type);
			$safeName = preg_replace('/[^a-z0-9-]+/i', '-', strtolower($type));
			header('Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename="itrack-' . $safeName . '.pdf"');
			header('Content-Length: ' . strlen($pdf));
			echo $pdf;
			return;
		}
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="itrack-report.csv"');
		$out = fopen('php://output', 'w');
		if ($rows) { fputcsv($out, array_keys($rows[0])); foreach ($rows as $row) fputcsv($out, $row); }
		fclose($out);
	}

	public function ask(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		header('Content-Type: application/json; charset=utf-8');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			http_response_code(405);
			$this->json(['ok' => false, 'error' => 'Use POST to ask a question.']);
			return;
		}

		if (!$this->validateCsrf()) {
			http_response_code(403);
			$this->json(['ok' => false, 'error' => 'Your session expired. Refresh the page and try again.']);
			return;
		}

		$question = trim((string) ($_POST['question'] ?? ''));
		if ($question === '' || strlen($question) > 1000) {
			http_response_code(422);
			$this->json(['ok' => false, 'error' => 'Enter a question of up to 1,000 characters.']);
			return;
		}

		$now = time();
		$_SESSION['ai_insights_requests'] = array_values(array_filter(
			$_SESSION['ai_insights_requests'] ?? [],
			static fn ($requestedAt): bool => is_int($requestedAt) && $requestedAt > $now - 60
		));
		if (count($_SESSION['ai_insights_requests']) >= 5) {
			http_response_code(429);
			$this->json(['ok' => false, 'error' => 'Please wait a minute before asking another question.']);
			return;
		}
		$_SESSION['ai_insights_requests'][] = $now;

		try {
			$answer = (new OpenRouterInsights())->generate($question, $this->model->aiInsightsData());
			$this->json(['ok' => true, 'answer' => $answer]);
		} catch (RuntimeException $exception) {
			$isConfigurationError = str_contains($exception->getMessage(), 'not configured');
			http_response_code($isConfigurationError ? 503 : 502);
			if (!$isConfigurationError) {
				error_log('AI insights request failed: ' . $exception->getMessage());
			}
			$this->json(['ok' => false, 'error' => $isConfigurationError
				? 'The AI assistant is not configured. Add OPENROUTER_API_KEY to the server environment.'
				: 'The AI service could not complete the request. Check the API key, model, and connection.']);
		} catch (Throwable $exception) {
			error_log('Unexpected AI insights error: ' . $exception->getMessage());
			http_response_code(502);
			$this->json(['ok' => false, 'error' => 'Unable to generate insights right now.']);
		}
	}
}
