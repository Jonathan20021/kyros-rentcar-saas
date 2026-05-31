-- ============================================================
-- 006_storage_and_approvals.sql
--
-- 1. Storage quotas per plan (in MB)
-- 2. Per-tenant usage cache + approved extra storage
-- 3. storage_requests table for tenants asking for more storage
-- 4. Tenant approval gate: new status `pending_approval` so super
--    admin must activate fresh registrations before they can log in
-- ============================================================

-- 1. Plans get a base storage quota in megabytes
ALTER TABLE plans
  ADD COLUMN storage_mb INT UNSIGNED NOT NULL DEFAULT 500 AFTER max_users;

-- Seed sensible defaults
UPDATE plans SET storage_mb = 500   WHERE slug = 'starter';
UPDATE plans SET storage_mb = 5000  WHERE slug = 'business';
UPDATE plans SET storage_mb = 25000 WHERE slug = 'premium';

-- 2. Tenants track current usage + approved extras
ALTER TABLE tenants
  ADD COLUMN storage_used_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER plan_id,
  ADD COLUMN storage_extra_mb   INT UNSIGNED NOT NULL DEFAULT 0   AFTER storage_used_bytes,
  ADD COLUMN storage_usage_at   TIMESTAMP NULL DEFAULT NULL       AFTER storage_extra_mb;

-- 3. Extend tenant status enum so fresh registrations can sit in approval queue.
--    We keep existing values intact and add `pending_approval`.
ALTER TABLE tenants
  MODIFY status ENUM('pending_approval','trial','active','suspended','inactive') NOT NULL DEFAULT 'trial';

-- 4. storage_requests — tenant asks for an extra quota bump
CREATE TABLE IF NOT EXISTS storage_requests (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id       INT UNSIGNED NOT NULL,
  requested_mb    INT UNSIGNED NOT NULL,
  reason          VARCHAR(500) NULL,
  status          ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  requested_by    INT UNSIGNED NULL,
  reviewed_by     INT UNSIGNED NULL,
  reviewed_at     TIMESTAMP NULL DEFAULT NULL,
  review_note     VARCHAR(500) NULL,
  granted_mb      INT UNSIGNED NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_storage_req_tenant (tenant_id),
  KEY idx_storage_req_status (status),
  CONSTRAINT fk_storage_req_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. tenant_approvals — log of admin approvals/suspensions for audit
CREATE TABLE IF NOT EXISTS tenant_approvals (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id       INT UNSIGNED NOT NULL,
  action          ENUM('approved','rejected','suspended','reactivated') NOT NULL,
  performed_by    INT UNSIGNED NULL,
  note            VARCHAR(500) NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tenant_approvals_tenant (tenant_id),
  CONSTRAINT fk_tenant_approvals_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
