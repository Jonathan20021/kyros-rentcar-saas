<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Incident extends Model
{
    protected static string $table = 'incidents';
    protected static bool $softDeletes = false;

    public const TYPES = ['traffic_fine','exterior_damage','interior_damage','accident','theft','late','fuel','cleaning','key_loss','other'];
    public const STATUSES = ['open','review','charged','cancelled','closed'];

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT i.*, v.brand, v.model, v.plate_number,
                       CONCAT(c.first_name,' ',c.last_name) AS customer_name, ct.contract_number
                  FROM incidents i
                  LEFT JOIN vehicles v ON v.id = i.vehicle_id
                  LEFT JOIN customers c ON c.id = i.customer_id
                  LEFT JOIN contracts ct ON ct.id = i.contract_id
                 WHERE i.tenant_id = :t";
        $params = ['t' => $tenantId];
        if (!empty($filters['status'])) { $sql .= " AND i.status = :st"; $params['st'] = $filters['status']; }
        if (!empty($filters['type']))   { $sql .= " AND i.type = :ty";   $params['ty'] = $filters['type']; }
        $sql .= " ORDER BY i.created_at DESC";
        return Database::select($sql, $params);
    }

    public static function statusCounts(int $tenantId): array
    {
        $rows = Database::select("SELECT status, COUNT(*) c, COALESCE(SUM(amount),0) total FROM incidents WHERE tenant_id=:t GROUP BY status", ['t'=>$tenantId]);
        $out = [];
        foreach (self::STATUSES as $s) { $out[$s] = ['c'=>0,'total'=>0.0]; }
        foreach ($rows as $r) { $out[$r['status']] = ['c'=>(int)$r['c'],'total'=>(float)$r['total']]; }
        return $out;
    }
}
