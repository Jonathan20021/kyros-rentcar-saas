-- =====================================================================
-- Migration 002: Promo codes, Drivers (chauffeurs), license/activity polish
-- Safe to re-run (uses IF NOT EXISTS / INSERT IGNORE).
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
USE `kyros_rentcar`;

-- ---------------------------------------------------------------------
-- PROMO CODES (coupons per tenant)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `promo_codes` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`      INT UNSIGNED NOT NULL,
  `code`           VARCHAR(40)  NOT NULL,
  `description`    VARCHAR(200) NULL,
  `discount_type`  ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `min_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `max_uses`       INT UNSIGNED NULL,                -- NULL = unlimited
  `used_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  `valid_from`     DATE NULL,
  `valid_to`       DATE NULL,
  `is_public`      TINYINT(1) NOT NULL DEFAULT 1,    -- 1 = visible in storefront banner
  `status`         ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`     TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_promo_tenant_code` (`tenant_id`,`code`),
  KEY `idx_promo_status` (`status`),
  CONSTRAINT `fk_promo_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- DRIVERS (chauffeur staff assignable to reservations / contracts)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `drivers` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`        INT UNSIGNED NOT NULL,
  `first_name`       VARCHAR(80) NOT NULL,
  `last_name`        VARCHAR(80) NULL,
  `document_number`  VARCHAR(40) NULL,
  `license_number`   VARCHAR(40) NULL,
  `license_expiration` DATE NULL,
  `phone`            VARCHAR(30) NULL,
  `email`            VARCHAR(150) NULL,
  `address`          VARCHAR(255) NULL,
  `daily_rate`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `hourly_rate`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `photo`            VARCHAR(255) NULL,
  `notes`            TEXT NULL,
  `rating`           DECIMAL(2,1) NULL,
  `status`           ENUM('active','vacation','inactive') NOT NULL DEFAULT 'active',
  `created_at`       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`       TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_drivers_tenant` (`tenant_id`),
  KEY `idx_drivers_status` (`status`),
  CONSTRAINT `fk_drivers_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Add optional driver / promo references to reservations + contracts.
-- (Adds nothing if the columns already exist.)
-- ---------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `__kyros_add_col`;
DELIMITER //
CREATE PROCEDURE `__kyros_add_col`(IN tbl VARCHAR(64), IN col VARCHAR(64), IN ddl TEXT)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col) THEN
    SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', ddl);
    PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
  END IF;
END//
DELIMITER ;

CALL __kyros_add_col('reservations','promo_code_id', '`promo_code_id` INT UNSIGNED NULL AFTER `discount_amount`');
CALL __kyros_add_col('reservations','driver_id',     '`driver_id` INT UNSIGNED NULL AFTER `vehicle_id`');
CALL __kyros_add_col('reservations','driver_cost',   '`driver_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `driver_id`');
CALL __kyros_add_col('contracts','promo_code_id',    '`promo_code_id` INT UNSIGNED NULL AFTER `insurance_amount`');
CALL __kyros_add_col('contracts','discount_amount',  '`discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `extras_total`');
CALL __kyros_add_col('contracts','driver_id',        '`driver_id` INT UNSIGNED NULL AFTER `vehicle_id`');
CALL __kyros_add_col('contracts','driver_cost',      '`driver_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `driver_id`');

DROP PROCEDURE __kyros_add_col;

-- ---------------------------------------------------------------------
-- New permissions (idempotent) + grant to owner/admin.
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `permissions` (module, action, slug) VALUES
  ('promos',     'view',   'promos.view'),
  ('promos',     'manage', 'promos.manage'),
  ('drivers',    'view',   'drivers.view'),
  ('drivers',    'create', 'drivers.create'),
  ('drivers',    'edit',   'drivers.edit'),
  ('drivers',    'delete', 'drivers.delete');

INSERT IGNORE INTO `role_permissions` (role_id, permission_id)
  SELECT r.id, p.id
  FROM roles r
  JOIN permissions p ON p.slug IN ('promos.view','promos.manage','drivers.view','drivers.create','drivers.edit','drivers.delete')
  WHERE r.slug IN ('owner','admin');
