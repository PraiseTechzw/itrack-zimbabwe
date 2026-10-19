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
            'title' => 'GPS Devices',
            'devices' => $this->gpsModel->all(),
        ]);
    }

    public function create(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('gps/form', [
                    'title' => 'Create GPS Device',
                    'mode' => 'create',
                    'error' => 'Invalid security token',
                    'device' => $_POST,
                    'deviceTypes' => GPSDevice::deviceTypes(),
                    'packageTypes' => GPSDevice::packageTypes(),
                ]);
                return;
            }

            $submittedImeis = $_POST['imeis'] ?? $_POST['imei'] ?? [];
            $tokens = is_array($submittedImeis) ? $submittedImeis : (preg_split('/[\\s,;]+/', trim((string) $submittedImeis), -1, PREG_SPLIT_NO_EMPTY) ?: []);
            $tokens = array_values(array_filter(array_map(static fn ($value): string => trim((string) $value), $tokens), static fn ($value): bool => $value !== ''));
            $imeis = array_values(array_unique($tokens));
            $duplicateImeis = [];
            foreach ($imeis as $imei) {
                if ($this->gpsModel->imeiExists($imei)) {
                    $duplicateImeis[] = $imei;
                }
            }
            $deviceTypes = GPSDevice::deviceTypes();
            $packageTypes = GPSDevice::packageTypes();
            $invalidFields = trim((string) ($_POST['device_name'] ?? '')) === ''
                || !in_array($_POST['device_type'] ?? '', $deviceTypes, true)
                || !in_array($_POST['package_type'] ?? '', $packageTypes, true)
                || !in_array($_POST['status'] ?? 'available', ['available', 'installed', 'inactive'], true);
            $hasInputDuplicates = count($imeis) !== count($tokens);
            $invalidImeis = array_filter($imeis, static fn ($imei): bool => !preg_match('/^\\d{15}$/', $imei));
            if ($invalidFields || $imeis === [] || $hasInputDuplicates || $invalidImeis !== [] || $duplicateImeis !== []) {
                if ($invalidFields) {
                    $error = 'Enter a device name and choose valid device type, package, and status.';
                } elseif ($imeis === []) {
                    $error = 'Enter at least one device IMEI.';
                } elseif ($hasInputDuplicates) {
                    $error = 'The IMEI list contains duplicates. Each device must have a unique IMEI.';
                } elseif ($invalidImeis !== []) {
                    $error = 'Each IMEI must contain exactly 15 digits.';
                } else {
                    $error = 'One or more GPS IMEIs are already registered: ' . implode(', ', $duplicateImeis);
                }
                $this->view('gps/form', [
                    'title' => 'Create GPS Device', 'mode' => 'create', 'error' => $error,
                    'device' => $_POST, 'deviceTypes' => $deviceTypes, 'packageTypes' => $packageTypes,
                ]);
                return;
            }

            $this->gpsModel->createMany($imeis, $_POST);
            $this->redirect('/index.php?controller=gps');
        }

        $this->view('gps/form', [
            'title' => 'Create GPS Device',
            'mode' => 'create',
            'device' => [],
            'deviceTypes' => GPSDevice::deviceTypes(),
            'packageTypes' => GPSDevice::packageTypes(),
        ]);
    }

    public function edit(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $device = $this->gpsModel->find($id);

        if (!$device) {
            $this->redirect('/index.php?controller=gps');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('gps/form', [
                    'title' => 'Edit GPS Device',
                    'mode' => 'edit',
                    'error' => 'Invalid security token',
                    'device' => $device,
                    'deviceTypes' => GPSDevice::deviceTypes(),
                    'packageTypes' => GPSDevice::packageTypes(),
                ]);
                return;
            }

            $imei = trim((string) ($_POST['imei'] ?? ''));
            $validFields = in_array($_POST['device_type'] ?? '', GPSDevice::deviceTypes(), true)
                && in_array($_POST['package_type'] ?? '', GPSDevice::packageTypes(), true)
                && in_array($_POST['status'] ?? 'available', ['available', 'installed', 'inactive'], true);
            if (!$validFields || $imei === '' || $this->gpsModel->imeiExists($imei, $id)) {
                $this->view('gps/form', [
                    'title' => 'Edit GPS Device', 'mode' => 'edit',
                    'error' => !$validFields ? 'Choose a valid device type, package, and status.'
                        : ($imei === '' ? 'Enter the device IMEI.' : 'That IMEI is already assigned to another GPS device.'),
                    'device' => array_merge($device, $_POST),
                    'deviceTypes' => GPSDevice::deviceTypes(), 'packageTypes' => GPSDevice::packageTypes(),
                ]);
                return;
            }
            $this->gpsModel->update($id, $_POST);
            $this->redirect('/index.php?controller=gps');
        }

        $this->view('gps/form', [
            'title' => 'Edit GPS Device',
            'mode' => 'edit',
            'device' => $device,
            'deviceTypes' => GPSDevice::deviceTypes(),
            'packageTypes' => GPSDevice::packageTypes(),
        ]);
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
