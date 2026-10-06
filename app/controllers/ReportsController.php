<?php
require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Report.php';
require_once dirname(__DIR__) . '/services/OpenRouterInsights.php';

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
		$this->view('reports/index', ['title' => 'Reports', 'reports' => $this->model->all()]);
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
