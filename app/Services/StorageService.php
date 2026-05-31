<?php
namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\Logger;

/**
 * StorageService — single source of truth for tenant storage quotas.
 *
 * Architecture:
 *  - `plans.storage_mb` is the BASE quota included with a plan
 *  - `tenants.storage_extra_mb` is APPROVED extra (granted by super admin)
 *  - Effective quota = plan_storage_mb + tenant.storage_extra_mb
 *  - `tenants.storage_used_bytes` is the cached current usage
 *  - `tenants.storage_usage_at` is when the cache was last computed
 *
 * The usage cache lets us check quotas on every upload in O(1) without
 * walking the upload tree. We refresh the cache opportunistically on
 * upload/delete and on a TTL (default 6h) when dashboards load.
 */
class StorageService
{
    /** Seconds between automatic recomputes from the upload tree. */
    public const CACHE_TTL_SECONDS = 6 * 3600;

    /** Soft warning threshold (UI nag, no block). */
    public const WARN_AT_PCT = 80;
    /** Hard block threshold — upload fails. */
    public const BLOCK_AT_PCT = 100;

    /** Effective quota in BYTES for a tenant (plan base + approved extra). */
    public static function quotaBytes(int $tenantId): int
    {
        $row = Database::selectOne(
            "SELECT COALESCE(p.storage_mb, 0) AS base_mb, t.storage_extra_mb AS extra_mb
               FROM tenants t
          LEFT JOIN plans p ON p.id = t.plan_id
              WHERE t.id = :t",
            ['t' => $tenantId]
        );
        if (!$row) return 0;
        return ((int) $row['base_mb'] + (int) $row['extra_mb']) * 1024 * 1024;
    }

    /** Current usage in bytes (cached). Computes fresh if cache is stale. */
    public static function usedBytes(int $tenantId, bool $forceRecompute = false): int
    {
        $row = Database::selectOne(
            "SELECT storage_used_bytes, storage_usage_at FROM tenants WHERE id = :t",
            ['t' => $tenantId]
        );
        if (!$row) return 0;

        $stale = $forceRecompute
              || empty($row['storage_usage_at'])
              || (time() - strtotime($row['storage_usage_at'])) > self::CACHE_TTL_SECONDS;

        if ($stale) {
            return self::recompute($tenantId);
        }
        return (int) $row['storage_used_bytes'];
    }

    /** Walk the tenant's upload tree and write the totals back to the cache. */
    public static function recompute(int $tenantId): int
    {
        $total = 0;
        $byKind = ['photos' => 0, 'documents' => 0, 'signatures' => 0, 'branding' => 0, 'other' => 0];

        // Pull every file path this tenant has referenced — across vehicle_images,
        // tenants.logo/cover_image, customer_signature on contracts, photos on
        // contracts, driver photos, expense receipts, etc. We size each file
        // once via filesize().
        $paths = self::collectPaths($tenantId);

        $rootPublic = Config::get('app.root_path') . DIRECTORY_SEPARATOR . 'public';
        foreach ($paths as $kind => $list) {
            foreach ($list as $p) {
                $abs = self::resolveAbs($p, $rootPublic);
                if (!$abs) continue;
                $sz = @filesize($abs);
                if ($sz === false) continue;
                $total += $sz;
                $byKind[$kind] = ($byKind[$kind] ?? 0) + $sz;
            }
        }

        Database::execute(
            "UPDATE tenants SET storage_used_bytes = :b, storage_usage_at = NOW() WHERE id = :t",
            ['b' => $total, 't' => $tenantId]
        );
        return $total;
    }

    /** Break the usage down by category for the storage page. */
    public static function usageBreakdown(int $tenantId): array
    {
        $rootPublic = Config::get('app.root_path') . DIRECTORY_SEPARATOR . 'public';
        $paths = self::collectPaths($tenantId);
        $out = ['photos' => 0, 'documents' => 0, 'signatures' => 0, 'branding' => 0, 'other' => 0];
        $counts = ['photos' => 0, 'documents' => 0, 'signatures' => 0, 'branding' => 0, 'other' => 0];
        foreach ($paths as $kind => $list) {
            foreach ($list as $p) {
                $abs = self::resolveAbs($p, $rootPublic);
                if (!$abs) continue;
                $sz = @filesize($abs);
                if ($sz === false) continue;
                $out[$kind] = ($out[$kind] ?? 0) + $sz;
                $counts[$kind] = ($counts[$kind] ?? 0) + 1;
            }
        }
        return ['bytes' => $out, 'counts' => $counts];
    }

    /**
     * Add `$extraBytes` to the cached usage in a single SQL UPDATE. Cheap and
     * race-safe. Called by FileUploader after a successful upload.
     */
    public static function addBytes(int $tenantId, int $extraBytes): void
    {
        if ($extraBytes <= 0) return;
        Database::execute(
            "UPDATE tenants
                SET storage_used_bytes = storage_used_bytes + :b,
                    storage_usage_at   = NOW()
              WHERE id = :t",
            ['b' => $extraBytes, 't' => $tenantId]
        );
    }

    /** Subtract bytes (file deleted). Floors at 0. */
    public static function removeBytes(int $tenantId, int $bytes): void
    {
        if ($bytes <= 0) return;
        Database::execute(
            "UPDATE tenants
                SET storage_used_bytes = GREATEST(0, CAST(storage_used_bytes AS SIGNED) - :b),
                    storage_usage_at   = NOW()
              WHERE id = :t",
            ['b' => $bytes, 't' => $tenantId]
        );
    }

    /**
     * Can this tenant store an additional $bytes? Returns [bool, string] where
     * the string is a user-facing reason when blocked. Use BEFORE saving.
     */
    public static function canStore(int $tenantId, int $bytes): array
    {
        if ($tenantId <= 0) return [true, ''];
        $quota = self::quotaBytes($tenantId);
        if ($quota <= 0) {
            return [false, 'Tu plan no incluye almacenamiento. Contacta a soporte.'];
        }
        $used  = self::usedBytes($tenantId);
        if (($used + $bytes) > $quota) {
            $free = max(0, $quota - $used);
            return [false, 'Almacenamiento insuficiente. Disponible: ' . self::format($free)
                         . '. Solicita más espacio desde Configuración → Almacenamiento.'];
        }
        return [true, ''];
    }

    /** Snapshot for dashboards / widgets / API. */
    public static function snapshot(int $tenantId): array
    {
        $quota = self::quotaBytes($tenantId);
        $used  = self::usedBytes($tenantId);
        $pct   = $quota > 0 ? min(100.0, ($used / $quota) * 100) : 0.0;
        $level = $pct >= self::BLOCK_AT_PCT ? 'block' : ($pct >= self::WARN_AT_PCT ? 'warn' : 'ok');
        return [
            'used_bytes'    => $used,
            'quota_bytes'   => $quota,
            'free_bytes'    => max(0, $quota - $used),
            'percent'       => round($pct, 1),
            'level'         => $level,
            'used_human'    => self::format($used),
            'quota_human'   => self::format($quota),
            'free_human'    => self::format(max(0, $quota - $used)),
        ];
    }

    /** "12.3 MB" style format. */
    public static function format(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        $units = ['KB','MB','GB','TB'];
        $v = $bytes / 1024.0;
        $i = 0;
        while ($v >= 1024 && $i < count($units) - 1) { $v /= 1024.0; $i++; }
        return number_format($v, $v < 10 ? 2 : 1) . ' ' . $units[$i];
    }

    /**
     * Collect every file path this tenant has referenced, grouped by category.
     * Sources are the columns we know hold uploaded media; new modules should
     * register their paths here when added.
     */
    private static function collectPaths(int $tenantId): array
    {
        $out = ['photos' => [], 'documents' => [], 'signatures' => [], 'branding' => [], 'other' => []];

        // Vehicle gallery + main image
        foreach (Database::select("SELECT path FROM vehicle_images WHERE tenant_id = :t", ['t' => $tenantId]) as $r) {
            $out['photos'][] = $r['path'];
        }
        foreach (Database::select("SELECT main_image FROM vehicles WHERE tenant_id = :t AND main_image IS NOT NULL", ['t' => $tenantId]) as $r) {
            $out['photos'][] = $r['main_image'];
        }

        // Tenant branding
        foreach (Database::select("SELECT logo, cover_image FROM tenants WHERE id = :t", ['t' => $tenantId]) as $r) {
            if (!empty($r['logo']))         $out['branding'][] = $r['logo'];
            if (!empty($r['cover_image']))  $out['branding'][] = $r['cover_image'];
        }

        // Contract photos + signatures
        foreach (Database::select("SELECT path FROM contract_photos WHERE tenant_id = :t", ['t' => $tenantId]) as $r) {
            $out['documents'][] = $r['path'];
        }
        foreach (Database::select("SELECT customer_signature FROM contracts WHERE tenant_id = :t AND customer_signature IS NOT NULL", ['t' => $tenantId]) as $r) {
            $out['signatures'][] = $r['customer_signature'];
        }

        // Driver photos
        if (Database::scalar("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'drivers'")) {
            foreach (Database::select("SELECT photo FROM drivers WHERE tenant_id = :t AND photo IS NOT NULL", ['t' => $tenantId]) as $r) {
                $out['photos'][] = $r['photo'];
            }
        }

        // Maintenance invoice scans
        if (Database::scalar("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'maintenance_records' AND column_name = 'invoice_file'")) {
            foreach (Database::select("SELECT invoice_file FROM maintenance_records WHERE tenant_id = :t AND invoice_file IS NOT NULL", ['t' => $tenantId]) as $r) {
                $out['documents'][] = $r['invoice_file'];
            }
        }

        return $out;
    }

    /** Map /assets/uploads/... → absolute path under public/, or pass abs paths through. */
    private static function resolveAbs(string $path, string $publicRoot): ?string
    {
        if (preg_match('#^(https?://|data:)#i', $path)) return null;
        if (str_starts_with($path, '/')) {
            $candidate = $publicRoot . str_replace('/', DIRECTORY_SEPARATOR, $path);
            return is_file($candidate) ? $candidate : null;
        }
        return is_file($path) ? $path : null;
    }
}
