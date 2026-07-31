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

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT DEFAULT 'staff',
            department TEXT DEFAULT 'General',
            status TEXT DEFAULT 'active',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            sku TEXT,
            category_id INTEGER,
            unit TEXT DEFAULT 'pcs',
            cost_price REAL DEFAULT 0,
            selling_price REAL DEFAULT 0,
            opening_stock INTEGER DEFAULT 0,
            reorder_level INTEGER DEFAULT 0,
            status TEXT DEFAULT 'active',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )");

        $userCount = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($userCount === 0) {
            $hash = password_hash('password', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (name, email, password_hash, role, department, status) VALUES (?, ?, ?, 'admin', 'Operations', 'active')")
                ->execute(['System Admin', 'admin@example.com', $hash]);
        }

        $productCount = (int) $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($productCount === 0) {
            $pdo->prepare("INSERT INTO products (name, sku, unit, cost_price, selling_price, opening_stock, reorder_level, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')")
                ->execute(['Starter Item', 'ST-1001', 'pcs', 10.5, 15.0, 25, 5]);
        }
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
