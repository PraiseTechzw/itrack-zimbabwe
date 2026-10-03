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
			http_response_code(str_contains($exception->getMessage(), 'not configured') ? 503 : 502);
			$this->json(['ok' => false, 'error' => $exception->getMessage()]);
		} catch (Throwable $exception) {
			http_response_code(502);
			$this->json(['ok' => false, 'error' => 'Unable to generate insights right now.']);
		}
	}
}
