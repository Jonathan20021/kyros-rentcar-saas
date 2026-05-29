<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Reservation extends Model
{
    protected static string $table = 'reservations';

    public const STATUSES = ['pending','confirmed','rejected','cancelled','in_progress','converted','finished'];

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT r.*, v.brand, v.model, v.plate_number,
                       COALESCE(CONCAT(c.first_name,' ',c.last_name), r.lead_name) AS customer_name
                  FROM reservations r
                  JOIN vehicles v ON v.id = r.vehicle_id
                  LEFT JOIN customers c ON c.id = r.customer_id
                 WHERE r.tenant_id = :t AND r.deleted_at IS NULL";
        $params = ['t' => $tenantId];
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :st"; $params['st'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (r.reservation_code LIKE :s OR r.lead_name LIKE :s OR c.first_name LIKE :s)";
            $params['s'] = '%' . $filters['search'] . '%';
        }
        $sql .= " ORDER BY r.start_datetime DESC";
        return Database::select($sql, $params);
    }

    public static function todayCount(int $tenantId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM reservations
              WHERE tenant_id = :t AND deleted_at IS NULL
                AND DATE(start_datetime) = CURDATE()
                AND status IN ('confirmed','in_progress','pending')",
            ['t' => $tenantId]
        );
    }

    public static function statusCounts(int $tenantId): array
    {
        $rows = Database::select(
            "SELECT status, COUNT(*) c FROM reservations
              WHERE tenant_id = :t AND deleted_at IS NULL GROUP BY status",
            ['t' => $tenantId]
        );
        $out = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $r) { $out[$r['status']] = (int) $r['c']; }
        return $out;
    }

    public static function upcomingReturns(int $tenantId, int $days = 7): array
    {
        return Database::select(
            "SELECT r.*, v.brand, v.model, v.plate_number,
                    COALESCE(CONCAT(c.first_name,' ',c.last_name), r.lead_name) AS customer_name
               FROM reservations r
               JOIN vehicles v ON v.id = r.vehicle_id
               LEFT JOIN customers c ON c.id = r.customer_id
              WHERE r.tenant_id = :t AND r.deleted_at IS NULL
                AND r.status IN ('confirmed','in_progress','converted')
                AND r.end_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :d DAY)
              ORDER BY r.end_datetime ASC LIMIT 10",
            ['t' => $tenantId, 'd' => $days]
        );
    }

    public static function nextCode(int $tenantId): string
    {
        $year = date('Y');
        $max = (int) Database::scalar(
            "SELECT MAX(CAST(SUBSTRING_INDEX(reservation_code, '-', -1) AS UNSIGNED))
               FROM reservations WHERE tenant_id = :t AND reservation_code LIKE :pfx",
            ['t' => $tenantId, 'pfx' => "RSV-{$year}-%"]
        );
        return sprintf('RSV-%s-%04d', $year, $max + 1);
    }

    /** Calendar events (FullCalendar JSON) for tenant flotilla. */
    public static function calendarEvents(int $tenantId): array
    {
        $rows = Database::select(
            "SELECT r.id, r.reservation_code, r.status, r.start_datetime, r.end_datetime,
                    v.brand, v.model
               FROM reservations r
               JOIN vehicles v ON v.id = r.vehicle_id
              WHERE r.tenant_id = :t AND r.deleted_at IS NULL
                AND r.status NOT IN ('cancelled','rejected')",
            ['t' => $tenantId]
        );
        $colors = [
            'pending'    => '#F59E0B',
            'confirmed'  => '#10B981',
            'in_progress'=> '#3B82F6',
            'converted'  => '#8B5CF6',
            'finished'   => '#94A3B8',
        ];
        $events = [];
        foreach ($rows as $r) {
            $events[] = [
                'id'    => $r['id'],
                'title' => $r['reservation_code'] . ' · ' . $r['brand'] . ' ' . $r['model'],
                'start' => str_replace(' ', 'T', $r['start_datetime']),
                'end'   => str_replace(' ', 'T', $r['end_datetime']),
                'color' => $colors[$r['status']] ?? '#4F46E5',
            ];
        }
        return $events;
    }
}
