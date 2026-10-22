-- Inventory item catalog and stock-backed installation references (MySQL).
ALTER TABLE products
  ADD COLUMN item_category VARCHAR(40) NOT NULL DEFAULT 'Tracker',
  ADD COLUMN item_type VARCHAR(100) NOT NULL DEFAULT 'Teltonika FMB920';
UPDATE products SET item_type = device_type WHERE device_type IS NOT NULL AND device_type <> '';

ALTER TABLE gps_devices
  ADD COLUMN tracker_product_id INT NULL,
  ADD COLUMN fls_product_id INT NULL,
  ADD COLUMN camera_product_id INT NULL,
  ADD COLUMN sim_product_id INT NULL,
  ADD COLUMN fls_serial VARCHAR(100) NULL,
  ADD COLUMN camera_serial VARCHAR(100) NULL;

ALTER TABLE invoices
  MODIFY sale_id INT NULL,
  ADD COLUMN installation_id INT NULL,
  ADD CONSTRAINT invoices_installation_fk FOREIGN KEY (installation_id) REFERENCES gps_devices(id) ON DELETE SET NULL;
