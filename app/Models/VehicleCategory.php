<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class VehicleCategory extends Model
{
    protected static string $table = 'vehicle_categories';
    protected static bool $softDeletes = false;

    public static function forTenant(int $tenantId): array
    {
        return Database::select(
            "SELECT * FROM vehicle_categories WHERE tenant_id = :t AND status = 'active' ORDER BY name ASC",
            ['t' => $tenantId]
        );
    }

    /** All categories with vehicle counts (for the management screen). */
    public static function listWithCounts(int $tenantId): array
    {
        return Database::select(
            "SELECT c.*, (SELECT COUNT(*) FROM vehicles v WHERE v.category_id = c.id AND v.deleted_at IS NULL) AS vehicle_count
               FROM vehicle_categories c
              WHERE c.tenant_id = :t
              ORDER BY c.status = 'active' DESC, c.name",
            ['t' => $tenantId]
        );
    }

    public static function uniqueSlug(int $tenantId, string $base, ?int $exceptId = null): string
    {
        $slug = slugify($base) ?: 'cat';
        $candidate = $slug; $i = 1;
        while (true) {
            $sql = "SELECT COUNT(*) FROM vehicle_categories WHERE tenant_id = :t AND slug = :s";
            $p = ['t' => $tenantId, 's' => $candidate];
            if ($exceptId) { $sql .= " AND id <> :id"; $p['id'] = $exceptId; }
            if ((int) Database::scalar($sql, $p) === 0) return $candidate;
            $candidate = $slug . '-' . (++$i);
        }
    }
}
