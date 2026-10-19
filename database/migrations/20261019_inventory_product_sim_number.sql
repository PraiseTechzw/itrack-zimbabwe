-- Apply once to MySQL databases before deploying inventory SIM number support.
ALTER TABLE products
  ADD COLUMN sim_number VARCHAR(80) NULL;
