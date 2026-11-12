<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Requisition.php';
require_once dirname(__DIR__) . '/models/Client.php';
require_once dirname(__DIR__) . '/models/Product.php';

class RequisitionController extends Controller
{
    private Requisition $model;

    public function __construct()
    {
        $this->model = new Requisition();
    }

    private function renderIndex(?string $error = null, array $formData = []): void
    {
        $role = (string)($_SESSION['user']['role'] ?? '');
        $this->view('requisitions/index', [
            'title' => 'Requisitions',
            'requisitions' => $this->model->all(),
            'clients' => (new Client())->active(),
            'deviceTypes' => Product::deviceTypes(),
            'itemTypes' => Product::itemTypes(),
            'canApprove' => in_array($role === 'admin' ? 'Administrator' : $role, ['Administrator', 'Procurement Officer', 'Store Officer'], true),
            'error' => $error,
            'formData' => $formData,
        ]);
    }

    private function validDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) return false;
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();
        return $parsed !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $parsed->format('Y-m-d') === $date;
    }

    private function signature(array $data, string $key): ?string
    {
        $signature = trim((string)($data[$key] ?? ''));
        if (!preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/]+={0,2})$/D', $signature, $match)) return null;
        $image = base64_decode($match[1], true);
        if ($image === false || strlen($image) < 100 || strlen($image) > 200000 || substr($image, 0, 8) !== "\x89PNG\r\n\x1a\n") return null;
        return $signature;
    }

    public function index(): void
    {
        $this->requireLogin();
        $error = (string)($_SESSION['requisition_error'] ?? '');
        unset($_SESSION['requisition_error']);
        $this->renderIndex($error !== '' ? $error : null);
    }

    public function create(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/index.php?controller=requisition');
        if (!$this->validateCsrf()) {
            $this->renderIndex('Invalid security token.', $_POST);
            return;
        }

        $type = (string)($_POST['request_type'] ?? '');
        $requestedDate = trim((string)($_POST['requested_date'] ?? ''));
        $requiredDate = trim((string)($_POST['required_date'] ?? ''));
        $location = $this->sanitize((string)($_POST['location'] ?? ''));
        $reason = $this->sanitize((string)($_POST['purpose'] ?? ''));
        $amount = $_POST['total_amount'] ?? null;
        $requesterSignature = $this->signature($_POST, 'requester_signature');
        $dispatcherSignatureValue = trim((string)($_POST['dispatcher_signature'] ?? ''));
        $dispatcherSignature = $dispatcherSignatureValue === '' ? '' : $this->signature($_POST, 'dispatcher_signature');
        $receiverSignatureValue = trim((string)($_POST['receiver_signature'] ?? ''));
        $receiverSignature = $receiverSignatureValue === '' ? '' : $this->signature($_POST, 'receiver_signature');
        $dispatcherName = $this->sanitize((string)($_POST['dispatcher_name'] ?? ''));
        $receiverName = $this->sanitize((string)($_POST['receiver_name'] ?? ''));
        $receiverDate = trim((string)($_POST['receiver_date'] ?? ''));
        $requesterEmail = trim((string)($_SESSION['user']['email'] ?? ''));
        $error = null;
        if (!in_array($type, ['installation', 'other'], true)) $error = 'Choose a requisition type.';
        elseif (!$this->validDate($requestedDate)) $error = 'Enter a valid request date.';
        elseif (!$this->validDate($requiredDate)) $error = 'Enter a valid date required.';
        elseif ($location === '' || strlen($location) > 150) $error = 'Enter a location of 1 to 150 characters.';
        elseif ($reason === '') $error = 'Enter the reason for this request.';
        elseif (!is_numeric($amount) || (float)$amount < 0) $error = 'Enter a valid non-negative amount.';
        elseif ($requesterSignature === null) $error = 'Add the requestor digital signature before submitting.';
        elseif (!filter_var($requesterEmail, FILTER_VALIDATE_EMAIL)) $error = 'The logged-in account does not have a valid email address.';
        elseif ($dispatcherSignature === null || $receiverSignature === null) $error = 'Check the dispatcher and receiver signatures.';
        elseif (strlen($dispatcherName) > 150 || strlen($receiverName) > 150) $error = 'Dispatcher and receiver names must be at most 150 characters.';
        elseif ($receiverDate !== '' && !$this->validDate($receiverDate)) $error = 'Enter a valid receiver date.';

        $installationItems = null;
        $items = null;
        $clientId = 0;
        $beneficiaryName = '';
        if ($error === null && $type === 'installation') {
            $clientId = (int)($_POST['client_id'] ?? 0);
            $clients = (new Client())->active();
            $clientIds = array_map(static fn(array $client): int => (int)$client['id'], $clients);
            $installationItems = [
                'device' => ['type' => trim((string)($_POST['installation_device_type'] ?? '')), 'description' => $this->sanitize((string)($_POST['installation_device_description'] ?? '')), 'quantity' => (int)($_POST['installation_device_quantity'] ?? 0)],
                'camera' => ['type' => trim((string)($_POST['installation_camera_type'] ?? '')), 'description' => $this->sanitize((string)($_POST['installation_camera_description'] ?? '')), 'quantity' => (int)($_POST['installation_camera_quantity'] ?? 0)],
                'fls' => ['type' => trim((string)($_POST['installation_fls_type'] ?? '')), 'description' => $this->sanitize((string)($_POST['installation_fls_description'] ?? '')), 'quantity' => (int)($_POST['installation_fls_quantity'] ?? 0)],
                'sim_line' => ['type' => trim((string)($_POST['installation_sim_line_type'] ?? '')), 'description' => $this->sanitize((string)($_POST['installation_sim_line_description'] ?? '')), 'quantity' => (int)($_POST['installation_sim_line_quantity'] ?? 0)],
            ];
            if (!in_array($clientId, $clientIds, true)) $error = 'Choose an active client for the installation request.';
            elseif (!in_array($installationItems['device']['type'], Product::deviceTypes(), true) || $installationItems['device']['quantity'] < 1) $error = 'Choose a device type and enter a positive device quantity.';
            elseif (strlen($installationItems['device']['description']) > 500) $error = 'Item descriptions must be at most 500 characters.';
            else {
                foreach (['camera' => 'Cameras', 'fls' => 'FLS', 'sim_line' => 'Sim cards'] as $key => $category) {
                    $itemType = $installationItems[$key]['type'];
                    $quantity = $installationItems[$key]['quantity'];
                    if (strlen($installationItems[$key]['description']) > 500) {
                        $error = 'Item descriptions must be at most 500 characters.';
                        break;
                    }
                    if (($itemType === '' && $quantity !== 0) || ($itemType !== '' && (!in_array($itemType, Product::itemTypes()[$category], true) || $quantity < 1))) {
                        $error = 'For cameras, FLS, and SIM lines, choose an item type and enter a positive quantity, or leave both empty.';
                        break;
                    }
                }
            }
        } elseif ($error === null) {
            $beneficiaryName = $this->sanitize((string)($_POST['beneficiary_name'] ?? ''));
            $submittedItems = $_POST['items'] ?? [];
            if ($beneficiaryName === '') $error = 'Enter the name of the person the requisition is for.';
            elseif (!is_array($submittedItems)) $error = 'Add at least one requested item.';
            else {
                $items = [];
                foreach ($submittedItems as $item) {
                    if (!is_array($item)) continue;
                    $name = $this->sanitize((string)($item['name'] ?? ''));
                    $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
                    if ($name === '' && ($quantity === false || $quantity === 0)) continue;
                    if ($name === '' || $quantity === false || $quantity < 1) {
                        $error = 'Each requested item needs a name and a positive whole-number quantity.';
                        break;
                    }
                    $description = $this->sanitize((string)($item['description'] ?? ''));
                    if (strlen($description) > 500) {
                        $error = 'Item descriptions must be at most 500 characters.';
                        break;
                    }
                    $items[] = ['name' => $name, 'description' => $description, 'quantity' => $quantity];
                }
                if ($error === null && count($items) < 1) $error = 'Add at least one requested item.';
                if ($error === null && count($items) > 50) $error = 'A requisition can contain at most 50 items.';
            }
        }

        if ($error !== null) {
            $this->renderIndex($error, $_POST);
            return;
        }

        $this->model->create([
            'requested_by' => (int)($_SESSION['user']['id'] ?? 0),
            'department' => $this->sanitize((string)($_SESSION['user']['department'] ?? 'General')),
            'purpose' => $reason,
            'requested_date' => $requestedDate,
            'total_amount' => (float)$amount,
            'remarks' => $reason,
            'request_type' => $type,
            'client_id' => $clientId,
            'beneficiary_name' => $beneficiaryName,
            'requester_signature' => $requesterSignature,
            'installation_items' => $installationItems,
            'items' => $items,
            'requester_email' => $requesterEmail,
            'location' => $location,
            'required_date' => $requiredDate,
            'dispatcher_name' => $dispatcherName,
            'dispatcher_signature' => $dispatcherSignature,
            'receiver_name' => $receiverName,
            'receiver_signature' => $receiverSignature,
            'receiver_date' => $receiverDate,
        ]);
        $this->redirect('/index.php?controller=requisition');
    }

    public function decide(): void
    {
        $this->requireModuleAccess('requisition', ['Administrator', 'Procurement Officer', 'Store Officer']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/index.php?controller=requisition');
        if (!$this->validateCsrf()) {
            $_SESSION['requisition_error'] = 'Invalid security token.';
            $this->redirect('/index.php?controller=requisition');
        }
        $id = $this->sanitizeInt($_POST['id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        $signature = $this->signature($_POST, 'approver_signature');
        if ($id < 1 || !in_array($status, ['approved', 'rejected'], true) || $signature === null) {
            $_SESSION['requisition_error'] = 'Choose an approval decision and add the approver digital signature.';
            $this->redirect('/index.php?controller=requisition');
        }
        try {
            $this->model->updateStatus($id, $status, (int)($_SESSION['user']['id'] ?? 0), $this->sanitize((string)($_POST['comments'] ?? '')), $signature);
        } catch (InvalidArgumentException $exception) {
            $_SESSION['requisition_error'] = $exception->getMessage();
        }
        $this->redirect('/index.php?controller=requisition');
    }
}
