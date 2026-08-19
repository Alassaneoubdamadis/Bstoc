UPDATE settings SET value = 'oubdaalassane01@gmail.com' WHERE `key` = 'email';
UPDATE settings SET value = 'Alassane Oubda' WHERE `key` = 'developed';
UPDATE settings SET value = 'Conçu en Côte d''Ivoire par Alassane Oubda — B-Stock. Tous droits réservés.' WHERE `key` = 'footer';
UPDATE settings SET value = 'B-Stock' WHERE `key` = 'company_name';
UPDATE settings SET value = 'Côte d''Ivoire' WHERE `key` = 'country';
UPDATE settings SET value = 'Abidjan' WHERE `key` = 'city';
UPDATE settings SET value = 'Abidjan' WHERE `key` = 'state';
UPDATE settings SET value = 'Abidjan, Côte d''Ivoire' WHERE `key` = 'address';

UPDATE customers
SET country = 'Côte d''Ivoire', city = 'Abidjan'
WHERE LOWER(IFNULL(country, '')) IN ('india', 'inde')
   OR LOWER(IFNULL(city, '')) IN ('mumbai', 'surat');

UPDATE warehouses
SET country = 'Côte d''Ivoire', city = 'Abidjan'
WHERE LOWER(IFNULL(country, '')) IN ('india', 'inde')
   OR LOWER(IFNULL(city, '')) IN ('mumbai', 'surat');

UPDATE currencies SET name = 'Ancien (non utilisé)' WHERE name = 'India';
