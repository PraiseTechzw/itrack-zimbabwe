<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/models/AppSetting.php';
require_once dirname(__DIR__) . '/models/Product.php';
require_once dirname(__DIR__) . '/services/LowStockAlerter.php';
require_once dirname(__DIR__) . '/helpers/settings.php';
require_once dirname(__DIR__) . '/helpers/mail.php';

class SettingsController extends Controller
{
    private function flash(string $type, string $message): void
    {
        $_SESSION['settings_flash'] = ['type' => $type, 'message' => $message];
    }

    private function takeFlash(): array
    {
        $flash = $_SESSION['settings_flash'] ?? ['type' => '', 'message' => ''];
        unset($_SESSION['settings_flash']);
        return $flash;
    }

    private function viewData(AppSetting $settingsModel): array
    {
        $settings = $settingsModel->all();
        // Show the effective sender so a domain configured via .env is editable
        // and passes the email validation when the form is saved.
        $settings['mail_from'] = mailSenderEmail();
        $config = require dirname(__DIR__) . '/config/app.php';
        try {
            $driver = Database::getInstance()->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (Throwable) {
            $driver = 'unavailable';
        }

        return [
            'title' => 'Settings',
            'settings' => $settings,
            'definitions' => AppSetting::definitions(),
            'flash' => $this->takeFlash(),
            'config' => $config,
            'dbDriver' => $driver,
            'phpVersion' => PHP_VERSION,
            'lowStockItems' => setting_bool('low_stock_alerts_enabled')
                ? (new Product())->lowStockItems((int) $settings['default_reorder_level'], 10)
                : [],
        ];
    }

    public function index(): void
    {
        $this->requireModuleAccess('settings', ['Administrator', 'Director']);
        $this->view('settings/index', $this->viewData(new AppSetting()));
    }

    public function save(): void
    {
        $this->requireModuleAccess('settings', ['Administrator', 'Director']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/index.php?controller=settings');
        }
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token. Please try again.');
            $this->redirect('/index.php?controller=settings');
        }
        try {
            (new AppSetting())->save($_POST);
            $this->flash('success', 'Settings saved.');
        } catch (InvalidArgumentException $exception) {
            $this->flash('error', $exception->getMessage());
        } catch (Throwable $exception) {
            error_log('Unable to save settings: ' . $exception->getMessage());
            $this->flash('error', 'Settings could not be saved. Check the application log.');
        }
        $this->redirect('/index.php?controller=settings');
    }

    public function sendAlert(): void
    {
        $this->requireModuleAccess('settings', ['Administrator', 'Director']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->validateCsrf()) {
            $this->redirect('/index.php?controller=settings');
        }
        $settings = (new AppSetting())->all();
        $items = (new Product())->lowStockItems((int) $settings['default_reorder_level'], 50);
        $result = LowStockAlerter::send($settings, $items);
        $this->flash($result['status'] === 'error' ? 'error' : 'success', $result['message'] ?? 'Alert check complete.');
        $this->redirect('/index.php?controller=settings');
    }
}
