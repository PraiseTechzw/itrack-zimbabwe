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
            $trackerImei = $this->gpsModel->trackerImeiFromInventory((int)($_POST['tracker_product_id'] ?? 0));
            if ($invalidFields || $trackerImei === null || ($needsFls && empty($_POST['fls_product_id'])) || ($needsCamera && empty($_POST['camera_product_id']))) {
                $error = 'Choose serialized equipment from inventory. Tracker IMEI / serial and required FLS / camera serials must already be recorded on the inventory items.';
                $this->view('gps/form', [
                    'title' => 'Create Installation', 'mode' => 'create', 'error' => $error,
                    'device' => $_POST, 'packageTypes' => $packageTypes, 'inventory' => $this->gpsModel->inventoryChoices(),
                ]);
                return;
            }
            if ($trackerImei !== null && $this->gpsModel->imeiExists($trackerImei)) {
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

    public function createBulk(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);
        $choices = $this->gpsModel->inventoryChoices();
        $packages = GPSDevice::packageTypes();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rows = $_POST['installations'] ?? [];
            $view = fn(string $error) => $this->view('gps/bulk', ['title' => 'Bulk installations', 'rows' => $rows, 'inventory' => $choices, 'packageTypes' => $packages, 'error' => $error]);
            if (!$this->validateCsrf()) { $view('Invalid security token.'); return; }
            if (!is_array($rows)) { $view('Submit one or more valid installation rows.'); return; }
            $rows = array_values(array_filter($rows, static fn($row) => is_array($row) && ((int)($row['tracker_product_id'] ?? 0) > 0 || (int)($row['fls_product_id'] ?? 0) > 0 || (int)($row['camera_product_id'] ?? 0) > 0 || (int)($row['sim_product_id'] ?? 0) > 0)));
            $imeis = [];
            $valid = count($rows) > 0 && count($rows) <= 50;
            foreach ($rows as $row) {
                $imei = $this->gpsModel->trackerImeiFromInventory((int)($row['tracker_product_id'] ?? 0));
                $package = (string)($row['package_type'] ?? '');
                $needsFls = in_array($package, ['Basic + FLS', 'Basic + FLS + Camera'], true);
                $needsCamera = $package === 'Basic + FLS + Camera';
                if (!in_array($package, $packages, true) || $imei === null || ($needsFls && (int)($row['fls_product_id'] ?? 0) < 1) || (!$needsFls && (int)($row['fls_product_id'] ?? 0) > 0) || ($needsCamera && (int)($row['camera_product_id'] ?? 0) < 1) || (!$needsCamera && (int)($row['camera_product_id'] ?? 0) > 0) || ($imei !== null && (isset($imeis[$imei]) || $this->gpsModel->imeiExists($imei)))) $valid = false;
                if ($imei !== null) $imeis[$imei] = true;
            }
            if (!$valid) { $view('Add 1–50 rows with serialized tracker, FLS, and camera inventory selections required by each package. Each tracker IMEI must be unique.'); return; }
            foreach ($rows as &$row) $row['installed_at'] = trim((string)($row['installed_at'] ?? '')) ?: null;
            unset($row);
            try { $this->gpsModel->createMany($rows, []); }
            catch (InvalidArgumentException $e) { $view($e->getMessage()); return; }
            catch (Throwable $e) { $view('The batch could not be saved. Check that each IMEI is unique and there is enough stock for all selected equipment.'); return; }
            $this->redirect('/index.php?controller=gps');
            return;
        }
        $this->view('gps/bulk', ['title' => 'Bulk installations', 'inventory' => $choices, 'packageTypes' => $packages]);
    }

    public function edit(): void
    {
        $this->requireModuleAccess('gps', ['Administrator','Technician','Store Officer','IT Support']);
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $device = $this->gpsModel->find($id);
        if (!$device) { $this->redirect('/index.php?controller=gps'); return; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $merged = array_merge($device, $_POST);
                $this->view('gps/form', ['title' => 'Edit Installation', 'mode' => 'edit', 'device' => $merged, 'packageTypes' => GPSDevice::packageTypes(), 'inventory' => $this->gpsModel->inventoryChoices($merged), 'error' => 'Invalid security token']);
                return;
            }
            $package = (string)($_POST['package_type'] ?? '');
            $needsFls = in_array($package, ['Basic + FLS', 'Basic + FLS + Camera'], true);
            $needsCamera = $package === 'Basic + FLS + Camera';
            $trackerImei = $this->gpsModel->trackerImeiFromInventory((int)($_POST['tracker_product_id'] ?? 0));
            $invalid = !in_array($package, GPSDevice::packageTypes(), true) || $trackerImei === null || ($needsFls && (int)($_POST['fls_product_id'] ?? 0) < 1) || (!$needsFls && (int)($_POST['fls_product_id'] ?? 0) > 0) || ($needsCamera && (int)($_POST['camera_product_id'] ?? 0) < 1) || (!$needsCamera && (int)($_POST['camera_product_id'] ?? 0) > 0);
            $merged = array_merge($device, $_POST);
            if ($invalid) {
                $this->view('gps/form', ['title' => 'Edit Installation', 'mode' => 'edit', 'device' => $merged, 'packageTypes' => GPSDevice::packageTypes(), 'inventory' => $this->gpsModel->inventoryChoices($merged), 'error' => 'Choose serialized equipment from inventory. Basic installations do not include FLS or camera; equipment serials come from inventory records.']);
                return;
            }
            if ($trackerImei !== null && $this->gpsModel->imeiExists($trackerImei, $id)) {
                $this->view('gps/form', ['title' => 'Edit Installation', 'mode' => 'edit', 'device' => $merged, 'packageTypes' => GPSDevice::packageTypes(), 'inventory' => $this->gpsModel->inventoryChoices($merged), 'error' => 'That IMEI is already registered.']);
                return;
            }
            try { $this->gpsModel->update($id, $_POST); }
            catch (InvalidArgumentException $e) {
                $this->view('gps/form', ['title' => 'Edit Installation', 'mode' => 'edit', 'device' => $merged, 'packageTypes' => GPSDevice::packageTypes(), 'inventory' => $this->gpsModel->inventoryChoices($merged), 'error' => $e->getMessage()]);
                return;
            }
            $this->redirect('/index.php?controller=gps');
            return;
        }
        $this->view('gps/form', ['title' => 'Edit Installation', 'mode' => 'edit', 'device' => $device, 'packageTypes' => GPSDevice::packageTypes(), 'inventory' => $this->gpsModel->inventoryChoices($device)]);
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
