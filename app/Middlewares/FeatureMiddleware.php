<?php
namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Session;
use App\Models\Plan;

/**
 * Blocks access to a route when the tenant's plan does not include the
 * required feature key. Redirects to /admin/upgrade with the feature key.
 */
class FeatureMiddleware
{
    public function __construct(protected string $feature) {}

    public function handle(): void
    {
        $tid = (int) Auth::tenantId();
        if (!$tid) {
            header('Location: ' . url('/login'));
            exit;
        }
        $slug = Plan::slugForTenant($tid);
        if (!Plan::planHas($slug, $this->feature)) {
            Session::flash('warning', 'Esta función requiere el plan ' . Plan::labelFor($this->feature) . '.');
            header('Location: ' . url('/admin/upgrade?feature=' . urlencode($this->feature)));
            exit;
        }
    }
}
