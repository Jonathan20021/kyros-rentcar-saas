<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Contract extends Model
{
    protected static string $table = 'contracts';

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT ct.*, v.brand, v.model, v.plate_number,
                       CONCAT(c.first_name,' ',c.last_name) AS customer_name
                  FROM contracts ct
                  JOIN vehicles v ON v.id = ct.vehicle_id
                  JOIN customers c ON c.id = ct.customer_id
                 WHERE ct.tenant_id = :t AND ct.deleted_at IS NULL";
        $params = ['t' => $tenantId];
        if (!empty($filters['status'])) {
            $sql .= " AND ct.status = :st"; $params['st'] = $filters['status'];
        }
        $sql .= " ORDER BY ct.created_at DESC";
        return Database::select($sql, $params);
    }

    public static function activeCount(int $tenantId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM contracts WHERE tenant_id = :t AND status = 'active' AND deleted_at IS NULL",
            ['t' => $tenantId]
        );
    }

    public static function pendingBalance(int $tenantId): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(balance_due),0) FROM contracts
              WHERE tenant_id = :t AND deleted_at IS NULL AND status IN ('active','overdue')",
            ['t' => $tenantId]
        );
    }

    public static function nextNumber(int $tenantId): string
    {
        $year = date('Y');
        // MAX(suffix) + 1 — robust against soft-deletes and gaps.
        $max = (int) Database::scalar(
            "SELECT MAX(CAST(SUBSTRING_INDEX(contract_number, '-', -1) AS UNSIGNED))
               FROM contracts WHERE tenant_id = :t AND contract_number LIKE :pfx",
            ['t' => $tenantId, 'pfx' => "CTR-{$year}-%"]
        );
        return sprintf('CTR-%s-%04d', $year, $max + 1);
    }

    /**
     * Ensure the contract has a share_token. If one already exists, returns it;
     * otherwise generates a 128-bit random hex string, persists it, and returns it.
     * The token IS the auth on the public viewing page.
     */
    public static function ensureShareToken(int $id, int $tenantId): ?string
    {
        $row = Database::selectOne(
            "SELECT share_token FROM contracts WHERE id = :id AND tenant_id = :t AND deleted_at IS NULL LIMIT 1",
            ['id' => $id, 't' => $tenantId]
        );
        if (!$row) return null;
        if (!empty($row['share_token'])) return $row['share_token'];
        // Generate unique token (16 bytes = 32 hex chars + 8 bytes salt for uniqueness)
        do {
            $tok = bin2hex(random_bytes(20));   // 40 hex chars
            $exists = Database::scalar("SELECT 1 FROM contracts WHERE share_token = :s LIMIT 1", ['s' => $tok]);
        } while ($exists);
        Database::execute(
            "UPDATE contracts SET share_token = :s, share_created_at = NOW()
               WHERE id = :id AND tenant_id = :t",
            ['s' => $tok, 'id' => $id, 't' => $tenantId]
        );
        return $tok;
    }

    /** Revoke the current token (typed wrong, leaked, etc.). */
    public static function revokeShareToken(int $id, int $tenantId): void
    {
        Database::execute(
            "UPDATE contracts SET share_token = NULL, share_created_at = NULL WHERE id = :id AND tenant_id = :t",
            ['id' => $id, 't' => $tenantId]
        );
    }

    /**
     * Resolve a contract by its share_token alone — no tenant context needed because
     * the token IS the secret. The returned row carries its OWN tenant_id which is
     * what every downstream query must use.
     */
    public static function findByShareToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{32,80}$/i', $token)) return null;
        return Database::selectOne(
            "SELECT * FROM contracts
               WHERE share_token = :t AND deleted_at IS NULL LIMIT 1",
            ['t' => $token]
        );
    }
}
