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
        $this->requireLogin();

        $this->view('gps/index', [
            'title' => 'GPS Devices',
            'devices' => $this->gpsModel->all(),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('gps/form', [
                    'title' => 'Create GPS Device',
                    'mode' => 'create',
                    'error' => 'Invalid security token',
                    'device' => $_POST,
                ]);
                return;
            }

            $this->gpsModel->create($_POST);
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=gps');
        }

        $this->view('gps/form', [
            'title' => 'Create GPS Device',
            'mode' => 'create',
            'device' => [],
        ]);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $device = $this->gpsModel->find($id);

        if (!$device) {
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=gps');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('gps/form', [
                    'title' => 'Edit GPS Device',
                    'mode' => 'edit',
                    'error' => 'Invalid security token',
                    'device' => $device,
                ]);
                return;
            }

            $this->gpsModel->update($id, $_POST);
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=gps');
        }

        $this->view('gps/form', [
            'title' => 'Edit GPS Device',
            'mode' => 'edit',
            'device' => $device,
        ]);
    }

    public function delete(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->gpsModel->delete($id);
        }

        $this->redirect('/itrack-zimbabwe/public/index.php?controller=gps');
    }
}
