<?php
namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Models\Plan;

class PlanController extends Controller
{
    public function index(Request $request): void
    {
        $plans = Plan::all(null, 'price_monthly ASC');
        foreach ($plans as &$p) {
            $p['tenants_count'] = (int) Database::scalar(
                "SELECT COUNT(*) FROM tenants WHERE plan_id = :id AND deleted_at IS NULL",
                ['id' => $p['id']]
            );
            $p['features_list'] = $p['features'] ? (json_decode($p['features'], true) ?: []) : [];
        }
        unset($p);

        $this->view('superadmin/plans', [
            'title'  => 'Planes · Super Admin',
            'panel'  => 'super',
            'active' => 'plans',
            'plans'  => $plans,
        ], 'app');
    }
}
