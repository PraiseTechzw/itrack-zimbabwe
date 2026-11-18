-- Add camera hard-drive tracking, per-item VAT, deinstallation return details,
-- and make the deinstallation chassis number optional.
-- Apply to MySQL deployments that were created from an earlier schema.sql.

ALTER TABLE products
  ADD COLUMN vat_rate DECIMAL(6,3) NOT NULL DEFAULT 15.000 AFTER selling_price;

ALTER TABLE gps_devices
  ADD COLUMN hard_drive_product_id INT NULL AFTER camera_sim_number,
  ADD COLUMN hard_drive_serial VARCHAR(100) NULL AFTER hard_drive_product_id;

ALTER TABLE deinstallations
  MODIFY COLUMN chassis_number VARCHAR(100) NULL,
  ADD COLUMN hard_drive_serial VARCHAR(100) NULL AFTER camera_serial,
  ADD COLUMN removed_item VARCHAR(40) NULL AFTER hard_drive_serial,
  ADD COLUMN product_id INT NULL AFTER removed_item;
