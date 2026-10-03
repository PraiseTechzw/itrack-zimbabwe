<?php

require_once dirname(__DIR__) . '/config/app.php';

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require dirname(__DIR__) . '/config/app.php';
            $mysqlEnabled = extension_loaded('pdo_mysql');

            if ($mysqlEnabled) {
                try {
                    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $config['db']['host'], $config['db']['port'], $config['db']['name']);
                    self::$instance = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                } catch (PDOException $exception) {
                    self::$instance = self::createSqliteInstance($config);
                }
            } else {
                self::$instance = self::createSqliteInstance($config);
            }

            self::initializeSchema(self::$instance);
        }

        return self::$instance;
    }

    private static function createSqliteInstance(array $config): PDO
    {
        $projectRoot = dirname(__DIR__, 2);
        $storageDir = $projectRoot . '/storage';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0777, true);
        }

        $dbPath = $storageDir . '/itrack.sqlite';
        return new PDO('sqlite:' . $dbPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    private static function initializeSchema(PDO $pdo): void
    {
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
            return;
        }

        $pdo->exec('PRAGMA foreign_keys = ON');

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT DEFAULT 'Staff',
            department TEXT DEFAULT 'General',
            status TEXT DEFAULT 'active',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_name TEXT NOT NULL,
            contact_name TEXT,
            email TEXT,
            phone TEXT,
            address TEXT,
            status TEXT DEFAULT 'active',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_name TEXT NOT NULL,
            contact_name TEXT,
            email TEXT,
            phone TEXT,
            address TEXT,
            status TEXT DEFAULT 'active',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS gps_devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            device_name TEXT NOT NULL,
            imei TEXT NOT NULL UNIQUE,
            serial TEXT,
            sim_number TEXT,
            status TEXT DEFAULT 'available',
            installed_at TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER,
            name TEXT NOT NULL,
            sku TEXT NOT NULL UNIQUE,
            unit TEXT DEFAULT 'pcs',
            cost_price REAL DEFAULT 0,
            selling_price REAL DEFAULT 0,
            opening_stock INTEGER DEFAULT 0,
            reorder_level INTEGER DEFAULT 0,
            status TEXT DEFAULT 'active',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS stocks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            location TEXT DEFAULT 'Main Warehouse',
            quantity INTEGER NOT NULL DEFAULT 0,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            movement_type TEXT NOT NULL,
            reference TEXT,
            created_by INTEGER,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            supplier_id INTEGER,
            ordered_by INTEGER,
            order_date TEXT DEFAULT CURRENT_DATE,
            status TEXT DEFAULT 'pending',
            total_amount REAL DEFAULT 0,
            remarks TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
            FOREIGN KEY (ordered_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS purchase_order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            purchase_order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 0,
            unit_cost REAL NOT NULL DEFAULT 0,
            total_cost REAL NOT NULL DEFAULT 0,
            FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS goods_received (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            purchase_order_id INTEGER NOT NULL,
            received_by INTEGER,
            received_date TEXT DEFAULT CURRENT_DATE,
            reference_number TEXT,
            status TEXT DEFAULT 'received',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
            FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS delivery_notes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            reference_number TEXT UNIQUE,
            related_order_id INTEGER,
            type TEXT DEFAULT 'sales',
            issued_by INTEGER,
            issued_date TEXT DEFAULT CURRENT_DATE,
            status TEXT DEFAULT 'pending',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS sales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            client_id INTEGER,
            sales_person_id INTEGER,
            sale_date TEXT DEFAULT CURRENT_DATE,
            status TEXT DEFAULT 'draft',
            total_amount REAL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
            FOREIGN KEY (sales_person_id) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS sale_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sale_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 0,
            unit_price REAL NOT NULL DEFAULT 0,
            total_price REAL NOT NULL DEFAULT 0,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sale_id INTEGER NOT NULL,
            invoice_number TEXT NOT NULL UNIQUE,
            due_date TEXT NOT NULL,
            status TEXT DEFAULT 'unpaid',
            total_amount REAL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            invoice_id INTEGER,
            sale_id INTEGER,
            paid_by INTEGER,
            payment_date TEXT DEFAULT CURRENT_DATE,
            amount REAL DEFAULT 0,
            method TEXT DEFAULT 'cash',
            reference TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
            FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
            FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category TEXT NOT NULL,
            amount REAL DEFAULT 0,
            paid_to TEXT,
            paid_by INTEGER,
            payment_date TEXT DEFAULT CURRENT_DATE,
            description TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS petty_cash (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            amount REAL DEFAULT 0,
            transaction_date TEXT DEFAULT CURRENT_DATE,
            type TEXT DEFAULT 'expense',
            description TEXT,
            status TEXT DEFAULT 'posted',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS cash_book (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            entry_date TEXT DEFAULT CURRENT_DATE,
            description TEXT,
            debit REAL DEFAULT 0,
            credit REAL DEFAULT 0,
            balance REAL DEFAULT 0,
            created_by INTEGER,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS requisitions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            requested_by INTEGER,
            department TEXT DEFAULT 'General',
            requested_date TEXT DEFAULT CURRENT_DATE,
            status TEXT DEFAULT 'pending',
            total_amount REAL DEFAULT 0,
            remarks TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS approvals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            requisition_id INTEGER NOT NULL,
            approver_id INTEGER,
            status TEXT DEFAULT 'pending',
            comments TEXT,
            decision_date TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (requisition_id) REFERENCES requisitions(id) ON DELETE CASCADE,
            FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS reports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            report_name TEXT NOT NULL,
            report_type TEXT NOT NULL,
            filters TEXT,
            generated_by INTEGER,
            generated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            title TEXT NOT NULL,
            message TEXT,
            is_read INTEGER NOT NULL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS role_permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            role TEXT NOT NULL,
            permission_id INTEGER NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (role, permission_id),
            FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
        )");

        $userCount = (int) ($pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0);
        if ($userCount === 0) {
            $hash = password_hash('password', PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (name, email, password_hash, role, department, status) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute(['System Administrator', 'admin@itrack.local', $hash, 'Administrator', 'IT', 'active']);
            $pdo->prepare('INSERT OR IGNORE INTO users (name, email, password_hash, role, department, status) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute(['Backup Administrator', 'admin@example.com', $hash, 'Administrator', 'IT', 'active']);
        }

        $pdo->exec("INSERT OR IGNORE INTO categories (name) VALUES ('General'), ('Electronics'), ('Office Supplies'), ('Hardware')");
        $pdo->exec("INSERT OR IGNORE INTO suppliers (company_name, contact_name, email, phone, address, status) VALUES
            ('Global Supply Co.', 'Jane Doe', 'jane.doe@example.com', '+263 77 123 4567', '1 Supply Lane, Harare', 'active'),
            ('Atlas Technologies', 'David Chirwa', 'david.chirwa@example.com', '+263 77 234 5678', '12 Tech Blvd, Bulawayo', 'active')");
        $pdo->exec("INSERT OR IGNORE INTO clients (company_name, contact_name, email, phone, address, status) VALUES
            ('Zim Retail Ltd.', 'Ava Ndlovu', 'ava.ndlovu@example.com', '+263 77 345 6789', '88 Commerce Road, Harare', 'active'),
            ('Metro Logistics', 'Brian Moyo', 'brian.moyo@example.com', '+263 77 456 7890', '42 Logistics Drive, Gweru', 'active')");
        $pdo->exec("INSERT OR IGNORE INTO gps_devices (device_name, imei, serial, sim_number, status) VALUES
            ('Tracker A1', '123456789012345', 'SN-A1001', '0712345678', 'installed'),
            ('Tracker B2', '987654321098765', 'SN-B2002', '0712345679', 'available')");
        $pdo->exec("INSERT OR IGNORE INTO products (category_id, name, sku, unit, cost_price, selling_price, opening_stock, reorder_level, status) VALUES
            (1, 'Laptop', 'LT001', 'pcs', 800.00, 950.00, 5, 2, 'active'),
            (2, 'GPS Tracker', 'GPS001', 'pcs', 120.00, 150.00, 12, 3, 'active'),
            (3, 'Office Chair', 'OC001', 'pcs', 45.00, 60.00, 20, 5, 'active')");
        $pdo->exec("INSERT OR IGNORE INTO stocks (product_id, location, quantity) VALUES
            (1, 'Main Warehouse', 5),
            (2, 'Main Warehouse', 12),
            (3, 'Main Warehouse', 20)");
        $pdo->exec("INSERT OR IGNORE INTO purchase_orders (supplier_id, ordered_by, order_date, status, total_amount, remarks) VALUES
            (1, 1, DATE('now'), 'pending', 2600.00, 'Initial stock replenishment'),
            (2, 1, DATE('now'), 'pending', 600.00, 'GPS device restock')");
        $pdo->exec("INSERT OR IGNORE INTO purchase_order_items (purchase_order_id, product_id, quantity, unit_cost, total_cost) VALUES
            (1, 1, 2, 800.00, 1600.00),
            (1, 3, 10, 45.00, 450.00),
            (2, 2, 5, 120.00, 600.00)");
        $pdo->exec("INSERT OR IGNORE INTO goods_received (purchase_order_id, received_by, received_date, reference_number, status) VALUES
            (1, 1, DATE('now'), 'GR-1001', 'received')");
        $pdo->exec("INSERT OR IGNORE INTO sales (client_id, sales_person_id, sale_date, status, total_amount) VALUES
            (1, 1, DATE('now'), 'completed', 1700.00),
            (2, 1, DATE('now'), 'pending', 300.00)");
        $pdo->exec("INSERT OR IGNORE INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES
            (1, 1, 1, 950.00, 950.00),
            (1, 2, 5, 150.00, 750.00),
            (2, 3, 5, 60.00, 300.00)");
        $pdo->exec("INSERT OR IGNORE INTO invoices (sale_id, invoice_number, due_date, status, total_amount) VALUES
            (1, 'INV-1001', DATE('now', '+14 days'), 'unpaid', 1700.00),
            (2, 'INV-1002', DATE('now', '+14 days'), 'unpaid', 300.00)");
        $pdo->exec("INSERT OR IGNORE INTO payments (invoice_id, sale_id, paid_by, payment_date, amount, method, reference) VALUES
            (1, 1, 1, DATE('now'), 950.00, 'bank', 'PAY-1001'),
            (2, 2, 1, DATE('now'), 300.00, 'cash', 'PAY-1002')");
        $pdo->exec("INSERT OR IGNORE INTO expenses (category, amount, paid_to, paid_by, payment_date, description) VALUES
            ('Office', 120.00, 'Stationery Supplier', 1, DATE('now'), 'Stationery and supplies'),
            ('Transport', 250.00, 'Courier Service', 1, DATE('now'), 'Delivery charges')");
        $pdo->exec("INSERT OR IGNORE INTO petty_cash (user_id, amount, transaction_date, type, description, status) VALUES
            (1, 100.00, DATE('now'), 'expense', 'Fuel advance', 'posted')");
        $pdo->exec("INSERT OR IGNORE INTO cash_book (entry_date, description, debit, credit, balance, created_by) VALUES
            (DATE('now'), 'Opening balance', 0.00, 0.00, 0.00, 1)");
        $pdo->exec("INSERT OR IGNORE INTO requisitions (requested_by, department, requested_date, status, total_amount, remarks) VALUES
            (1, 'IT', DATE('now'), 'pending', 750.00, 'New GPS devices for field team')");
        $pdo->exec("INSERT OR IGNORE INTO approvals (requisition_id, approver_id, status, comments, decision_date) VALUES
            (1, 1, 'approved', 'Approved for procurement', DATE('now'))");
        $pdo->exec("INSERT OR IGNORE INTO reports (report_name, report_type, filters, generated_by) VALUES
            ('Monthly Inventory', 'inventory', 'month=current', 1),
            ('Sales Summary', 'sales', 'month=current', 1)");
        $pdo->exec("INSERT OR IGNORE INTO notifications (user_id, title, message, is_read) VALUES
            (1, 'Welcome', 'Your ERP environment is ready.', 0)");
        $pdo->exec("INSERT OR IGNORE INTO permissions (name, description) VALUES
            ('manage_users', 'Manage application users'),
            ('manage_inventory', 'Manage inventory and products'),
            ('manage_sales', 'Manage sales and invoices')");
        $pdo->exec("INSERT OR IGNORE INTO role_permissions (role, permission_id) VALUES
            ('Administrator', 1),
            ('Administrator', 2),
            ('Administrator', 3)");
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getInstance()->rollBack();
    }
}
