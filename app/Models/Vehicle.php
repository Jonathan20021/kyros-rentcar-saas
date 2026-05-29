<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Vehicle extends Model
{
    protected static string $table = 'vehicles';

    public const STATUSES = [
        'available', 'reserved', 'rented', 'maintenance',
        'out_of_service', 'cleaning', 'pending_delivery', 'pending_return',
    ];

    /** Vehicles for the admin list with category name + optional filters. */
    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT v.*, c.name AS category_name, l.name AS location_name
                  FROM vehicles v
                  LEFT JOIN vehicle_categories c ON c.id = v.category_id
                  LEFT JOIN locations l ON l.id = v.location_id
                 WHERE v.tenant_id = :t AND v.deleted_at IS NULL";
        $params = ['t' => $tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND v.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['category_id'])) {
            $sql .= " AND v.category_id = :cat";
            $params['cat'] = (int) $filters['category_id'];
        }
        if (!empty($filters['location_id'])) {
            $sql .= " AND v.location_id = :loc";
            $params['loc'] = (int) $filters['location_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (v.brand LIKE :s OR v.model LIKE :s OR v.plate_number LIKE :s)";
            $params['s'] = '%' . $filters['search'] . '%';
        }
        $sql .= " ORDER BY v.created_at DESC";
        return Database::select($sql, $params);
    }

    public static function findBySlug(int $tenantId, string $slug): ?array
    {
        return Database::selectOne(
            "SELECT v.*, c.name AS category_name FROM vehicles v
               LEFT JOIN vehicle_categories c ON c.id = v.category_id
              WHERE v.tenant_id = :t AND v.slug = :slug AND v.deleted_at IS NULL LIMIT 1",
            ['t' => $tenantId, 'slug' => $slug]
        );
    }

    public static function slugExists(int $tenantId, string $slug, ?int $exceptId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM vehicles WHERE tenant_id = :t AND slug = :slug AND deleted_at IS NULL";
        $params = ['t' => $tenantId, 'slug' => $slug];
        if ($exceptId) { $sql .= " AND id <> :id"; $params['id'] = $exceptId; }
        return (int) Database::scalar($sql, $params) > 0;
    }

    public static function uniqueSlug(int $tenantId, string $base, ?int $exceptId = null): string
    {
        $slug = slugify($base);
        $candidate = $slug;
        $i = 2;
        while (self::slugExists($tenantId, $candidate, $exceptId)) {
            $candidate = $slug . '-' . $i++;
        }
        return $candidate;
    }

    public static function plateExists(int $tenantId, string $plate, ?int $exceptId = null): bool
    {
        if ($plate === '') return false;
        $sql = "SELECT COUNT(*) FROM vehicles WHERE tenant_id = :t AND plate_number = :p AND deleted_at IS NULL";
        $params = ['t' => $tenantId, 'p' => $plate];
        if ($exceptId) { $sql .= " AND id <> :id"; $params['id'] = $exceptId; }
        return (int) Database::scalar($sql, $params) > 0;
    }

    /** Public storefront listing with filters (date range availability handled separately). */
    public static function publicList(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT v.*, c.name AS category_name, c.slug AS category_slug
                  FROM vehicles v
                  LEFT JOIN vehicle_categories c ON c.id = v.category_id
                 WHERE v.tenant_id = :t AND v.is_public = 1 AND v.deleted_at IS NULL
                   AND v.status NOT IN ('out_of_service')";
        $params = ['t' => $tenantId];

        if (!empty($filters['category_id'])) {
            $sql .= " AND v.category_id = :cat"; $params['cat'] = (int) $filters['category_id'];
        }
        if (!empty($filters['transmission'])) {
            $sql .= " AND v.transmission = :tr"; $params['tr'] = $filters['transmission'];
        }
        if (!empty($filters['fuel_type'])) {
            $sql .= " AND v.fuel_type = :fu"; $params['fu'] = $filters['fuel_type'];
        }
        if (!empty($filters['passengers'])) {
            $sql .= " AND v.passengers >= :pa"; $params['pa'] = (int) $filters['passengers'];
        }
        if (!empty($filters['price_min'])) {
            $sql .= " AND v.daily_price >= :pmin"; $params['pmin'] = (float) $filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $sql .= " AND v.daily_price <= :pm"; $params['pm'] = (float) $filters['price_max'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (v.brand LIKE :s OR v.model LIKE :s)"; $params['s'] = '%' . $filters['search'] . '%';
        }
        $order = [
            'price_asc'  => 'v.daily_price ASC',
            'price_desc' => 'v.daily_price DESC',
            'newest'     => 'v.year DESC, v.id DESC',
        ][$filters['sort'] ?? ''] ?? 'v.is_featured DESC, v.daily_price ASC';
        $sql .= " ORDER BY {$order}";
        return Database::select($sql, $params);
    }

    /**
     * Availability check: returns true if the vehicle has NO blocking
     * reservation/contract overlapping [start,end] and is not in a blocking status.
     * Enforces business rule #1 (no double booking).
     */
    public static function isAvailable(int $tenantId, int $vehicleId, string $start, string $end, ?int $ignoreReservationId = null): bool
    {
        $vehicle = self::find($vehicleId, $tenantId);
        if (!$vehicle) return false;
        if (in_array($vehicle['status'], ['maintenance', 'out_of_service'], true)) {
            return false;
        }

        // Overlapping active reservations
        $sql = "SELECT COUNT(*) FROM reservations
                 WHERE tenant_id = :t AND vehicle_id = :v AND deleted_at IS NULL
                   AND status IN ('confirmed','in_progress','converted')
                   AND start_datetime < :end AND end_datetime > :start";
        $params = ['t' => $tenantId, 'v' => $vehicleId, 'start' => $start, 'end' => $end];
        if ($ignoreReservationId) {
            $sql .= " AND id <> :ignore"; $params['ignore'] = $ignoreReservationId;
        }
        if ((int) Database::scalar($sql, $params) > 0) {
            return false;
        }

        // Overlapping active contracts
        $contractParams = ['t' => $tenantId, 'v' => $vehicleId, 'start' => $start, 'end' => $end];
        $sqlC = "SELECT COUNT(*) FROM contracts
                  WHERE tenant_id = :t AND vehicle_id = :v AND deleted_at IS NULL
                    AND status IN ('active','overdue')
                    AND start_datetime < :end AND end_datetime > :start";
        return (int) Database::scalar($sqlC, $contractParams) === 0;
    }

    /** Daily-price distribution for the storefront histogram (public vehicles). */
    public static function priceHistogram(int $tenantId, int $buckets = 22): array
    {
        $prices = array_map('floatval', array_column(Database::select(
            "SELECT daily_price FROM vehicles
              WHERE tenant_id = :t AND is_public = 1 AND deleted_at IS NULL AND status <> 'out_of_service'",
            ['t' => $tenantId]
        ), 'daily_price'));

        if (empty($prices)) {
            return ['min' => 0, 'max' => 100, 'bars' => array_fill(0, $buckets, 0)];
        }
        $min = floor(min($prices));
        $max = ceil(max($prices));
        if ($max <= $min) { $max = $min + 1; }
        $span = ($max - $min) / $buckets;
        $bars = array_fill(0, $buckets, 0);
        foreach ($prices as $p) {
            $idx = (int) min($buckets - 1, floor(($p - $min) / $span));
            $bars[$idx]++;
        }
        return ['min' => (int) $min, 'max' => (int) $max, 'bars' => $bars];
    }

    public static function statusCounts(int $tenantId, ?int $locationId = null): array
    {
        $sql = "SELECT status, COUNT(*) AS c FROM vehicles
                 WHERE tenant_id = :t AND deleted_at IS NULL";
        $p = ['t' => $tenantId];
        if ($locationId) { $sql .= " AND location_id = :loc"; $p['loc'] = $locationId; }
        $sql .= " GROUP BY status";
        $rows = Database::select($sql, $p);
        $out = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $r) { $out[$r['status']] = (int) $r['c']; }
        return $out;
    }
}
