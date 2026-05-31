-- ============================================================
-- 007_localization_rd_co.sql
--
-- Multi-country support: Dominican Republic (RD) + Colombia (CO).
--  - tenants.country drives label/currency/tax defaults
--  - Customer document types adapted per country
--  - Vehicle expirations adapted (SOAT + Tecnomecánica for CO,
--    marbete + inspección for RD already exist)
--  - NCF sequences for RD legal billing
--  - DIAN resolution fields on invoices for CO legal billing
-- ============================================================

-- 1. Country on tenant — drives every locale decision downstream
ALTER TABLE tenants
  ADD COLUMN country CHAR(2) NOT NULL DEFAULT 'DO' AFTER currency,
  ADD COLUMN tax_label VARCHAR(20) NOT NULL DEFAULT 'ITBIS' AFTER country,
  ADD COLUMN tax_id_label VARCHAR(20) NOT NULL DEFAULT 'RNC' AFTER tax_label;

-- 2. Customer documents — relax enum so CO types fit too
ALTER TABLE customers
  MODIFY document_type ENUM('cedula','passport','license','rnc','nit','cedula_extranjeria','ruc') NOT NULL DEFAULT 'cedula';

-- 3. Vehicle expirations — add CO-specific dates
ALTER TABLE vehicles
  ADD COLUMN soat_expires DATE NULL AFTER marbete_expires,
  ADD COLUMN tecnomecanica_expires DATE NULL AFTER soat_expires;

-- 4. NCF sequences (RD legal invoicing — DGII)
CREATE TABLE IF NOT EXISTS ncf_sequences (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id     INT UNSIGNED NOT NULL,
  ncf_type      ENUM('B01','B02','B14','B15','B16') NOT NULL,
  prefix        CHAR(3) NOT NULL,                    -- e.g. 'B01'
  current_seq   INT UNSIGNED NOT NULL DEFAULT 0,
  max_seq       INT UNSIGNED NOT NULL DEFAULT 99999999,
  valid_until   DATE NULL,
  status        ENUM('active','exhausted','expired','disabled') NOT NULL DEFAULT 'active',
  notes         VARCHAR(255) NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_tenant_type (tenant_id, ncf_type, status),
  KEY idx_ncf_tenant (tenant_id),
  CONSTRAINT fk_ncf_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Invoice legal fields
ALTER TABLE invoices
  ADD COLUMN ncf VARCHAR(13) NULL AFTER invoice_number,
  ADD COLUMN ncf_type CHAR(3) NULL AFTER ncf,
  ADD COLUMN dian_prefix VARCHAR(8) NULL AFTER ncf_type,
  ADD COLUMN dian_resolution VARCHAR(40) NULL AFTER dian_prefix,
  ADD COLUMN cufe VARCHAR(96) NULL AFTER dian_resolution,
  ADD KEY idx_invoices_ncf (ncf);

-- 6. Vehicle lifecycle log — every status transition is auditable
CREATE TABLE IF NOT EXISTS vehicle_status_log (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id     INT UNSIGNED NOT NULL,
  vehicle_id    INT UNSIGNED NOT NULL,
  from_status   VARCHAR(40) NULL,
  to_status     VARCHAR(40) NOT NULL,
  source        VARCHAR(60) NOT NULL,    -- 'contract.start' | 'contract.finish' | 'manual' | 'auto.cleaning_done'
  source_id     INT UNSIGNED NULL,
  performed_by  INT UNSIGNED NULL,
  note          VARCHAR(255) NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_vsl_vehicle (vehicle_id),
  KEY idx_vsl_tenant (tenant_id),
  CONSTRAINT fk_vsl_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
