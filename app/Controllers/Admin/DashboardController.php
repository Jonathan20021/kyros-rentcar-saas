<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Database;
use App\Models\Vehicle;
use App\Models\Reservation;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Expense;

class DashboardController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $locId = $request->int('location_id') ?: null;
        $vehStatus = Vehicle::statusCounts($tid, $locId);

        $stats = [
            'reservations_today' => Reservation::todayCount($tid),
            'available'          => $vehStatus['available'],
            'rented'             => $vehStatus['rented'],
            'reserved'           => $vehStatus['reserved'],
            'maintenance'        => $vehStatus['maintenance'],
            'income_month'       => Payment::incomeThisMonth($tid),
            'expense_month'      => Expense::totalThisMonth($tid),
            'payments_pending'   => Payment::pendingCount($tid),
            'contracts_active'   => Contract::activeCount($tid),
            'customers_new'      => Customer::newThisMonth($tid),
            'balance_due'        => Contract::pendingBalance($tid),
        ];

        // Vehicles overdue for return (end date passed, still active)
        $overdue = Database::select(
            "SELECT ct.contract_number, ct.end_datetime, v.brand, v.model, v.plate_number,
                    CONCAT(c.first_name,' ',c.last_name) AS customer_name
               FROM contracts ct
               JOIN vehicles v ON v.id = ct.vehicle_id
               JOIN customers c ON c.id = ct.customer_id
              WHERE ct.tenant_id = :t AND ct.deleted_at IS NULL
                AND ct.status IN ('active','overdue') AND ct.end_datetime < NOW()
              ORDER BY ct.end_datetime ASC LIMIT 8",
            ['t' => $tid]
        );

        // License expiry alerts — customers + drivers (within 30 days or already expired)
        $customerLicAlerts = Database::select(
            "SELECT id, first_name, last_name, license_number, license_expiration
               FROM customers
              WHERE tenant_id = :t AND deleted_at IS NULL AND status != 'blocked'
                AND license_expiration IS NOT NULL
                AND license_expiration <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              ORDER BY license_expiration ASC LIMIT 6",
            ['t' => $tid]
        );
        $driverLicAlerts = Database::select(
            "SELECT id, first_name, last_name, license_number, license_expiration
               FROM drivers
              WHERE tenant_id = :t AND deleted_at IS NULL AND status != 'inactive'
                AND license_expiration IS NOT NULL
                AND license_expiration <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              ORDER BY license_expiration ASC LIMIT 6",
            ['t' => $tid]
        );

        // Document alerts (insurance/marbete expiring within 30 days or expired)
        $docAlerts = Database::select(
            "SELECT id, brand, model, plate_number,
                    LEAST(
                      COALESCE(insurance_expires,'9999-12-31'),
                      COALESCE(marbete_expires,'9999-12-31'),
                      COALESCE(plate_expires,'9999-12-31'),
                      COALESCE(inspection_expires,'9999-12-31')
                    ) AS nearest,
                    insurance_expires, marbete_expires, plate_expires, inspection_expires
               FROM vehicles
              WHERE tenant_id = :t AND deleted_at IS NULL
                AND (
                  insurance_expires <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) OR
                  marbete_expires   <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) OR
                  plate_expires     <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) OR
                  inspection_expires<= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                )
              ORDER BY nearest ASC LIMIT 8",
            ['t' => $tid]
        );

        // Week-over-week trends (honest deltas from real data)
        $resThisWeek = (int) Database::scalar("SELECT COUNT(*) FROM reservations WHERE tenant_id=:t AND deleted_at IS NULL AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)", ['t'=>$tid]);
        $resLastWeek = (int) Database::scalar("SELECT COUNT(*) FROM reservations WHERE tenant_id=:t AND deleted_at IS NULL AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND created_at < DATE_SUB(CURDATE(), INTERVAL 7 DAY)", ['t'=>$tid]);
        $incThisMonth = $stats['income_month'];
        $incLastMonth = (float) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM payments WHERE tenant_id=:t AND status='paid' AND payment_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH),'%Y-%m-01') AND payment_date < DATE_FORMAT(CURDATE(),'%Y-%m-01')", ['t'=>$tid]);
        $pct = fn($cur,$prev) => $prev > 0 ? round((($cur-$prev)/$prev)*100,2) : ($cur>0 ? 100.0 : 0.0);
        $trends = [
            'revenue'  => $pct($incThisMonth, $incLastMonth),
            'bookings' => $pct($resThisWeek, $resLastWeek),
        ];

        // Car types distribution (for progress widget) — branch-aware
        $totalVehSql = "SELECT COUNT(*) FROM vehicles WHERE tenant_id=:t AND deleted_at IS NULL" . ($locId ? " AND location_id=:loc" : "");
        $totalVehParams = $locId ? ['t'=>$tid,'loc'=>$locId] : ['t'=>$tid];
        $totalVeh = max(1, (int) Database::scalar($totalVehSql, $totalVehParams));
        $carTypes = Database::select(
            "SELECT c.name, COUNT(v.id) AS cnt
               FROM vehicle_categories c
               LEFT JOIN vehicles v ON v.category_id = c.id AND v.deleted_at IS NULL" . ($locId ? " AND v.location_id=:loc" : "") . "
              WHERE c.tenant_id = :t GROUP BY c.id, c.name HAVING cnt > 0 ORDER BY cnt DESC LIMIT 6",
            $locId ? ['t'=>$tid,'loc'=>$locId] : ['t'=>$tid]
        );
        foreach ($carTypes as &$ct) { $ct['pct'] = (int) round(($ct['cnt'] / $totalVeh) * 100); }
        unset($ct);

        // Per-branch fleet snapshot (only relevant when the tenant uses branches)
        $branches = Database::select(
            "SELECT l.id, l.name,
                    COUNT(v.id) AS total,
                    SUM(v.status = 'available') AS available,
                    SUM(v.status IN ('rented','reserved')) AS busy
               FROM locations l
               LEFT JOIN vehicles v ON v.location_id = l.id AND v.deleted_at IS NULL
              WHERE l.tenant_id = :t AND l.deleted_at IS NULL AND l.status = 'active'
              GROUP BY l.id, l.name
              ORDER BY total DESC, l.name LIMIT 6",
            ['t' => $tid]
        );

        $this->renderAdmin('admin/dashboard', [
            'title'         => 'Dashboard · ' . ($this->tenantName()),
            'active'        => 'dashboard',
            'stats'         => $stats,
            'trends'        => $trends,
            'totalVeh'      => $totalVeh,
            'carTypes'      => $carTypes,
            'branches'      => $branches,
            'branchOptions' => \App\Models\Location::activeForTenant($tid),
            'selectedLoc'   => $locId,
            'vehStatus'     => $vehStatus,
            'overdue'       => $overdue,
            'docAlerts'     => $docAlerts,
            'customerLicAlerts' => $customerLicAlerts,
            'driverLicAlerts'   => $driverLicAlerts,
            'upcoming'      => Reservation::upcomingReturns($tid, 7),
            'reservationStatus' => Reservation::statusCounts($tid),
            'monthlyIncome' => Payment::monthlyIncome($tid),
            'monthlyExpenses' => Expense::monthly($tid),
        ]);
    }

    protected function tenantName(): string
    {
        $t = \App\Models\Tenant::find($this->tenantId(), null);
        return $t['name'] ?? 'Kyros Rent Car';
    }

    /** JSON endpoint for dashboard charts (used by Fetch). */
    public function charts(Request $request): void
    {
        $tid = $this->tenantId();
        $this->json([
            'monthly_income'     => Payment::monthlyIncome($tid),
            'vehicle_status'     => Vehicle::statusCounts($tid),
            'reservation_status' => Reservation::statusCounts($tid),
        ]);
    }
}
