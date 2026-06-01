<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Tenant;
use App\Models\Notification;

/**
 * Base for tenant-admin controllers. Injects tenant + notification context
 * into every view rendered with renderAdmin().
 */
abstract class AdminController extends Controller
{
    protected function tenantId(): int
    {
        return (int) Auth::tenantId();
    }

    protected function renderAdmin(string $view, array $data = []): void
    {
        $tid = $this->tenantId();
        $data['panel']  = 'admin';
        $tenant = Tenant::withPlan($tid);
        // No tenant row — likely a demo that was swept while the session was open,
        // or a deleted/suspended tenant. Force logout + redirect.
        if (!$tenant) {
            \App\Core\Auth::logout();
            \App\Core\Session::flash('warning', 'Tu sesión expiró o tu empresa ya no está disponible.');
            $this->redirect('/login');
        }
        // Demo expired mid-session? Force logout + sweep so nothing leaks.
        if (!empty($tenant['is_demo']) && !empty($tenant['demo_expires_at'])
            && strtotime($tenant['demo_expires_at']) <= time()) {
            \App\Services\DemoService::sweep();
            \App\Core\Auth::logout();
            \App\Core\Session::flash('warning', 'Tu demo expiró. Los datos fueron eliminados.');
            $this->redirect('/login');
        }
        $data['tenant'] = $tenant;
        // Make money()/money_compact() format in this tenant's currency everywhere.
        \App\Services\LocaleService::setCurrentCurrency($tenant['currency'] ?? null);
        $data['notifications'] = Notification::forTenant($tid, 8);
        $data['notifications_unread'] = Notification::unreadCount($tid);
        $this->view($view, $data, 'app');
    }
}
