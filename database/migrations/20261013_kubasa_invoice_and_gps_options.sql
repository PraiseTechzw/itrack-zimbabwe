-- Apply once to MySQL databases created before the Kubasa invoice/GPS update.
ALTER TABLE sales
  ADD COLUMN sale_type VARCHAR(80) NOT NULL DEFAULT 'speed_certificates_tracking',
  ADD COLUMN package_type VARCHAR(80) NOT NULL DEFAULT 'basic_installation';

ALTER TABLE requisitions
  ADD COLUMN purpose VARCHAR(180) NOT NULL DEFAULT '';

ALTER TABLE gps_devices
  ADD COLUMN package_type VARCHAR(100) NOT NULL DEFAULT 'Basic installation';

ALTER TABLE invoices
  ADD COLUMN currency VARCHAR(8) NOT NULL DEFAULT 'USD',
  ADD COLUMN issue_date DATE NULL,
  ADD COLUMN reference_number VARCHAR(150) NULL,
  ADD COLUMN sales_person VARCHAR(150) NULL,
  ADD COLUMN customer_registered_name VARCHAR(200) NULL,
  ADD COLUMN customer_trade_name VARCHAR(200) NULL,
  ADD COLUMN customer_address TEXT NULL,
  ADD COLUMN customer_email VARCHAR(190) NULL,
  ADD COLUMN customer_phone VARCHAR(80) NULL,
  ADD COLUMN customer_tin VARCHAR(80) NULL,
  ADD COLUMN customer_vat VARCHAR(80) NULL;

CREATE TABLE IF NOT EXISTS invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  quantity DECIMAL(12,3) NOT NULL DEFAULT 1,
  hs_code VARCHAR(40),
  description VARCHAR(255) NOT NULL,
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  vat_rate DECIMAL(6,3) NOT NULL DEFAULT 0.000,
  vat_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  total_excl DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  total_incl DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;
