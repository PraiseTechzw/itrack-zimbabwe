-- Treat mobile network lines as SIM card inventory while retaining each line's provider in item_type.
UPDATE products
SET item_category = 'Sim cards'
WHERE item_category = 'Line';
