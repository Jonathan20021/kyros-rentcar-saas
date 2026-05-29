<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Location extends Model
{
    protected static string $table = 'locations';

    /** Active branches for selects. */
    public static function activeForTenant(int $tenantId): array
    {
        return Database::select(
            "SELECT id, name FROM locations
              WHERE tenant_id = :t AND deleted_at IS NULL AND status = 'active'
              ORDER BY name",
            ['t' => $tenantId]
        );
    }

    /** Branches with fleet/staff counts for the index. */
    public static function listWithCounts(int $tenantId): array
    {
        return Database::select(
            "SELECT l.*,
                    (SELECT COUNT(*) FROM vehicles v WHERE v.location_id = l.id AND v.deleted_at IS NULL) AS vehicle_count,
                    (SELECT COUNT(*) FROM users u WHERE u.location_id = l.id AND u.deleted_at IS NULL) AS staff_count
               FROM locations l
              WHERE l.tenant_id = :t AND l.deleted_at IS NULL
              ORDER BY l.status = 'active' DESC, l.name",
            ['t' => $tenantId]
        );
    }
}
