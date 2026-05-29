<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * Drivers (chauffeurs). Optional staff who can be assigned to reservations
 * and contracts when the rent car offers driver-included service.
 */
class Driver extends Model
{
    protected static string $table = 'drivers';

    public const STATUSES = [
        'active'   => 'Activo',
        'vacation' => 'De vacaciones',
        'inactive' => 'Inactivo',
    ];

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT d.*,
                       (SELECT COUNT(*) FROM contracts c WHERE c.driver_id = d.id AND c.deleted_at IS NULL) AS trips_count
                  FROM drivers d
                 WHERE d.tenant_id = :t AND d.deleted_at IS NULL";
        $p = ['t' => $tenantId];
        if (!empty($filters['status'])) { $sql .= " AND d.status = :s"; $p['s'] = $filters['status']; }
        if (!empty($filters['search'])) { $sql .= " AND (d.first_name LIKE :q OR d.last_name LIKE :q OR d.document_number LIKE :q OR d.license_number LIKE :q)"; $p['q'] = '%'.$filters['search'].'%'; }
        $sql .= " ORDER BY d.status='active' DESC, d.first_name, d.last_name";
        return Database::select($sql, $p);
    }

    /** Drivers that can be assigned right now (active only). */
    public static function activeForTenant(int $tenantId): array
    {
        return Database::select(
            "SELECT id, first_name, last_name, daily_rate, photo
               FROM drivers WHERE tenant_id = :t AND deleted_at IS NULL AND status = 'active'
              ORDER BY first_name, last_name",
            ['t' => $tenantId]
        );
    }

    public static function statusCounts(int $tenantId): array
    {
        $rows = Database::select(
            "SELECT status, COUNT(*) c FROM drivers WHERE tenant_id = :t AND deleted_at IS NULL GROUP BY status",
            ['t' => $tenantId]
        );
        $out = ['active'=>0,'vacation'=>0,'inactive'=>0];
        foreach ($rows as $r) { $out[$r['status']] = (int) $r['c']; }
        return $out;
    }

    /** Drivers whose license is expiring (or expired) within X days. */
    public static function licenseAlerts(int $tenantId, int $daysAhead = 30): array
    {
        return Database::select(
            "SELECT id, first_name, last_name, license_expiration
               FROM drivers
              WHERE tenant_id = :t AND deleted_at IS NULL AND status != 'inactive'
                AND license_expiration IS NOT NULL
                AND license_expiration <= DATE_ADD(CURDATE(), INTERVAL :d DAY)
              ORDER BY license_expiration ASC
              LIMIT 10",
            ['t' => $tenantId, 'd' => $daysAhead]
        );
    }
}
