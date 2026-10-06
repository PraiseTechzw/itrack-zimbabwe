<?php
return [
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    'auth' => ['controller' => 'AuthController', 'actions' => ['login', 'logout', 'forgotPassword', 'changePassword']],
    'inventory' => ['controller' => 'InventoryController', 'actions' => ['index', 'create', 'edit', 'delete', 'apiList']],
    'clients' => ['controller' => 'ClientController', 'actions' => ['index', 'create', 'edit', 'delete']],
    'supplier' => ['controller' => 'SupplierController', 'actions' => ['index', 'create', 'edit', 'delete']],
    'users' => ['controller' => 'UsersController', 'actions' => ['index', 'create', 'edit', 'delete']],
    'gps' => ['controller' => 'GPSController', 'actions' => ['index', 'create', 'edit', 'delete']],
    'accounting' => ['controller' => 'AccountingController', 'actions' => ['index', 'invoices', 'invoiceCreate', 'invoiceEdit', 'invoiceDelete', 'payments', 'paymentCreate', 'paymentEdit', 'paymentDelete', 'expenses', 'expenseCreate', 'expenseEdit', 'expenseDelete', 'pettyCash', 'pettyCashCreate', 'pettyCashEdit', 'pettyCashDelete', 'cashBook', 'cashBookCreate', 'cashBookEdit', 'cashBookDelete']],
    'purchases' => ['controller' => 'PurchasesController', 'actions' => ['index', 'create', 'status']],
    'sales' => ['controller' => 'SalesController', 'actions' => ['index', 'create', 'status']],
    'requisition' => ['controller' => 'RequisitionController', 'actions' => ['index', 'create', 'decide']],
    'reports' => ['controller' => 'ReportsController', 'actions' => ['index', 'generate', 'ask']],
    'notification' => ['controller' => 'NotificationController', 'actions' => ['index', 'create', 'edit', 'delete']],
    'settings' => ['controller' => 'SettingsController', 'actions' => ['index']],
    'permissions' => ['controller' => 'PermissionsController', 'actions' => ['index', 'save']],
];
