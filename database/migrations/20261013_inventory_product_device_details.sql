-- Apply once to MySQL databases that already have product IMEI tracking.
ALTER TABLE products
  ADD COLUMN device_type VARCHAR(80) NOT NULL DEFAULT 'Galileosky 7x',
  ADD COLUMN serial VARCHAR(100) NULL;
