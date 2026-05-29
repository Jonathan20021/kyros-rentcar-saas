-- =====================================================================
-- Migration 004: make business codes unique PER TENANT (not globally)
-- =====================================================================
-- Before: contract_number, reservation_code, payment_code, invoice_number
--         were globally unique → a second tenant trying to create their
--         CTR-2026-0001 would collide with tenant 1's CTR-2026-0001.
-- After:  uniqueness is scoped to (tenant_id, code), so every tenant has
--         its own counter and never collides with anyone else.
-- =====================================================================
USE `kyros_rentcar`;

ALTER TABLE `contracts`     DROP INDEX `uq_contract_number`,
                            ADD UNIQUE KEY `uq_contract_number` (`tenant_id`, `contract_number`);

ALTER TABLE `reservations`  DROP INDEX `uq_res_code`,
                            ADD UNIQUE KEY `uq_res_code` (`tenant_id`, `reservation_code`);

ALTER TABLE `payments`      DROP INDEX `uq_payment_code`,
                            ADD UNIQUE KEY `uq_payment_code` (`tenant_id`, `payment_code`);

ALTER TABLE `invoices`      DROP INDEX `uq_invoice_number`,
                            ADD UNIQUE KEY `uq_invoice_number` (`tenant_id`, `invoice_number`);
