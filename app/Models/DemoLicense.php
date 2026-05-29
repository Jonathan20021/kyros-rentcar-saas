<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * Demo licenses — codes that spin up a temporary tenant on a chosen plan
 * for N hours (default 5). After expiry, a sweep deletes the tenant and all
 * of its scoped data (FK CASCADE handles most of it).
 *
 * Not tenant-scoped: this is a platform-level catalog like plans/roles.
 */
class DemoLicense extends Model
{
    protected static string $table = 'demo_licenses';
    protected static bool $tenantScoped = false;
    protected static bool $softDeletes = false;

    public static function findActiveByCode(string $code): ?array
    {
        return Database::selectOne(
            "SELECT dl.*, p.name AS plan_name, p.slug AS plan_slug
               FROM demo_licenses dl
               JOIN plans p ON p.id = dl.plan_id
              WHERE UPPER(dl.code) = UPPER(:c) AND dl.status = 'active' LIMIT 1",
            ['c' => trim($code)]
        );
    }

    public static function isUsable(array $lic): bool
    {
        if (($lic['status'] ?? '') !== 'active') return false;
        if ($lic['max_uses'] !== null && (int) $lic['used_count'] >= (int) $lic['max_uses']) return false;
        return true;
    }

    public static function incrementUse(int $id): void
    {
        Database::execute("UPDATE demo_licenses SET used_count = used_count + 1 WHERE id = :id", ['id' => $id]);
    }

    /** Publicly visible demo offers for the login page. */
    public static function publicOffers(): array
    {
        return Database::select(
            "SELECT dl.code, dl.label, dl.hours_valid, p.name AS plan_name, p.slug AS plan_slug,
                    p.price_monthly, p.max_vehicles, p.max_users
               FROM demo_licenses dl
               JOIN plans p ON p.id = dl.plan_id
              WHERE dl.status = 'active' AND (dl.max_uses IS NULL OR dl.used_count < dl.max_uses)
              ORDER BY dl.plan_id"
        );
    }
}
