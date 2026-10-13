# Database migrations

Add versioned SQL migration scripts here. Prefer reversible changes and include clear descriptions for each migration.

Apply MySQL migrations once to existing production databases before deploying code that depends on the new columns. Product IMEI migration leaves existing SKU values and inventory quantities intact; existing products have no IMEI until staff enter their actual device IMEI.
