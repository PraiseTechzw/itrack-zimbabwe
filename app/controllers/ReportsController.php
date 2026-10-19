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
		return in_array($period, ['all', 'today', 'last_7_days', 'last_30_days', 'this_month', 'custom'], true) ? $period : 'all';
	}

	private function effectiveReportPeriod(string $type, string $period): string
	{
		return $this->normalizeReportType($type) === 'Inventory valuation' ? 'all' : $period;
	}

	private function reportFilters(mixed $filters): array
	{
		$decoded = is_string($filters) ? json_decode($filters, true) : $filters;
		return is_array($decoded) ? $decoded : [];
	}

	private function validReportDate(string $date): bool
	{
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
			return false;
		}
		$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
		$errors = DateTimeImmutable::getLastErrors();
		return $parsed !== false
			&& ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
			&& $parsed->format('Y-m-d') === $date;
	}

	private function customPeriodError(string $type, string $startDate, string $endDate): ?string
	{
		if (!$this->validReportDate($startDate) || !$this->validReportDate($endDate)) {
			return 'Choose a valid start and end date.';
		}
		if ($startDate > $endDate) {
			return 'The start date must be on or before the end date.';
		}
		$bounds = $this->model->dateBounds($type) ?? ['min' => null, 'max' => null];
		$minimumDate = $bounds['min'] ?? date('Y-m-d');
		$maximumDate = max(date('Y-m-d'), $bounds['max'] ?? date('Y-m-d'));
		if ($startDate < $minimumDate) {
			return 'The start date cannot be earlier than the first available record (' . $minimumDate . ').';
		}
		if ($endDate > $maximumDate) {
			return 'The end date cannot be later than the latest available date (' . $maximumDate . ').';
		}
		return null;
	}

	private function requireValidCustomPeriod(string $type, string $period, string $startDate, string $endDate): void
	{
		if ($period !== 'custom' || $type === 'Inventory valuation') {
			return;
		}
		$error = $this->customPeriodError($type, $startDate, $endDate);
		if ($error !== null) {
			$_SESSION['report_error'] = $error;
			$this->redirect('/index.php?controller=reports');
		}
	}

	public function index(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		$reports = array_map(function (array $report): array {
			$type = (string) ($report['report_type'] ?? '');
			$filters = $this->reportFilters($report['filters'] ?? null);
			$report['period'] = $this->effectiveReportPeriod($type, $this->normalizeReportPeriod((string) ($filters['period'] ?? 'all')));
			$report['start_date'] = (string) ($filters['start_date'] ?? '');
			$report['end_date'] = (string) ($filters['end_date'] ?? '');
			$report['period_label'] = $this->normalizeReportType($type) === 'Inventory valuation'
				? 'Current stock snapshot'
				: ($report['period'] === 'custom' && $this->validReportDate($report['start_date']) && $this->validReportDate($report['end_date'])
					? $report['start_date'] . ' – ' . $report['end_date']
					: '');
			return $report;
		}, $this->model->all());
		$reportPeriods = [
			'all' => 'All available records',
			'today' => 'Today',
			'last_7_days' => 'Last 7 days',
			'last_30_days' => 'Last 30 days',
			'this_month' => 'This month',
			'custom' => 'Custom date range',
		];
		$reportDateBounds = [];
		foreach (['Sales summary', 'Receivables', 'GPS activity', 'Cash book'] as $type) {
			$bounds = $this->model->dateBounds($type) ?? ['min' => null, 'max' => null];
			$reportDateBounds[$type] = [
				'min' => $bounds['min'] ?? date('Y-m-d'),
				'max' => max(date('Y-m-d'), $bounds['max'] ?? date('Y-m-d')),
			];
		}
		$reportError = $_SESSION['report_error'] ?? null;
		unset($_SESSION['report_error']);
		$this->view('reports/index', [
			'title' => 'Reports',
			'reports' => $reports,
			'reportTypes' => ['Sales summary', 'Inventory valuation', 'Receivables', 'GPS activity', 'Cash book'],
			'reportPeriods' => $reportPeriods,
			'reportDateBounds' => $reportDateBounds,
			'reportToday' => date('Y-m-d'),
			'reportError' => $reportError,
		]);
	}

	public function preview(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		$type = $this->normalizeReportType((string) ($_GET['type'] ?? 'Sales summary'));
		$period = $this->effectiveReportPeriod($type, $this->normalizeReportPeriod((string) ($_GET['period'] ?? 'all')));
		$startDate = (string) ($_GET['start_date'] ?? '');
		$endDate = (string) ($_GET['end_date'] ?? '');
		$this->requireValidCustomPeriod($type, $period, $startDate, $endDate);
		$periodLabels = [
			'all' => 'All available records',
			'today' => 'Today',
			'last_7_days' => 'Last 7 days',
			'last_30_days' => 'Last 30 days',
			'this_month' => 'This month',
			'custom' => $startDate . ' – ' . $endDate,
		];
		$this->view('reports/preview', [
			'title' => $type . ' report',
			'reportType' => $type,
			'reportPeriod' => $type === 'Inventory valuation' ? 'Current stock snapshot' : $periodLabels[$period],
			'period' => $period,
			'rows' => ReportFormatter::rows($this->model->exportRows($type, $period, $startDate, $endDate)),
			'generatedAt' => date('Y-m-d H:i T'),
		]);
	}

	public function generate(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->validateCsrf()) {
			$data = $_POST;
			$type = $this->normalizeReportType((string) ($data['report_type'] ?? 'Sales summary'));
			$period = $type === 'Inventory valuation' ? 'all' : $this->normalizeReportPeriod((string) ($data['period'] ?? 'custom'));
			$startDate = (string) ($data['start_date'] ?? '');
			$endDate = (string) ($data['end_date'] ?? '');
			if ($type !== 'Inventory valuation' && $period === 'custom') {
				$error = $this->customPeriodError($type, $startDate, $endDate);
				if ($error !== null) {
					$_SESSION['report_error'] = $error;
					$this->redirect('/index.php?controller=reports');
				}
			}
			$data['filters'] = ['period' => $period];
			if ($type !== 'Inventory valuation' && $period === 'custom') {
				$data['filters']['start_date'] = $startDate;
				$data['filters']['end_date'] = $endDate;
			}
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
		$startDate = (string) ($_GET['start_date'] ?? '');
		$endDate = (string) ($_GET['end_date'] ?? '');
		$this->requireValidCustomPeriod($type, $period, $startDate, $endDate);
		$rows = ReportFormatter::rows($this->model->exportRows($type, $period, $startDate, $endDate));
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
