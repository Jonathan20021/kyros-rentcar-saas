<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Database;

/**
 * Cross-module search for the header command palette.
 * Returns JSON groups: vehicles, customers, reservations, contracts, drivers.
 *
 * NOTE: PDO::ATTR_EMULATE_PREPARES is OFF for this app, which disallows
 * reusing the same named placeholder multiple times in a single query.
 * Hence the helper below expands a single `?q?` token into uniquely-named
 * placeholders the params array supplies.
 */
class SearchController extends AdminController
{
    /** Replace `?q?` with `:q0, :q1, :q2 …` and produce the params array. */
    private static function bindLike(string $sql, string $like, int $count, array $extra = []): array
    {
        $i = 0;
        $sql = preg_replace_callback('/\?q\?/', function () use (&$i) {
            return ':q' . $i++;
        }, $sql);
        $params = $extra;
        for ($k = 0; $k < $count; $k++) {
            $params['q' . $k] = $like;
        }
        return [$sql, $params];
    }

    public function search(Request $request): void
    {
        $tid = $this->tenantId();
        $q   = trim((string) $request->str('q', ''));
        if (mb_strlen($q) < 2) {
            $this->json(['query' => $q, 'groups' => []]);
        }

        $like = '%' . $q . '%';
        $LIM  = 5;
        $groups = [];

        // --- Vehicles -----------------------------------------------------
        [$sql, $p] = self::bindLike(
            "SELECT id, brand, model, year, plate_number, status
               FROM vehicles
              WHERE tenant_id = :t AND deleted_at IS NULL
                AND (brand LIKE ?q? OR model LIKE ?q? OR plate_number LIKE ?q? OR vin LIKE ?q?)
              ORDER BY status='available' DESC, brand LIMIT $LIM",
            $like, 4, ['t' => $tid]
        );
        $groups[] = ['label'=>'Vehículos','icon'=>'car','items'=>array_map(function ($v) {
            return [
                'title'    => trim($v['brand'] . ' ' . $v['model'] . ' ' . ($v['year'] ?? '')),
                'subtitle' => trim(($v['plate_number'] ?: 'sin placa') . ' · ' . status_label($v['status'])),
                'url'      => url('/admin/vehicles/show/' . $v['id']),
            ];
        }, Database::select($sql, $p))];

        // --- Customers ----------------------------------------------------
        [$sql, $p] = self::bindLike(
            "SELECT id, first_name, last_name, document_number, phone
               FROM customers
              WHERE tenant_id = :t AND deleted_at IS NULL
                AND (first_name LIKE ?q? OR last_name LIKE ?q? OR document_number LIKE ?q? OR phone LIKE ?q? OR email LIKE ?q?)
              ORDER BY first_name LIMIT $LIM",
            $like, 5, ['t' => $tid]
        );
        $groups[] = ['label'=>'Clientes','icon'=>'users','items'=>array_map(function ($c) {
            return [
                'title'    => trim($c['first_name'] . ' ' . ($c['last_name'] ?? '')),
                'subtitle' => trim(($c['document_number'] ?: '') . ($c['phone'] ? ' · ' . $c['phone'] : '')),
                'url'      => url('/admin/customers/show/' . $c['id']),
            ];
        }, Database::select($sql, $p))];

        // --- Reservations -------------------------------------------------
        [$sql, $p] = self::bindLike(
            "SELECT r.id, r.reservation_code, r.status, r.lead_name,
                    v.brand, v.model,
                    TRIM(CONCAT(c.first_name,' ',COALESCE(c.last_name,''))) AS customer_name
               FROM reservations r
               JOIN vehicles v ON v.id = r.vehicle_id
               LEFT JOIN customers c ON c.id = r.customer_id
              WHERE r.tenant_id = :t AND r.deleted_at IS NULL
                AND (r.reservation_code LIKE ?q? OR r.lead_name LIKE ?q? OR r.lead_phone LIKE ?q? OR c.first_name LIKE ?q? OR c.last_name LIKE ?q?)
              ORDER BY r.created_at DESC LIMIT $LIM",
            $like, 5, ['t' => $tid]
        );
        $groups[] = ['label'=>'Reservas','icon'=>'calendar-check','items'=>array_map(function ($r) {
            return [
                'title'    => $r['reservation_code'] . ' · ' . trim($r['brand'] . ' ' . $r['model']),
                'subtitle' => trim((($r['customer_name'] ?: ($r['lead_name'] ?: '—')) . ' · ' . status_label($r['status']))),
                'url'      => url('/admin/reservations/show/' . $r['id']),
            ];
        }, Database::select($sql, $p))];

        // --- Contracts ----------------------------------------------------
        [$sql, $p] = self::bindLike(
            "SELECT ct.id, ct.contract_number, ct.status,
                    v.brand, v.model,
                    TRIM(CONCAT(cu.first_name,' ',COALESCE(cu.last_name,''))) AS customer_name
               FROM contracts ct
               JOIN vehicles v ON v.id = ct.vehicle_id
               LEFT JOIN customers cu ON cu.id = ct.customer_id
              WHERE ct.tenant_id = :t AND ct.deleted_at IS NULL
                AND (ct.contract_number LIKE ?q? OR cu.first_name LIKE ?q? OR cu.last_name LIKE ?q?)
              ORDER BY ct.created_at DESC LIMIT $LIM",
            $like, 3, ['t' => $tid]
        );
        $groups[] = ['label'=>'Contratos','icon'=>'file-text','items'=>array_map(function ($c) {
            return [
                'title'    => $c['contract_number'] . ' · ' . trim($c['brand'] . ' ' . $c['model']),
                'subtitle' => trim((($c['customer_name'] ?: '—') . ' · ' . status_label($c['status']))),
                'url'      => url('/admin/contracts/show/' . $c['id']),
            ];
        }, Database::select($sql, $p))];

        // --- Drivers ------------------------------------------------------
        [$sql, $p] = self::bindLike(
            "SELECT id, first_name, last_name, license_number, phone
               FROM drivers
              WHERE tenant_id = :t AND deleted_at IS NULL
                AND (first_name LIKE ?q? OR last_name LIKE ?q? OR license_number LIKE ?q? OR phone LIKE ?q?)
              ORDER BY first_name LIMIT $LIM",
            $like, 4, ['t' => $tid]
        );
        $groups[] = ['label'=>'Choferes','icon'=>'id-card','items'=>array_map(function ($d) {
            return [
                'title'    => trim($d['first_name'] . ' ' . ($d['last_name'] ?? '')),
                'subtitle' => trim(($d['license_number'] ?: '') . ($d['phone'] ? ' · ' . $d['phone'] : '')),
                'url'      => url('/admin/drivers/show/' . $d['id']),
            ];
        }, Database::select($sql, $p))];

        // Keep only groups with at least one hit.
        $groups = array_values(array_filter($groups, fn($g) => !empty($g['items'])));

        $this->json(['query' => $q, 'groups' => $groups]);
    }
}
