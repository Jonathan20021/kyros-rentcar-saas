-- =====================================================================
-- KYROS RENT CAR - SaaS Multi-Tenant Database Schema
-- Engine: MySQL 5.7+ / MariaDB 10.3+
-- Charset: utf8mb4
-- =====================================================================
-- Notes:
--  * Every tenant-scoped table carries tenant_id and is indexed by it.
--  * Soft deletes use deleted_at (NULL = active).
--  * All FKs are explicit; ON DELETE behavior chosen to protect history.
--  * Slugs are unique globally for tenants; vehicle slug unique per tenant.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `kyros_rentcar`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kyros_rentcar`;

-- =====================================================================
-- PLANS (SaaS subscription tiers)
-- =====================================================================
DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(80)  NOT NULL,
  `slug`            VARCHAR(80)  NOT NULL,
  `price_monthly`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `price_yearly`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `max_vehicles`    INT NOT NULL DEFAULT 10,          -- -1 = unlimited
  `max_users`       INT NOT NULL DEFAULT 2,           -- -1 = unlimited
  `features`        JSON NULL,
  `is_public`       TINYINT(1) NOT NULL DEFAULT 1,
  `status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plans_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- TENANTS (each rent car company)
-- =====================================================================
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(150) NOT NULL,
  `slug`            VARCHAR(150) NOT NULL,
  `legal_name`      VARCHAR(180) NULL,
  `rnc`             VARCHAR(30)  NULL,
  `phone`           VARCHAR(30)  NULL,
  `whatsapp`        VARCHAR(30)  NULL,
  `email`           VARCHAR(150) NULL,
  `address`         VARCHAR(255) NULL,
  `logo`            VARCHAR(255) NULL,
  `cover_image`     VARCHAR(255) NULL,
  `description`     TEXT NULL,
  `primary_color`   VARCHAR(9)   NOT NULL DEFAULT '#4F46E5',
  `secondary_color` VARCHAR(9)   NOT NULL DEFAULT '#06B6D4',
  `currency`        VARCHAR(8)   NOT NULL DEFAULT 'DOP',
  `tax_rate`        DECIMAL(5,2) NOT NULL DEFAULT 18.00,
  `timezone`        VARCHAR(60)  NOT NULL DEFAULT 'America/Santo_Domingo',
  `plan_id`         INT UNSIGNED NULL,
  `status`          ENUM('trial','active','suspended','inactive') NOT NULL DEFAULT 'trial',
  `trial_ends_at`   DATE NULL,
  `created_at`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`      TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenants_slug` (`slug`),
  KEY `idx_tenants_plan` (`plan_id`),
  KEY `idx_tenants_status` (`status`),
  CONSTRAINT `fk_tenants_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- TENANT SUBSCRIPTIONS (billing history per tenant)
-- =====================================================================
DROP TABLE IF EXISTS `tenant_subscriptions`;
CREATE TABLE `tenant_subscriptions` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`       INT UNSIGNED NOT NULL,
  `plan_id`         INT UNSIGNED NOT NULL,
  `billing_cycle`   ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
  `amount`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `starts_at`       DATE NOT NULL,
  `ends_at`         DATE NULL,
  `status`          ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `created_at`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_subs_tenant` (`tenant_id`),
  KEY `idx_subs_plan` (`plan_id`),
  KEY `idx_subs_status` (`status`),
  CONSTRAINT `fk_subs_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_subs_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- ROLES
-- =====================================================================
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(80) NOT NULL,
  `slug`        VARCHAR(80) NOT NULL,
  `scope`       ENUM('system','tenant') NOT NULL DEFAULT 'tenant',
  `description` VARCHAR(255) NULL,
  `created_at`  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- PERMISSIONS (module + action)
-- =====================================================================
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module`    VARCHAR(60) NOT NULL,   -- vehicles, customers, reservations...
  `action`    VARCHAR(40) NOT NULL,   -- view, create, edit, delete, export...
  `slug`      VARCHAR(100) NOT NULL,  -- vehicles.create
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_perm_slug` (`slug`),
  KEY `idx_perm_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- ROLE_PERMISSIONS (pivot)
-- =====================================================================
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id`       INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `idx_rp_perm` (`permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- USERS (system + tenant staff). tenant_id NULL = Super Admin Kyros
-- =====================================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`         INT UNSIGNED NULL,
  `role_id`           INT UNSIGNED NOT NULL,
  `name`              VARCHAR(120) NOT NULL,
  `email`             VARCHAR(150) NOT NULL,
  `password`          VARCHAR(255) NOT NULL,
  `phone`             VARCHAR(30)  NULL,
  `avatar`            VARCHAR(255) NULL,
  `location_id`       INT UNSIGNED NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `remember_token`    VARCHAR(100) NULL,
  `reset_token`       VARCHAR(100) NULL,
  `reset_expires_at`  TIMESTAMP NULL DEFAULT NULL,
  `last_login_at`     TIMESTAMP NULL DEFAULT NULL,
  `status`            ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `created_at`        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_tenant_email` (`tenant_id`,`email`),
  KEY `idx_users_email` (`email`),
  KEY `idx_users_tenant` (`tenant_id`),
  KEY `idx_users_role` (`role_id`),
  KEY `idx_users_status` (`status`),
  CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- LOCATIONS / BRANCHES
-- =====================================================================
DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`    INT UNSIGNED NOT NULL,
  `name`         VARCHAR(120) NOT NULL,
  `address`      VARCHAR(255) NULL,
  `phone`        VARCHAR(30)  NULL,
  `manager_name` VARCHAR(120) NULL,
  `latitude`     DECIMAL(10,7) NULL,
  `longitude`    DECIMAL(10,7) NULL,
  `status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_locations_tenant` (`tenant_id`),
  CONSTRAINT `fk_locations_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- VEHICLE CATEGORIES
-- =====================================================================
DROP TABLE IF EXISTS `vehicle_categories`;
CREATE TABLE `vehicle_categories` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`  INT UNSIGNED NOT NULL,
  `name`       VARCHAR(80) NOT NULL,
  `slug`       VARCHAR(80) NOT NULL,
  `icon`       VARCHAR(60) NULL,
  `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_tenant_slug` (`tenant_id`,`slug`),
  KEY `idx_cat_tenant` (`tenant_id`),
  CONSTRAINT `fk_cat_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- VEHICLES
-- =====================================================================
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`          INT UNSIGNED NOT NULL,
  `brand`              VARCHAR(60)  NOT NULL,
  `model`              VARCHAR(80)  NOT NULL,
  `version`            VARCHAR(80)  NULL,
  `year`               SMALLINT UNSIGNED NULL,
  `plate_number`       VARCHAR(20)  NULL,
  `vin`                VARCHAR(40)  NULL,
  `color`              VARCHAR(40)  NULL,
  `category_id`        INT UNSIGNED NULL,
  `transmission`       ENUM('manual','automatic') NOT NULL DEFAULT 'automatic',
  `fuel_type`          ENUM('gasoline','diesel','electric','hybrid','gas') NOT NULL DEFAULT 'gasoline',
  `mileage`            INT UNSIGNED NOT NULL DEFAULT 0,
  `passengers`         TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `doors`              TINYINT UNSIGNED NOT NULL DEFAULT 4,
  `luggage_capacity`   TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `daily_price`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `weekly_price`       DECIMAL(10,2) NULL,
  `monthly_price`      DECIMAL(10,2) NULL,
  `deposit_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `insurance_price`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`             ENUM('available','reserved','rented','maintenance','out_of_service','cleaning','pending_delivery','pending_return') NOT NULL DEFAULT 'available',
  `location_id`        INT UNSIGNED NULL,
  `description`        TEXT NULL,
  `features`           JSON NULL,
  `main_image`         VARCHAR(255) NULL,
  `slug`               VARCHAR(150) NOT NULL,
  -- Document expirations
  `insurance_expires`  DATE NULL,
  `marbete_expires`    DATE NULL,   -- vehicle sticker (DR)
  `plate_expires`      DATE NULL,   -- matricula
  `inspection_expires` DATE NULL,   -- revista
  `is_featured`        TINYINT(1) NOT NULL DEFAULT 0,
  `is_public`          TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`         TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`         TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_vehicles_tenant_slug` (`tenant_id`,`slug`),
  UNIQUE KEY `uq_vehicles_tenant_plate` (`tenant_id`,`plate_number`),
  UNIQUE KEY `uq_vehicles_tenant_vin` (`tenant_id`,`vin`),
  KEY `idx_vehicles_tenant` (`tenant_id`),
  KEY `idx_vehicles_category` (`category_id`),
  KEY `idx_vehicles_status` (`status`),
  KEY `idx_vehicles_location` (`location_id`),
  KEY `idx_vehicles_public` (`is_public`),
  CONSTRAINT `fk_vehicles_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vehicles_category` FOREIGN KEY (`category_id`) REFERENCES `vehicle_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_vehicles_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- VEHICLE IMAGES (gallery)
-- =====================================================================
DROP TABLE IF EXISTS `vehicle_images`;
CREATE TABLE `vehicle_images` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`  INT UNSIGNED NOT NULL,
  `vehicle_id` INT UNSIGNED NOT NULL,
  `path`       VARCHAR(255) NOT NULL,
  `is_main`    TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vimg_tenant` (`tenant_id`),
  KEY `idx_vimg_vehicle` (`vehicle_id`),
  CONSTRAINT `fk_vimg_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vimg_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- CUSTOMERS
-- =====================================================================
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`            INT UNSIGNED NOT NULL,
  `first_name`           VARCHAR(80)  NOT NULL,
  `last_name`            VARCHAR(80)  NULL,
  `document_type`        ENUM('cedula','passport','license','rnc') NOT NULL DEFAULT 'cedula',
  `document_number`      VARCHAR(40)  NULL,
  `nationality`          VARCHAR(60)  NULL,
  `birth_date`           DATE NULL,
  `phone`                VARCHAR(30)  NULL,
  `whatsapp`             VARCHAR(30)  NULL,
  `email`                VARCHAR(150) NULL,
  `address`              VARCHAR(255) NULL,
  `license_number`       VARCHAR(40)  NULL,
  `license_expiration`   DATE NULL,
  `license_front_image`  VARCHAR(255) NULL,
  `license_back_image`   VARCHAR(255) NULL,
  `document_front_image` VARCHAR(255) NULL,
  `document_back_image`  VARCHAR(255) NULL,
  `risk_level`           ENUM('low','medium','high') NOT NULL DEFAULT 'low',
  `notes`                TEXT NULL,
  `status`               ENUM('active','blocked','blacklist','pending') NOT NULL DEFAULT 'active',
  `created_at`           TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`           TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_customers_tenant` (`tenant_id`),
  KEY `idx_customers_doc` (`tenant_id`,`document_number`),
  KEY `idx_customers_email` (`email`),
  KEY `idx_customers_status` (`status`),
  CONSTRAINT `fk_customers_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- EXTRAS (add-ons)
-- =====================================================================
DROP TABLE IF EXISTS `extras`;
CREATE TABLE `extras` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   INT UNSIGNED NOT NULL,
  `name`        VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NULL,
  `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `charge_type` ENUM('per_day','one_time','per_reservation') NOT NULL DEFAULT 'per_day',
  `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_extras_tenant` (`tenant_id`),
  CONSTRAINT `fk_extras_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- RESERVATIONS
-- =====================================================================
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`        INT UNSIGNED NOT NULL,
  `reservation_code` VARCHAR(30) NOT NULL,
  `customer_id`      INT UNSIGNED NULL,
  `vehicle_id`       INT UNSIGNED NOT NULL,
  `start_datetime`   DATETIME NOT NULL,
  `end_datetime`     DATETIME NOT NULL,
  `pickup_location`  VARCHAR(180) NULL,
  `return_location`  VARCHAR(180) NULL,
  `daily_rate`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `days_count`       INT NOT NULL DEFAULT 1,
  `subtotal`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `deposit_amount`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `extras_total`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`           ENUM('pending','confirmed','rejected','cancelled','in_progress','converted','finished') NOT NULL DEFAULT 'pending',
  `source`           ENUM('public','internal','api') NOT NULL DEFAULT 'public',
  -- denormalized public-lead contact (before a customer record exists)
  `lead_name`        VARCHAR(150) NULL,
  `lead_phone`       VARCHAR(30)  NULL,
  `lead_whatsapp`    VARCHAR(30)  NULL,
  `lead_email`       VARCHAR(150) NULL,
  `lead_document`    VARCHAR(40)  NULL,
  `lead_license`     VARCHAR(255) NULL,
  `preferred_contact` ENUM('whatsapp','phone','email') NULL,
  `notes`            TEXT NULL,
  `created_by`       INT UNSIGNED NULL,
  `created_at`       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`       TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_res_code` (`tenant_id`,`reservation_code`),
  KEY `idx_res_tenant` (`tenant_id`),
  KEY `idx_res_customer` (`customer_id`),
  KEY `idx_res_vehicle` (`vehicle_id`),
  KEY `idx_res_status` (`status`),
  KEY `idx_res_start` (`start_datetime`),
  KEY `idx_res_end` (`end_datetime`),
  CONSTRAINT `fk_res_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_res_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- RESERVATION_EXTRAS (pivot)
-- =====================================================================
DROP TABLE IF EXISTS `reservation_extras`;
CREATE TABLE `reservation_extras` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`      INT UNSIGNED NOT NULL,
  `reservation_id` INT UNSIGNED NOT NULL,
  `extra_id`       INT UNSIGNED NULL,
  `name`           VARCHAR(120) NOT NULL,
  `price`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `quantity`       INT NOT NULL DEFAULT 1,
  `charge_type`    ENUM('per_day','one_time','per_reservation') NOT NULL DEFAULT 'per_day',
  `line_total`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_rex_tenant` (`tenant_id`),
  KEY `idx_rex_res` (`reservation_id`),
  CONSTRAINT `fk_rex_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rex_res` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rex_extra` FOREIGN KEY (`extra_id`) REFERENCES `extras` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- CONTRACTS
-- =====================================================================
DROP TABLE IF EXISTS `contracts`;
CREATE TABLE `contracts` (
  `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`             INT UNSIGNED NOT NULL,
  `contract_number`       VARCHAR(30) NOT NULL,
  `reservation_id`        INT UNSIGNED NULL,
  `customer_id`           INT UNSIGNED NOT NULL,
  `vehicle_id`            INT UNSIGNED NOT NULL,
  `start_datetime`        DATETIME NOT NULL,
  `end_datetime`          DATETIME NOT NULL,
  `actual_return_datetime` DATETIME NULL,
  `start_mileage`         INT UNSIGNED NULL,
  `end_mileage`           INT UNSIGNED NULL,
  `start_fuel_level`      TINYINT UNSIGNED NULL,  -- 0..100 (%)
  `end_fuel_level`        TINYINT UNSIGNED NULL,
  `daily_rate`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `subtotal`              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `deposit_amount`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `insurance_amount`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `extras_total`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `penalties_total`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `balance_due`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`                ENUM('draft','active','finished','cancelled','overdue','claim') NOT NULL DEFAULT 'draft',
  `terms`                 TEXT NULL,
  `customer_signature`    VARCHAR(255) NULL,  -- stored signature image path
  `staff_signature`       VARCHAR(255) NULL,
  `created_by`            INT UNSIGNED NULL,
  `created_at`            TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contract_number` (`tenant_id`,`contract_number`),
  KEY `idx_contract_tenant` (`tenant_id`),
  KEY `idx_contract_reservation` (`reservation_id`),
  KEY `idx_contract_customer` (`customer_id`),
  KEY `idx_contract_vehicle` (`vehicle_id`),
  KEY `idx_contract_status` (`status`),
  CONSTRAINT `fk_contract_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_contract_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contract_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_contract_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- CONTRACT_PHOTOS (delivery/return evidence)
-- =====================================================================
DROP TABLE IF EXISTS `contract_photos`;
CREATE TABLE `contract_photos` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   INT UNSIGNED NOT NULL,
  `contract_id` INT UNSIGNED NOT NULL,
  `phase`       ENUM('delivery','return') NOT NULL DEFAULT 'delivery',
  `path`        VARCHAR(255) NOT NULL,
  `note`        VARCHAR(255) NULL,
  `created_at`  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cphoto_tenant` (`tenant_id`),
  KEY `idx_cphoto_contract` (`contract_id`),
  CONSTRAINT `fk_cphoto_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cphoto_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- PAYMENTS
-- =====================================================================
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`      INT UNSIGNED NOT NULL,
  `payment_code`   VARCHAR(30) NOT NULL,
  `customer_id`    INT UNSIGNED NULL,
  `reservation_id` INT UNSIGNED NULL,
  `contract_id`    INT UNSIGNED NULL,
  `amount`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `method`         ENUM('cash','transfer','card','paypal','stripe','azul','cardnet','other') NOT NULL DEFAULT 'cash',
  `reference`      VARCHAR(120) NULL,
  `payment_date`   DATE NOT NULL,
  `status`         ENUM('pending','paid','partial','refunded','voided') NOT NULL DEFAULT 'paid',
  `notes`          VARCHAR(255) NULL,
  `received_by`    INT UNSIGNED NULL,
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_code` (`tenant_id`,`payment_code`),
  KEY `idx_pay_tenant` (`tenant_id`),
  KEY `idx_pay_customer` (`customer_id`),
  KEY `idx_pay_reservation` (`reservation_id`),
  KEY `idx_pay_contract` (`contract_id`),
  KEY `idx_pay_status` (`status`),
  CONSTRAINT `fk_pay_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pay_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pay_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- INVOICES
-- =====================================================================
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`       INT UNSIGNED NOT NULL,
  `invoice_number`  VARCHAR(30) NOT NULL,
  `customer_id`     INT UNSIGNED NULL,
  `contract_id`     INT UNSIGNED NULL,
  `subtotal`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`          ENUM('draft','issued','paid','void') NOT NULL DEFAULT 'issued',
  `issue_date`      DATE NOT NULL,
  `due_date`        DATE NULL,
  `created_at`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_number` (`tenant_id`,`invoice_number`),
  KEY `idx_inv_tenant` (`tenant_id`),
  KEY `idx_inv_customer` (`customer_id`),
  KEY `idx_inv_contract` (`contract_id`),
  KEY `idx_inv_status` (`status`),
  CONSTRAINT `fk_inv_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inv_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- INVOICE_ITEMS
-- =====================================================================
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   INT UNSIGNED NOT NULL,
  `invoice_id`  INT UNSIGNED NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `quantity`    DECIMAL(10,2) NOT NULL DEFAULT 1,
  `unit_price`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `line_total`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_invitem_tenant` (`tenant_id`),
  KEY `idx_invitem_invoice` (`invoice_id`),
  CONSTRAINT `fk_invitem_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invitem_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- MAINTENANCE_RECORDS
-- =====================================================================
DROP TABLE IF EXISTS `maintenance_records`;
CREATE TABLE `maintenance_records` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`        INT UNSIGNED NOT NULL,
  `vehicle_id`       INT UNSIGNED NOT NULL,
  `maintenance_type` ENUM('oil','tires','brakes','battery','alignment','mechanical','deep_clean','paint','inspection','other') NOT NULL DEFAULT 'other',
  `description`      TEXT NULL,
  `provider`         VARCHAR(150) NULL,
  `cost`             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `mileage`          INT UNSIGNED NULL,
  `start_date`       DATE NULL,
  `end_date`         DATE NULL,
  `next_due_date`    DATE NULL,
  `next_due_mileage` INT UNSIGNED NULL,
  `status`           ENUM('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `invoice_file`     VARCHAR(255) NULL,
  `notes`            TEXT NULL,
  `created_at`       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_maint_tenant` (`tenant_id`),
  KEY `idx_maint_vehicle` (`vehicle_id`),
  KEY `idx_maint_status` (`status`),
  CONSTRAINT `fk_maint_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_maint_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DOCUMENTS (generic uploaded documents)
-- =====================================================================
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`      INT UNSIGNED NOT NULL,
  `documentable_type` VARCHAR(40) NULL,  -- vehicle, customer, contract
  `documentable_id`   INT UNSIGNED NULL,
  `title`          VARCHAR(150) NOT NULL,
  `path`           VARCHAR(255) NOT NULL,
  `expires_at`     DATE NULL,
  `status`         ENUM('valid','expiring','expired') NOT NULL DEFAULT 'valid',
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`     TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_doc_tenant` (`tenant_id`),
  KEY `idx_doc_owner` (`documentable_type`,`documentable_id`),
  KEY `idx_doc_expires` (`expires_at`),
  CONSTRAINT `fk_doc_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- INCIDENTS (fines, damages, claims)
-- =====================================================================
DROP TABLE IF EXISTS `incidents`;
CREATE TABLE `incidents` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`      INT UNSIGNED NOT NULL,
  `contract_id`    INT UNSIGNED NULL,
  `customer_id`    INT UNSIGNED NULL,
  `vehicle_id`     INT UNSIGNED NULL,
  `type`           ENUM('traffic_fine','exterior_damage','interior_damage','accident','theft','late','fuel','cleaning','key_loss','other') NOT NULL DEFAULT 'other',
  `description`    TEXT NULL,
  `amount`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`         ENUM('open','review','charged','cancelled','closed') NOT NULL DEFAULT 'open',
  `evidence_files` JSON NULL,
  `created_by`     INT UNSIGNED NULL,
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inc_tenant` (`tenant_id`),
  KEY `idx_inc_contract` (`contract_id`),
  KEY `idx_inc_customer` (`customer_id`),
  KEY `idx_inc_vehicle` (`vehicle_id`),
  KEY `idx_inc_status` (`status`),
  CONSTRAINT `fk_inc_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inc_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inc_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inc_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- NOTIFICATIONS (internal)
-- =====================================================================
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`  INT UNSIGNED NULL,
  `user_id`    INT UNSIGNED NULL,
  `title`      VARCHAR(150) NOT NULL,
  `message`    VARCHAR(500) NULL,
  `type`       VARCHAR(40) NOT NULL DEFAULT 'info',
  `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
  `action_url` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_tenant` (`tenant_id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_read` (`is_read`),
  CONSTRAINT `fk_notif_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- ACTIVITY_LOGS (audit trail)
-- =====================================================================
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   INT UNSIGNED NULL,
  `user_id`     INT UNSIGNED NULL,
  `action`      VARCHAR(80) NOT NULL,   -- created, updated, deleted, login...
  `module`      VARCHAR(60) NULL,
  `entity_id`   INT UNSIGNED NULL,
  `description` VARCHAR(500) NULL,
  `ip_address`  VARCHAR(45) NULL,
  `user_agent`  VARCHAR(255) NULL,
  `created_at`  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_tenant` (`tenant_id`),
  KEY `idx_log_user` (`user_id`),
  KEY `idx_log_module` (`module`),
  KEY `idx_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- LOGIN_ATTEMPTS (brute force protection)
-- =====================================================================
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(150) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `success`    TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_la_email` (`email`),
  KEY `idx_la_ip` (`ip_address`),
  KEY `idx_la_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- API_KEYS (per tenant, hashed)
-- =====================================================================
DROP TABLE IF EXISTS `api_keys`;
CREATE TABLE `api_keys` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`    INT UNSIGNED NOT NULL,
  `name`         VARCHAR(120) NOT NULL,
  `token_hash`   VARCHAR(255) NOT NULL,
  `permissions`  JSON NULL,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  `status`       ENUM('active','revoked') NOT NULL DEFAULT 'active',
  `created_at`   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_apikey_tenant` (`tenant_id`),
  KEY `idx_apikey_hash` (`token_hash`),
  CONSTRAINT `fk_apikey_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- SETTINGS (key/value per tenant; tenant_id NULL = platform-wide)
-- =====================================================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`  INT UNSIGNED NULL,
  `key_name`   VARCHAR(120) NOT NULL,
  `value`      TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_tenant_key` (`tenant_id`,`key_name`),
  KEY `idx_settings_tenant` (`tenant_id`),
  CONSTRAINT `fk_settings_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- EMAIL_TEMPLATES
-- =====================================================================
DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE `email_templates` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`  INT UNSIGNED NULL,
  `code`       VARCHAR(80) NOT NULL,   -- welcome_company, reservation_received...
  `subject`    VARCHAR(200) NOT NULL,
  `body_html`  MEDIUMTEXT NULL,
  `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_etpl_tenant` (`tenant_id`),
  KEY `idx_etpl_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- EXPENSES (operating costs: fuel, insurance, repairs, salaries...)
-- =====================================================================
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`      INT UNSIGNED NOT NULL,
  `location_id`    INT UNSIGNED NULL,
  `vehicle_id`     INT UNSIGNED NULL,
  `category`       ENUM('fuel','insurance','repairs','maintenance','salaries','rent','utilities','marketing','taxes','fees','supplies','other') NOT NULL DEFAULT 'other',
  `description`    VARCHAR(200) NOT NULL,
  `amount`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `expense_date`   DATE NOT NULL,
  `payment_method` ENUM('cash','card','transfer','check','other') NOT NULL DEFAULT 'cash',
  `vendor`         VARCHAR(150) NULL,
  `reference`      VARCHAR(80)  NULL,
  `notes`          VARCHAR(500) NULL,
  `created_by`     INT UNSIGNED NULL,
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`     TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_expenses_tenant` (`tenant_id`),
  KEY `idx_expenses_date` (`expense_date`),
  KEY `idx_expenses_category` (`category`),
  CONSTRAINT `fk_expenses_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_expenses_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_expenses_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- CASH CLOSINGS (daily cash register reconciliation)
-- =====================================================================
DROP TABLE IF EXISTS `cash_closings`;
CREATE TABLE `cash_closings` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`       INT UNSIGNED NOT NULL,
  `location_id`     INT UNSIGNED NULL,
  `closing_date`    DATE NOT NULL,
  `income_cash`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `income_card`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `income_transfer` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `income_other`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `income_total`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `expense_cash`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `expense_total`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `expected_cash`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `counted_cash`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `difference`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `notes`           VARCHAR(500) NULL,
  `closed_by`       INT UNSIGNED NULL,
  `created_at`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cash_tenant` (`tenant_id`),
  KEY `idx_cash_date` (`closing_date`),
  CONSTRAINT `fk_cash_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cash_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- PROMO CODES (coupons per tenant)
-- =====================================================================
DROP TABLE IF EXISTS `promo_codes`;
CREATE TABLE `promo_codes` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`      INT UNSIGNED NOT NULL,
  `code`           VARCHAR(40)  NOT NULL,
  `description`    VARCHAR(200) NULL,
  `discount_type`  ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `min_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `max_uses`       INT UNSIGNED NULL,
  `used_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  `valid_from`     DATE NULL,
  `valid_to`       DATE NULL,
  `is_public`      TINYINT(1) NOT NULL DEFAULT 1,
  `status`         ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`     TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_promo_tenant_code` (`tenant_id`,`code`),
  KEY `idx_promo_status` (`status`),
  CONSTRAINT `fk_promo_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DRIVERS (chauffeur staff assignable to reservations/contracts)
-- =====================================================================
DROP TABLE IF EXISTS `drivers`;
CREATE TABLE `drivers` (
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

-- Optional FK columns on reservations and contracts (driver / promo)
ALTER TABLE `reservations`
  ADD COLUMN `promo_code_id` INT UNSIGNED NULL AFTER `discount_amount`,
  ADD COLUMN `driver_id`     INT UNSIGNED NULL AFTER `vehicle_id`,
  ADD COLUMN `driver_cost`   DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `driver_id`;

ALTER TABLE `contracts`
  ADD COLUMN `promo_code_id`   INT UNSIGNED NULL AFTER `insurance_amount`,
  ADD COLUMN `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `extras_total`,
  ADD COLUMN `driver_id`       INT UNSIGNED NULL AFTER `vehicle_id`,
  ADD COLUMN `driver_cost`     DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `driver_id`;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- END OF SCHEMA
-- =====================================================================
