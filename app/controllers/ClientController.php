<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Client.php';

class ClientController extends Controller
{
    private Client $clientModel;

    public function __construct()
    {
        $this->clientModel = new Client();
    }

    public function index(): void
    {
        $this->requireLogin();

        $this->view('clients/index', [
            'title' => 'Clients',
            'clients' => $this->clientModel->all(),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('clients/form', [
                    'title' => 'Create Client',
                    'mode' => 'create',
                    'error' => 'Invalid security token',
                    'client' => $_POST,
                ]);
                return;
            }

            $this->clientModel->create($_POST);
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=client');
        }

        $this->view('clients/form', [
            'title' => 'Create Client',
            'mode' => 'create',
            'client' => [],
        ]);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $client = $this->clientModel->find($id);

        if (!$client) {
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=client');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('clients/form', [
                    'title' => 'Edit Client',
                    'mode' => 'edit',
                    'error' => 'Invalid security token',
                    'client' => $client,
                ]);
                return;
            }

            $this->clientModel->update($id, $_POST);
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=client');
        }

        $this->view('clients/form', [
            'title' => 'Edit Client',
            'mode' => 'edit',
            'client' => $client,
        ]);
    }

    public function delete(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->clientModel->delete($id);
        }

        $this->redirect('/itrack-zimbabwe/public/index.php?controller=client');
    }
}
