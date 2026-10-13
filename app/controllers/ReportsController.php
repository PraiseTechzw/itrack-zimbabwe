<?php
require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Report.php';
require_once dirname(__DIR__) . '/services/OpenRouterInsights.php';
require_once dirname(__DIR__) . '/services/XlsxReport.php';

class ReportsController extends Controller
{
	private Report $model;

	public function __construct()
	{
		$this->model = new Report();
	}

	public function index(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		$this->view('reports/index', ['title' => 'Reports', 'reports' => $this->model->all(), 'reportTypes' => ['Sales summary', 'Inventory valuation', 'Receivables', 'GPS activity', 'Cash book']]);
	}

	public function generate(): void
	{
		$this->requireModuleAccess('reports', ['Administrator', 'Finance Officer', 'Director']);
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->validateCsrf()) {
			$this->model->create($_POST + ['generated_by' => $_SESSION['user']['id'] ?? null]);
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
		$type = trim((string) ($_GET['type'] ?? 'Sales summary'));
		$rows = $this->model->exportRows($type);
		$format = strtolower((string) ($_GET['format'] ?? 'csv'));
		if (in_array($format, ['xlsx', 'excel'], true)) {
			$workbook = (new XlsxReport())->build($rows);
			$safeName = preg_replace('/[^a-z0-9-]+/i', '-', strtolower($type));
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="itrack-' . $safeName . '.xlsx"');
			header('Content-Length: ' . strlen($workbook));
			echo $workbook;
			return;
		}
		if ($format === 'pdf') {
			$lines = [$type, 'Generated: ' . date('Y-m-d H:i'), ''];
			if ($rows) {
				$lines[] = implode(' | ', array_map(static fn ($key) => ucwords(str_replace('_', ' ', (string) $key)), array_keys($rows[0])));
				$lines[] = str_repeat('-', 90);
				foreach ($rows as $row) $lines[] = implode(' | ', array_map(static fn ($value) => (string) $value, $row));
			} else {
				$lines[] = 'No records found.';
			}
			$pdf = $this->makeTextPdf($lines);
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

	private function makeTextPdf(array $lines): string
	{
		$pages = array_chunk($lines, 48);
		$fontId = 3 + count($pages) * 2;
		$objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>'];
		$pageRefs = [];
		foreach ($pages as $pageIndex => $pageLines) {
			$pageId = 3 + $pageIndex * 2;
			$contentId = $pageId + 1;
			$pageRefs[] = $pageId . ' 0 R';
			$content = "BT\n/F1 9 Tf\n";
			$y = 800;
			foreach ($pageLines as $line) {
				$line = function_exists('iconv') ? (iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $line) ?: (string) $line) : (string) $line;
				$line = substr(str_replace(["\\", '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $line), 0, 130);
				$content .= "1 0 0 1 40 {$y} Tm ({$line}) Tj\n";
				$y -= 15;
			}
			$content .= 'ET';
			$objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 ' . $fontId . ' 0 R >> >> /Contents ' . $contentId . ' 0 R >>';
			$objects[$contentId] = '<< /Length ' . strlen($content) . ">>\nstream\n" . $content . "\nendstream";
		}
		$objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageRefs) . '] /Count ' . count($pages) . ' >>';
		$objects[$fontId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
		ksort($objects);
		$pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
		$offsets = [0];
		foreach ($objects as $id => $object) {
			$offsets[$id] = strlen($pdf);
			$pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
		}
		$xrefOffset = strlen($pdf);
		$maxId = max(array_keys($objects));
		$pdf .= "xref\n0 " . ($maxId + 1) . "\n0000000000 65535 f \n";
		for ($id = 1; $id <= $maxId; $id++) $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
		$pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
		return $pdf;
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
