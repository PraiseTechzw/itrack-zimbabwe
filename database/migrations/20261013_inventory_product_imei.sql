-- Apply once to MySQL databases created before product IMEI tracking.
ALTER TABLE products
  ADD COLUMN imei VARCHAR(100) NULL UNIQUE;
