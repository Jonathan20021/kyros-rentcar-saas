-- =====================================================================
-- Migration 005: Shareable contract links + customer signature audit
-- =====================================================================
-- Adds:
--   share_token        random 64-hex string used as a public, unguessable URL
--   share_created_at   when the link was generated (for revocation TTL)
--   signed_at          when the customer signed via the share link
--   signed_ip          IP of the signing client (audit trail)
--
-- The token IS the auth on the public page. tenant_id is still enforced
-- on every related query so a token cannot leak data from another tenant.
-- =====================================================================
USE `kyros_rentcar`;

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

CALL __kyros_add_col('contracts','share_token',     '`share_token` VARCHAR(64) NULL AFTER `staff_signature`');
CALL __kyros_add_col('contracts','share_created_at','`share_created_at` TIMESTAMP NULL AFTER `share_token`');
CALL __kyros_add_col('contracts','signed_at',       '`signed_at` TIMESTAMP NULL AFTER `share_created_at`');
CALL __kyros_add_col('contracts','signed_ip',       '`signed_ip` VARCHAR(45) NULL AFTER `signed_at`');

DROP PROCEDURE __kyros_add_col;

-- Unique-ish index for fast token lookup. NULLs are allowed but every non-null token is unique.
CREATE UNIQUE INDEX IF NOT EXISTS `uq_contract_share_token` ON `contracts` (`share_token`);
