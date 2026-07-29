USE itrack_zimbabwe;

INSERT INTO users (name, email, password_hash, role, department, status) VALUES
('System Administrator', 'admin@itrack.local', '$2y$10$BbQ3g2V6BmSHzvW0I5DBL.1P9ba2XE/uH7nh0uFQ1AkgRx4vlVUfW', 'Administrator', 'IT', 'active')
ON DUPLICATE KEY UPDATE email=VALUES(email);

INSERT INTO categories (name) VALUES
('General'),
('Electronics')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO products (name, sku, category_id, unit, cost_price, selling_price, opening_stock, reorder_level, status) VALUES
('Laptop', 'LT001', 1, 'pcs', 800.00, 950.00, 5, 2, 'active'),
('GPS Tracker', 'GPS001', 2, 'pcs', 120.00, 150.00, 12, 3, 'active')
ON DUPLICATE KEY UPDATE sku=VALUES(sku);
