<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Customer extends Model
{
    protected static string $table = 'customers';

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT * FROM customers WHERE tenant_id = :t AND deleted_at IS NULL";
        $params = ['t' => $tenantId];
        if (!empty($filters['status'])) {
            $sql .= " AND status = :st"; $params['st'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE :s OR last_name LIKE :s OR document_number LIKE :s OR phone LIKE :s OR email LIKE :s)";
            $params['s'] = '%' . $filters['search'] . '%';
        }
        $sql .= " ORDER BY created_at DESC";
        return Database::select($sql, $params);
    }

    public static function isBlacklisted(int $tenantId, int $customerId): bool
    {
        $status = Database::scalar(
            "SELECT status FROM customers WHERE tenant_id = :t AND id = :id AND deleted_at IS NULL",
            ['t' => $tenantId, 'id' => $customerId]
        );
        return in_array($status, ['blacklist', 'blocked'], true);
    }

    public static function newThisMonth(int $tenantId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM customers
              WHERE tenant_id = :t AND deleted_at IS NULL
                AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())",
            ['t' => $tenantId]
        );
    }
}
