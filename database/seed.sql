USE itrack_zimbabwe;

INSERT INTO users (name, email, password_hash, role, department, status) VALUES
('System Administrator', 'admin@itrack.local', '$2y$10$BbQ3g2V6BmSHzvW0I5DBL.1P9ba2XE/uH7nh0uFQ1AkgRx4vlVUfW', 'Administrator', 'IT', 'active')
ON DUPLICATE KEY UPDATE email=VALUES(email), role=VALUES(role), department=VALUES(department), status=VALUES(status);

INSERT INTO categories (name) VALUES
('General'),
('Electronics'),
('Office Supplies'),
('Hardware')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO suppliers (company_name, contact_name, email, phone, address, status) VALUES
('Global Supply Co.', 'Jane Doe', 'jane.doe@example.com', '+263 77 123 4567', '1 Supply Lane, Harare', 'active'),
('Atlas Technologies', 'David Chirwa', 'david.chirwa@example.com', '+263 77 234 5678', '12 Tech Blvd, Bulawayo', 'active')
ON DUPLICATE KEY UPDATE company_name=VALUES(company_name), email=VALUES(email);

INSERT INTO clients (company_name, contact_name, email, phone, address, status) VALUES
('Zim Retail Ltd.', 'Ava Ndlovu', 'ava.ndlovu@example.com', '+263 77 345 6789', '88 Commerce Road, Harare', 'active'),
('Metro Logistics', 'Brian Moyo', 'brian.moyo@example.com', '+263 77 456 7890', '42 Logistics Drive, Gweru', 'active')
ON DUPLICATE KEY UPDATE company_name=VALUES(company_name), email=VALUES(email);

INSERT INTO gps_devices (device_name, imei, serial, sim_number, status) VALUES
('Tracker A1', '123456789012345', 'SN-A1001', '0712345678', 'installed'),
('Tracker B2', '987654321098765', 'SN-B2002', '0712345679', 'available')
ON DUPLICATE KEY UPDATE imei=VALUES(imei), serial=VALUES(serial);

INSERT INTO products (category_id, name, sku, unit, cost_price, selling_price, opening_stock, reorder_level, status) VALUES
(1, 'Laptop', 'LT001', 'pcs', 800.00, 950.00, 5, 2, 'active'),
(2, 'GPS Tracker', 'GPS001', 'pcs', 120.00, 150.00, 12, 3, 'active'),
(3, 'Office Chair', 'OC001', 'pcs', 45.00, 60.00, 20, 5, 'active')
ON DUPLICATE KEY UPDATE sku=VALUES(sku), name=VALUES(name), cost_price=VALUES(cost_price), selling_price=VALUES(selling_price);

INSERT INTO stocks (product_id, location, quantity) VALUES
(1, 'Main Warehouse', 5),
(2, 'Main Warehouse', 12),
(3, 'Main Warehouse', 20)
ON DUPLICATE KEY UPDATE quantity=VALUES(quantity);

INSERT INTO purchase_orders (supplier_id, ordered_by, order_date, status, total_amount, remarks) VALUES
(1, 1, CURDATE(), 'pending', 2600.00, 'Initial stock replenishment'),
(2, 1, CURDATE(), 'pending', 900.00, 'GPS device restock')
ON DUPLICATE KEY UPDATE status=VALUES(status), total_amount=VALUES(total_amount);

INSERT INTO purchase_order_items (purchase_order_id, product_id, quantity, unit_cost, total_cost) VALUES
(1, 1, 2, 800.00, 1600.00),
(1, 3, 10, 45.00, 450.00),
(2, 2, 5, 120.00, 600.00)
ON DUPLICATE KEY UPDATE quantity=VALUES(quantity), unit_cost=VALUES(unit_cost), total_cost=VALUES(total_cost);

INSERT INTO sales (client_id, sales_person_id, sale_date, status, total_amount) VALUES
(1, 1, CURDATE(), 'completed', 2100.00),
(2, 1, CURDATE(), 'pending', 450.00)
ON DUPLICATE KEY UPDATE status=VALUES(status), total_amount=VALUES(total_amount);

INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES
(1, 1, 1, 950.00, 950.00),
(1, 2, 5, 150.00, 750.00),
(2, 3, 5, 60.00, 300.00)
ON DUPLICATE KEY UPDATE quantity=VALUES(quantity), unit_price=VALUES(unit_price), total_price=VALUES(total_price);

INSERT INTO invoices (sale_id, invoice_number, due_date, status, total_amount) VALUES
(1, 'INV-1001', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'unpaid', 1700.00),
(2, 'INV-1002', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'unpaid', 300.00)
ON DUPLICATE KEY UPDATE status=VALUES(status), total_amount=VALUES(total_amount);

INSERT INTO payments (invoice_id, sale_id, paid_by, payment_date, amount, method, reference) VALUES
(1, 1, 1, CURDATE(), 950.00, 'bank', 'PAY-1001'),
(2, 2, 1, CURDATE(), 300.00, 'cash', 'PAY-1002')
ON DUPLICATE KEY UPDATE amount=VALUES(amount), method=VALUES(method), reference=VALUES(reference);

INSERT INTO expenses (category, amount, paid_to, paid_by, payment_date, description) VALUES
('Office', 120.00, 'Stationery Supplier', 1, CURDATE(), 'Stationery and supplies'),
('Transport', 250.00, 'Courier Service', 1, CURDATE(), 'Delivery charges')
ON DUPLICATE KEY UPDATE amount=VALUES(amount), paid_to=VALUES(paid_to);

INSERT INTO petty_cash (user_id, amount, transaction_date, type, description, status) VALUES
(1, 100.00, CURDATE(), 'expense', 'Fuel advance', 'posted')
ON DUPLICATE KEY UPDATE amount=VALUES(amount), type=VALUES(type);

INSERT INTO cash_book (entry_date, description, debit, credit, balance, created_by) VALUES
(CURDATE(), 'Opening balance', 0.00, 0.00, 0.00, 1)
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO requisitions (requested_by, department, requested_date, status, total_amount, remarks) VALUES
(1, 'IT', CURDATE(), 'pending', 750.00, 'New GPS devices for field team')
ON DUPLICATE KEY UPDATE status=VALUES(status), total_amount=VALUES(total_amount);

INSERT INTO approvals (requisition_id, approver_id, status, comments, decision_date) VALUES
(1, 1, 'approved', 'Approved for procurement', CURDATE())
ON DUPLICATE KEY UPDATE status=VALUES(status), comments=VALUES(comments);

INSERT INTO reports (report_name, report_type, filters, generated_by) VALUES
('Monthly Inventory', 'inventory', 'month=current', 1),
('Sales Summary', 'sales', 'month=current', 1)
ON DUPLICATE KEY UPDATE report_type=VALUES(report_type);

INSERT INTO notifications (user_id, title, message, is_read) VALUES
(1, 'Welcome', 'Your ERP environment is ready.', 0)
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO permissions (name, description) VALUES
('manage_users', 'Manage application users'),
('manage_inventory', 'Manage inventory and products'),
('manage_sales', 'Manage sales and invoices')
ON DUPLICATE KEY UPDATE description=VALUES(description);

INSERT INTO role_permissions (role, permission_id) VALUES
('Administrator', 1),
('Administrator', 2),
('Administrator', 3)
ON DUPLICATE KEY UPDATE role=VALUES(role), permission_id=VALUES(permission_id);
