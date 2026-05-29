<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Maintenance extends Model
{
    protected static string $table = 'maintenance_records';
    protected static bool $softDeletes = false;

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT m.*, v.brand, v.model, v.plate_number
                  FROM maintenance_records m
                  JOIN vehicles v ON v.id = m.vehicle_id
                 WHERE m.tenant_id = :t";
        $params = ['t' => $tenantId];
        if (!empty($filters['status'])) {
            $sql .= " AND m.status = :st"; $params['st'] = $filters['status'];
        }
        $sql .= " ORDER BY m.start_date DESC, m.id DESC";
        return Database::select($sql, $params);
    }

    public static function activeCount(int $tenantId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM maintenance_records
              WHERE tenant_id = :t AND status IN ('scheduled','in_progress')",
            ['t' => $tenantId]
        );
    }
}
