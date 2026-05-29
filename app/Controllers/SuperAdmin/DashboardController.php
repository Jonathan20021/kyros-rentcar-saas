<?php
namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $stats = [
            'tenants_total'     => (int) Database::scalar("SELECT COUNT(*) FROM tenants WHERE deleted_at IS NULL"),
            'tenants_active'    => (int) Database::scalar("SELECT COUNT(*) FROM tenants WHERE status='active' AND deleted_at IS NULL"),
            'tenants_suspended' => (int) Database::scalar("SELECT COUNT(*) FROM tenants WHERE status='suspended' AND deleted_at IS NULL"),
            'tenants_trial'     => (int) Database::scalar("SELECT COUNT(*) FROM tenants WHERE status='trial' AND deleted_at IS NULL"),
            'vehicles_total'    => (int) Database::scalar("SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NULL"),
            'reservations_total'=> (int) Database::scalar("SELECT COUNT(*) FROM reservations WHERE deleted_at IS NULL"),
        ];

        // Estimated MRR from active subscriptions
        $stats['mrr'] = (float) Database::scalar(
            "SELECT COALESCE(SUM(p.price_monthly),0)
               FROM tenants t JOIN plans p ON p.id = t.plan_id
              WHERE t.status IN ('active','trial') AND t.deleted_at IS NULL"
        );

        // Tenants per plan (for chart)
        $perPlan = Database::select(
            "SELECT p.name, COUNT(t.id) AS c
               FROM plans p LEFT JOIN tenants t ON t.plan_id = p.id AND t.deleted_at IS NULL
              GROUP BY p.id, p.name ORDER BY p.price_monthly"
        );

        $recentTenants = Database::select(
            "SELECT t.*, p.name AS plan_name FROM tenants t
               LEFT JOIN plans p ON p.id = t.plan_id
              WHERE t.deleted_at IS NULL ORDER BY t.created_at DESC LIMIT 8"
        );

        $this->view('superadmin/dashboard', [
            'title'   => 'Super Admin · Kyros',
            'panel'   => 'super',
            'active'  => 'dashboard',
            'stats'   => $stats,
            'perPlan' => $perPlan,
            'recentTenants' => $recentTenants,
        ], 'app');
    }
}
