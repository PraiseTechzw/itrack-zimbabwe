<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/GPSDevice.php';

class GPSController extends Controller
{
    private GPSDevice $gpsModel;

    public function __construct()
    {
        $this->gpsModel = new GPSDevice();
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
            if (!$this->validateCsrf()) {
                $this->view('gps/form', [
                    'title' => 'Create Installation',
                    'mode' => 'create',
                    'error' => 'Invalid security token',
                    'device' => $_POST,
                    'packageTypes' => GPSDevice::packageTypes(),
                    'inventory' => $this->gpsModel->inventoryChoices(),
                ]);
                return;
            }

            $packageTypes = GPSDevice::packageTypes();
            $invalidFields = !in_array($_POST['package_type'] ?? '', $packageTypes, true);
            $needsFls = in_array($_POST['package_type'] ?? '', ['Basic + FLS', 'Basic + FLS + Camera'], true);
            $needsCamera = ($_POST['package_type'] ?? '') === 'Basic + FLS + Camera';
            if ($invalidFields || empty($_POST['tracker_product_id']) || ($needsFls && empty($_POST['fls_product_id'])) || ($needsCamera && empty($_POST['camera_product_id'])) || !preg_match('/^\\d+$/', (string) ($_POST['imei'] ?? '')) || !preg_match('/^\\d*$/', (string) ($_POST['serial'] ?? '')) || !preg_match('/^\\d*$/', (string) ($_POST['fls_serial'] ?? '')) || !preg_match('/^\\d*$/', (string) ($_POST['camera_serial'] ?? ''))) {
                $error = 'Choose the required items from inventory. Serial numbers and IMEI must contain digits only.';
                $this->view('gps/form', [
                    'title' => 'Create Installation', 'mode' => 'create', 'error' => $error,
                    'device' => $_POST, 'packageTypes' => $packageTypes, 'inventory' => $this->gpsModel->inventoryChoices(),
                ]);
                return;
            }
            if ($this->gpsModel->imeiExists((string) $_POST['imei'])) {
                $this->view('gps/form', ['title' => 'Create Installation', 'mode' => 'create', 'error' => 'That IMEI is already registered.', 'device' => $_POST, 'packageTypes' => $packageTypes, 'inventory' => $this->gpsModel->inventoryChoices()]); return;
            }
            try { $this->gpsModel->create($_POST); } catch (InvalidArgumentException $e) {
                $this->view('gps/form', ['title' => 'Create Installation', 'mode' => 'create', 'error' => $e->getMessage(), 'device' => $_POST, 'packageTypes' => $packageTypes, 'inventory' => $this->gpsModel->inventoryChoices()]); return;
            }
            $this->redirect('/index.php?controller=gps');
        }

        $this->view('gps/form', [
            'title' => 'Create Installation',
            'mode' => 'create',
            'device' => [],
            'packageTypes' => GPSDevice::packageTypes(),
            'inventory' => $this->gpsModel->inventoryChoices(),
        ]);
    }

    public function edit(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);
        $this->redirect('/index.php?controller=gps');
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
}
