<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Tenant extends Model
{
    protected static string $table = 'tenants';
    protected static bool $tenantScoped = false; // tenants table is the tenant itself

    public static function findBySlug(string $slug): ?array
    {
        return Database::selectOne(
            "SELECT * FROM tenants WHERE slug = :slug AND deleted_at IS NULL LIMIT 1",
            ['slug' => $slug]
        );
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM tenants WHERE slug = :slug";
        $params = ['slug' => $slug];
        if ($exceptId) { $sql .= " AND id <> :id"; $params['id'] = $exceptId; }
        return (int) Database::scalar($sql, $params) > 0;
    }

    /** Generate a unique global slug from a base name. */
    public static function uniqueSlug(string $base, ?int $exceptId = null): string
    {
        $slug = slugify($base);
        $candidate = $slug;
        $i = 2;
        while (self::slugExists($candidate, $exceptId)) {
            $candidate = $slug . '-' . $i++;
        }
        return $candidate;
    }

    public static function withPlan(int $id): ?array
    {
        return Database::selectOne(
            "SELECT t.*, p.name AS plan_name, p.slug AS plan_slug, p.max_vehicles, p.max_users
               FROM tenants t LEFT JOIN plans p ON p.id = t.plan_id
              WHERE t.id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public static function allWithStats(): array
    {
        return Database::select(
            "SELECT t.*, p.name AS plan_name,
                    (SELECT COUNT(*) FROM vehicles v WHERE v.tenant_id = t.id AND v.deleted_at IS NULL) AS vehicles_count,
                    (SELECT COUNT(*) FROM users u WHERE u.tenant_id = t.id AND u.deleted_at IS NULL) AS users_count
               FROM tenants t
               LEFT JOIN plans p ON p.id = t.plan_id
              WHERE t.deleted_at IS NULL
              ORDER BY t.created_at DESC"
        );
    }
}
