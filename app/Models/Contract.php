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
}
