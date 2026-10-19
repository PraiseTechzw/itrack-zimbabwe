# Database migrations

Add versioned SQL migration scripts here. Prefer reversible changes and include clear descriptions for each migration.

Apply MySQL migrations once to existing production databases before deploying code that depends on the new columns. The product IMEI migration leaves existing SKU values and inventory quantities intact; existing products have no IMEI until staff enter their actual device IMEI. Apply the product device-details migration to add the product device type field. Legacy serial values, if present, are retained but no longer used by inventory.

Apply `20261019_inventory_product_sim_number.sql` to add the optional SIM number field to existing inventory device records.
