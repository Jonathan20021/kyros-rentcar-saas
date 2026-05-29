-- =====================================================================
-- Migration 003: Demo licenses + temporary tenants (auto-cleanup)
-- Safe to re-run (uses IF NOT EXISTS / idempotent stored proc).
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
USE `kyros_rentcar`;

-- ---------------------------------------------------------------------
-- DEMO_LICENSES — codes that spin up a temporary tenant on the chosen plan
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `demo_licenses` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`           VARCHAR(80)  NOT NULL,
  `label`          VARCHAR(120) NULL,
  `plan_id`        INT UNSIGNED NOT NULL,
  `hours_valid`    INT UNSIGNED NOT NULL DEFAULT 5,
  `max_uses`       INT UNSIGNED NULL,           -- NULL = unlimited
  `used_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  `status`         ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `notes`          VARCHAR(255) NULL,
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_demolic_code` (`code`),
  KEY `idx_demolic_status` (`status`),
  CONSTRAINT `fk_demolic_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Add demo flags to tenants (idempotent)
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

CALL __kyros_add_col('tenants','is_demo',          '`is_demo` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`');
CALL __kyros_add_col('tenants','demo_expires_at',  '`demo_expires_at` DATETIME NULL AFTER `is_demo`');
CALL __kyros_add_col('tenants','demo_license_code','`demo_license_code` VARCHAR(80) NULL AFTER `demo_expires_at`');

DROP PROCEDURE __kyros_add_col;

-- ---------------------------------------------------------------------
-- Seed 3 demo licenses (one per plan).
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `demo_licenses` (`code`,`label`,`plan_id`,`hours_valid`,`max_uses`,`status`,`notes`) VALUES
  ('KYROS-DEMO-STARTER',  'Demo Starter',  1, 5, NULL, 'active', 'Demo público — Starter por 5 horas'),
  ('KYROS-DEMO-BUSINESS', 'Demo Business', 2, 5, NULL, 'active', 'Demo público — Business por 5 horas'),
  ('KYROS-DEMO-PREMIUM',  'Demo Premium',  3, 5, NULL, 'active', 'Demo público — Premium con todas las funciones');
