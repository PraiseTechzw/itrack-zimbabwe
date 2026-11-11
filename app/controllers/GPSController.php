<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/GPSDevice.php';
require_once dirname(__DIR__) . '/models/Client.php';
require_once dirname(__DIR__) . '/models/User.php';

class GPSController extends Controller
{
    private GPSDevice $gpsModel;
    private Client $clientModel;
    private User $userModel;

    public function __construct()
    {
        $this->gpsModel = new GPSDevice();
        $this->clientModel = new Client();
        $this->userModel = new User();
    }

    private function installationOptions(?array $device = null): array
    {
        return [
            'inventory' => $this->gpsModel->inventoryChoices($device),
            'clients' => $this->clientModel->active(),
            'users' => $this->userModel->active(),
            'supportUsers' => $this->userModel->activeByRole('IT Support'),
        ];
    }

    private function validateInstallation(array $data, ?int $excludeId = null): ?string
    {
        $package = (string)($data['package_type'] ?? '');
        $cameraOnly = $package === 'Camera only';
        $needsFls = in_array($package, ['Basic + FLS', 'Basic + FLS + Camera'], true);
        $needsCamera = in_array($package, ['Basic + FLS + Camera', 'Camera only', 'Camera + Basic'], true);
        $trackerId = (int)($data['tracker_product_id'] ?? 0);
        $trackerImei = $cameraOnly ? null : $this->gpsModel->trackerIdentifierFromInventory(
            $trackerId,
            (string)($data['tracker_identifier'] ?? '')
        );
        if (!in_array($package, GPSDevice::packageTypes(), true) || (!$cameraOnly && $trackerImei === null)
            || ($cameraOnly && $trackerId > 0)
            || ($needsFls && (int)($data['fls_product_id'] ?? 0) < 1)
            || (!$needsFls && (int)($data['fls_product_id'] ?? 0) > 0)
            || ($needsCamera && (int)($data['camera_product_id'] ?? 0) < 1)
            || (!$needsCamera && (int)($data['camera_product_id'] ?? 0) > 0)) {
            return 'Choose a valid package and in-stock tracker. The selected package must include the required FLS and camera inventory items.';
        }
        if ($trackerImei !== null && $this->gpsModel->imeiExists($trackerImei, $excludeId)) {
            return 'That tracker IMEI / serial is already registered.';
        }
        if ((int)($data['sim_product_id'] ?? 0) > 0 && (int)$data['sim_product_id'] === (int)($data['camera_sim_product_id'] ?? 0)) {
            return 'Choose different SIM lines for the tracker and camera.';
        }
        $clientIds = array_map(static fn(array $client): int => (int)$client['id'], $this->clientModel->active());
        $userIds = array_map(static fn(array $user): int => (int)$user['id'], $this->userModel->active());
        $supportIds = array_map(static fn(array $user): int => (int)$user['id'], $this->userModel->activeByRole('IT Support'));
        if (!in_array((int)($data['client_id'] ?? 0), $clientIds, true)) return 'Choose an active client.';
        if (!in_array((int)($data['installer_id'] ?? 0), $userIds, true)) return 'Choose an active installer.';
        if (!in_array((int)($data['support_id'] ?? 0), $supportIds, true)) return 'Choose an active IT support person.';
        return null;
    }

    public function index(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);

        $this->view('gps/index', [
            'title' => 'Installations',
            'devices' => $this->gpsModel->all(),
        ]);
    }

    public function create(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = !$this->validateCsrf() ? 'Invalid security token.' : $this->validateInstallation($_POST);
            if ($error !== null) {
                $this->view('gps/form', ['title' => 'Create Installation', 'mode' => 'create', 'error' => $error, 'device' => $_POST, 'packageTypes' => GPSDevice::packageTypes()] + $this->installationOptions());
                return;
            }
            $this->gpsModel->create($_POST);
            $this->redirect('/index.php?controller=gps');
        }

        $this->view('gps/form', ['title' => 'Create Installation', 'mode' => 'create', 'device' => [], 'packageTypes' => GPSDevice::packageTypes()] + $this->installationOptions());
    }

    public function createBulk(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);
        $options = $this->installationOptions();
        $packages = GPSDevice::packageTypes();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rows = $_POST['installations'] ?? [];
            $view = fn(string $error) => $this->view('gps/bulk', ['title' => 'Bulk installations', 'rows' => $rows, 'packageTypes' => $packages, 'error' => $error] + $options);
            if (!$this->validateCsrf()) { $view('Invalid security token.'); return; }
            if (!is_array($rows)) { $view('Submit one or more valid installation rows.'); return; }
            $rows = array_values(array_filter($rows, static fn($row) => is_array($row) && ((int)($row['tracker_product_id'] ?? 0) > 0 || (int)($row['camera_product_id'] ?? 0) > 0)));
            $imeis = [];
            $valid = count($rows) > 0 && count($rows) <= 50;
            foreach ($rows as $row) {
                $imei = $this->gpsModel->trackerIdentifierFromInventory((int)($row['tracker_product_id'] ?? 0), (string)($row['tracker_identifier'] ?? ''));
                $cameraOnly = (string)($row['package_type'] ?? '') === 'Camera only';
                if ($this->validateInstallation($row) !== null || (!$cameraOnly && $imei === null) || ($imei !== null && isset($imeis[$imei]))) $valid = false;
                if ($imei !== null) $imeis[$imei] = true;
            }
            if (!$valid) { $view('Add 1–50 valid rows with a unique tracker IMEI / serial, client, installer, IT support person, and all package-required inventory items.'); return; }
            foreach ($rows as &$row) $row['installed_at'] = trim((string)($row['installed_at'] ?? '')) ?: null;
            unset($row);
            $this->gpsModel->createMany($rows, []);
            $this->redirect('/index.php?controller=gps');
            return;
        }
        $this->view('gps/bulk', ['title' => 'Bulk installations', 'packageTypes' => $packages] + $options);
    }

    public function edit(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $device = $this->gpsModel->find($id);
        if (!$device) { $this->redirect('/index.php?controller=gps'); return; }
        if (($device['status'] ?? '') === 'deinstalled') { $this->redirect('/index.php?controller=gps'); return; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $merged = array_merge($device, $_POST);
            $error = !$this->validateCsrf() ? 'Invalid security token.' : $this->validateInstallation($_POST, $id);
            if ($error !== null) {
                $this->view('gps/form', ['title' => 'Edit Installation', 'mode' => 'edit', 'device' => $merged, 'packageTypes' => GPSDevice::packageTypes(), 'error' => $error] + $this->installationOptions($merged));
                return;
            }
            $this->gpsModel->update($id, $_POST);
            $this->redirect('/index.php?controller=gps');
            return;
        }
        $this->view('gps/form', ['title' => 'Edit Installation', 'mode' => 'edit', 'device' => $device, 'packageTypes' => GPSDevice::packageTypes()] + $this->installationOptions($device));
    }

    public function delete(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);
        $id = $this->sanitizeInt($_POST['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->validateCsrf() && $id > 0) {
            $this->gpsModel->delete($id);
        }

        $this->redirect('/index.php?controller=gps');
    }

    public function deinstallations(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);
        $this->view('gps/deinstallations', [
            'title' => 'Deinstallations',
            'activeInstallations' => $this->gpsModel->activeForDeinstallation(),
            'deinstallations' => $this->gpsModel->deinstallations(),
        ]);
    }

    public function deinstall(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/index.php?controller=gps&action=deinstallations');
        }
        $installationId = $this->sanitizeInt($_POST['gps_device_id'] ?? 0);
        $error = !$this->validateCsrf() ? 'Invalid security token.' : null;
        if ($error === null) {
            try {
                $this->gpsModel->deinstall($installationId, $_POST, (int)($_SESSION['user']['id'] ?? 0));
                $this->redirect('/index.php?controller=gps&action=deinstallations');
            } catch (InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            }
        }
        $this->view('gps/deinstallations', [
            'title' => 'Deinstallations',
            'activeInstallations' => $this->gpsModel->activeForDeinstallation(),
            'deinstallations' => $this->gpsModel->deinstallations(),
            'error' => $error,
            'formData' => $_POST,
        ]);
    }
}
