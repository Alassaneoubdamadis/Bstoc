-- Schema missing from the CampCodes pos.sql dump (tables added after Nov 2023).

CREATE TABLE IF NOT EXISTS `variations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `variations_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `variation_types` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `variation_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `variation_types_variation_id_foreign` (`variation_id`),
  CONSTRAINT `variation_types_variation_id_foreign` FOREIGN KEY (`variation_id`) REFERENCES `variations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `main_products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_type` tinyint NOT NULL COMMENT '1=Single, 2=Variable',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `variation_products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `main_product_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `variation_id` bigint UNSIGNED NOT NULL,
  `variation_type_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `variation_products_product_id_foreign` (`product_id`),
  KEY `variation_products_variation_id_foreign` (`variation_id`),
  KEY `variation_products_variation_type_id_foreign` (`variation_type_id`),
  KEY `variation_products_main_product_id_foreign` (`main_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `products` ADD COLUMN `product_code` varchar(255) COLLATE utf8mb4_unicode_ci NULL AFTER `code`;
ALTER TABLE `products` ADD COLUMN `main_product_id` bigint UNSIGNED NULL AFTER `id`;

UPDATE `products` SET `product_code` = `code` WHERE `product_code` IS NULL OR `product_code` = '';

INSERT INTO `main_products` (`name`, `code`, `product_unit`, `product_type`, `created_at`, `updated_at`)
SELECT p.`name`, p.`product_code`, p.`product_unit`, 1, NOW(), NOW()
FROM `products` p
LEFT JOIN `main_products` m ON m.`code` = p.`product_code`
WHERE p.`product_code` IS NOT NULL AND p.`product_code` <> '' AND m.`id` IS NULL;

UPDATE `products` p
INNER JOIN `main_products` m ON m.code = p.product_code
SET p.main_product_id = m.id
WHERE p.main_product_id IS NULL;

INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`)
SELECT * FROM (
  SELECT 'show_logo_in_receipt' AS `key`, '1' AS `value`, NOW() AS created_at, NOW() AS updated_at
) s WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'show_logo_in_receipt');

INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`)
SELECT * FROM (
  SELECT 'show_app_name_in_sidebar' AS `key`, '1' AS `value`, NOW() AS created_at, NOW() AS updated_at
) s WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'show_app_name_in_sidebar');

INSERT INTO `permissions` (`name`, `display_name`, `guard_name`, `created_at`, `updated_at`)
SELECT * FROM (
  SELECT 'manage_variations' AS `name`, 'Manage Variations' AS display_name, 'web' AS guard_name, NOW() AS created_at, NOW() AS updated_at
) s WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE `name` = 'manage_variations');

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, 1 FROM `permissions` p
WHERE p.name = 'manage_variations'
  AND NOT EXISTS (
    SELECT 1 FROM `role_has_permissions` r WHERE r.permission_id = p.id AND r.role_id = 1
  );

INSERT INTO `migrations` (`migration`, `batch`)
SELECT v.migration, 2 FROM (
  SELECT '2023_11_21_115157_add_manage_variations_permission' AS migration UNION ALL
  SELECT '2023_11_21_123327_create_variations_table' UNION ALL
  SELECT '2023_11_21_123338_create_variation_types_table' UNION ALL
  SELECT '2023_12_21_065548_add_product_code_field_in_products_table' UNION ALL
  SELECT '2023_12_21_090730_add_variation_products_table' UNION ALL
  SELECT '2023_12_22_064744_create_main_products_table' UNION ALL
  SELECT '2023_12_22_065109_add_main_product_id_field_in_variation_products_table' UNION ALL
  SELECT '2023_12_22_065227_fill_up_product_code' UNION ALL
  SELECT '2023_12_29_064841_add_main_product_id_field_in_products_table' UNION ALL
  SELECT '2023_12_29_065039_fill_up_main_product_table_data' UNION ALL
  SELECT '2024_01_12_093843_move_product_images_to_main_product' UNION ALL
  SELECT '2024_03_01_085230_add_new_field_in_settings_table' UNION ALL
  SELECT '2024_03_13_103510_add_new_setting_value_in_settings_table'
) v
WHERE NOT EXISTS (SELECT 1 FROM `migrations` m WHERE m.migration = v.migration);
