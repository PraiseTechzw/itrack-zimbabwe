<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';

class Permission extends ModuleModel
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM permissions ORDER BY name');
    }

    public function forRole(string $role): array
    {
        return $this->fetchAll('SELECT p.* FROM permissions p JOIN role_permissions rp ON rp.permission_id = p.id WHERE rp.role = ? ORDER BY p.name', [$role]);
    }

    public function moduleDefinitions(): array
    {
        return [
            ['key'=>'inventory','label'=>'Inventory','description'=>'Products, stock levels, and inventory records.','icon'=>'fa-boxes-stacked','group'=>'Management'],
            ['key'=>'clients','label'=>'Clients','description'=>'Customer accounts and client organizations.','icon'=>'fa-handshake-angle','group'=>'Management'],
            ['key'=>'supplier','label'=>'Suppliers','description'=>'Supplier records and procurement partners.','icon'=>'fa-truck-fast','group'=>'Management'],
            ['key'=>'users','label'=>'Users','description'=>'Staff accounts, roles, and user administration.','icon'=>'fa-users','group'=>'Management'],
            ['key'=>'gps','label'=>'GPS Devices','description'=>'Field trackers and device monitoring.','icon'=>'fa-location-dot','group'=>'Management'],
            ['key'=>'accounting','label'=>'Accounting','description'=>'Cash book, invoices, expenses, and payments.','icon'=>'fa-file-invoice-dollar','group'=>'Operations'],
            ['key'=>'purchases','label'=>'Purchases','description'=>'Purchase orders and supplier workflows.','icon'=>'fa-cart-shopping','group'=>'Operations'],
            ['key'=>'sales','label'=>'Sales','description'=>'Sales records and customer transactions.','icon'=>'fa-chart-line','group'=>'Operations'],
            ['key'=>'requisition','label'=>'Requisitions','description'=>'Internal requests and approval workflows.','icon'=>'fa-clipboard-list','group'=>'Operations'],
            ['key'=>'reports','label'=>'Reports','description'=>'Operational and financial reporting.','icon'=>'fa-file-lines','group'=>'Operations'],
            ['key'=>'notification','label'=>'Notifications','description'=>'Internal alerts and team communication.','icon'=>'fa-bell','group'=>'Support'],
            ['key'=>'settings','label'=>'Settings','description'=>'Application configuration and administration.','icon'=>'fa-gear','group'=>'Support'],
        ];
    }

    public function ensureModulePermissions(): void
    {
        foreach ($this->moduleDefinitions() as $module) {
            $name = 'module.' . $module['key'];
            $existing = $this->fetchOne('SELECT id FROM permissions WHERE name = ?', [$name]);
            if (!$existing) {
                $this->execute('INSERT INTO permissions (name, description) VALUES (?, ?)', [$name, $module['description']]);
            }
        }
        $administratorModules = $this->fetchOne('SELECT COUNT(*) AS total FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role = ? AND p.name LIKE "module.%"', ['Administrator']);
        if ((int)($administratorModules['total'] ?? 0) === 0) {
            foreach ($this->moduleDefinitions() as $module) {
                $permission = $this->fetchOne('SELECT id FROM permissions WHERE name = ?', ['module.' . $module['key']]);
                if ($permission) $this->execute('INSERT INTO role_permissions (role, permission_id) VALUES (?, ?)', ['Administrator', $permission['id']]);
            }
        }
    }

    public function modulesForRole(string $role): array
    {
        $role = $role === 'admin' ? 'Administrator' : $role;
        $rows = $this->fetchAll('SELECT p.name FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role = ? AND p.name LIKE "module.%"', [$role]);
        return array_values(array_filter(array_map(fn(array $row): string => (string)preg_replace('/^module\\./', '', $row['name']), $rows)));
    }

    public function hasCustomModules(string $role): bool
    {
        return count($this->modulesForRole($role)) > 0;
    }

    public function roles(): array
    {
        $rows = $this->fetchAll('SELECT DISTINCT role FROM users WHERE role IS NOT NULL AND role <> "" ORDER BY role');
        $roles = array_map(fn(array $row): string => $row['role'] === 'admin' ? 'Administrator' : $row['role'], $rows);
        foreach (['Administrator','Director','Finance Officer','Procurement Officer','Store Officer','Sales Officer','Technician','Staff'] as $default) {
            if (!in_array($default, $roles, true)) $roles[] = $default;
        }
        return array_values(array_unique($roles));
    }

    public function matrix(): array
    {
        $this->ensureModulePermissions();
        $rows = $this->fetchAll('SELECT rp.role, p.name FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE p.name LIKE "module.%"');
        $matrix = [];
        foreach ($rows as $row) {
            $role = $row['role'] === 'admin' ? 'Administrator' : $row['role'];
            $matrix[$role][] = preg_replace('/^module\./', '', $row['name']);
        }
        return $matrix;
    }

    public function saveRoleModules(string $role, array $moduleKeys): void
    {
        $this->ensureModulePermissions();
        $role = $role === 'admin' ? 'Administrator' : trim($role);
        if ($role === '') throw new InvalidArgumentException('Role is required.');
        $allowed = array_column($this->moduleDefinitions(), 'key');
        $moduleKeys = array_values(array_intersect(array_unique($moduleKeys), $allowed));
        $this->db->beginTransaction();
        try {
            $this->execute('DELETE FROM role_permissions WHERE role = ? AND permission_id IN (SELECT id FROM permissions WHERE name LIKE "module.%")', [$role]);
            foreach ($moduleKeys as $key) {
                $permission = $this->fetchOne('SELECT id FROM permissions WHERE name = ?', ['module.' . $key]);
                if ($permission) $this->execute('INSERT INTO role_permissions (role, permission_id) VALUES (?, ?)', [$role, $permission['id']]);
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }
}
