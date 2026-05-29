<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Models\Plan;
use App\Models\Tenant;

class UpgradeController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $tenant = Tenant::withPlan($tid);
        $feature = $request->str('feature');
        $required = $feature ? Plan::labelFor($feature) : null;

        [$vCount, $vMax]   = Plan::usage($tid, 'vehicles');
        [$uCount, $uMax]   = Plan::usage($tid, 'users');

        $this->renderAdmin('admin/upgrade/index', [
            'title'   => 'Tu plan · Kyros',
            'active'  => '',
            'tenant'  => $tenant,
            'plans'   => Plan::publicPlans(),
            'feature' => $feature,
            'required'=> $required,
            'vehiclesCount' => $vCount, 'vehiclesMax' => $vMax,
            'usersCount'    => $uCount, 'usersMax'    => $uMax,
            'breadcrumbs'   => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Tu plan']],
        ]);
    }
}
