<?php
require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Permission.php';

class PermissionsController extends Controller
{
    private Permission $model;

    public function __construct()
    {
        $this->model = new Permission();
    }

    public function index(): void
    {
        $this->requireRole(['Administrator']);
        $this->model->ensureModulePermissions();
        $roles = $this->model->roles();
        $selectedRole = trim((string)($_GET['role'] ?? ($roles[0] ?? 'Administrator')));
        if (!in_array($selectedRole, $roles, true)) $selectedRole = $roles[0] ?? 'Administrator';
        $this->view('permissions/index', [
            'title' => 'Permissions',
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'modules' => $this->model->moduleDefinitions(),
            'matrix' => $this->model->matrix(),
            'saved' => isset($_GET['saved']),
        ]);
    }

    public function save(): void
    {
        $this->requireRole(['Administrator']);
        if (!$this->validateCsrf()) {
            http_response_code(419);
            exit('Invalid security token');
        }
        $role = $this->sanitize((string)($_POST['role'] ?? ''));
        $modules = $_POST['modules'] ?? [];
        if (!is_array($modules)) $modules = [];
        $modules = array_map(fn($module): string => $this->sanitize((string)$module), $modules);
        $this->model->saveRoleModules($role, $modules);
        $this->redirect('/index.php?controller=permissions&role=' . urlencode($role) . '&saved=1');
    }
}
