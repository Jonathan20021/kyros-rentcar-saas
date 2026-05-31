<?php
namespace App\Controllers\SuperAdmin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\Mailer;
use App\Services\NotificationService;

/**
 * Platform notification routing — who gets alerted on registrations,
 * demo redemptions, and login events.
 *
 * Settings consumed:
 *   notify_recipients_registration
 *   notify_recipients_demo
 *   notify_recipients_logins
 *   notify_logins_enabled
 *   notify_logins_filter
 */
class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $s = Setting::allPlatform();
        $this->view('superadmin/notifications/index', [
            'title'   => 'Notificaciones · Super Admin',
            'panel'   => 'super',
            'active'  => 'notifications',
            's'       => $s,
            'mailReady' => Mailer::enabled(),
        ], 'app');
    }

    public function update(Request $request): void
    {
        // Clean each list: trim, validate, dedupe, recombine with commas.
        $cleanList = function (string $raw): string {
            $parts = preg_split('/[\s,;]+/', $raw) ?: [];
            $out = [];
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p === '') continue;
                if (filter_var($p, FILTER_VALIDATE_EMAIL)) {
                    $out[$p] = true;
                }
            }
            return implode(', ', array_keys($out));
        };

        Setting::setPlatform('notify_recipients_registration', $cleanList((string) $request->input('notify_recipients_registration', '')));
        Setting::setPlatform('notify_recipients_demo',         $cleanList((string) $request->input('notify_recipients_demo', '')));
        Setting::setPlatform('notify_recipients_logins',       $cleanList((string) $request->input('notify_recipients_logins', '')));
        Setting::setPlatform('notify_logins_enabled',          $request->input('notify_logins_enabled') ? '1' : '0');

        $filter = (string) $request->input('notify_logins_filter', 'failed_only');
        if (!in_array($filter, ['all', 'super_only', 'failed_only'], true)) $filter = 'failed_only';
        Setting::setPlatform('notify_logins_filter', $filter);

        ActivityLog::record('updated', 'settings', null, 'Configuración de notificaciones actualizada');
        Session::flash('success', 'Configuración guardada.');
        $this->redirect('/super-admin/notifications');
    }

    /** Fire a sample of each enabled alert to the configured recipients. */
    public function test(Request $request): void
    {
        $u = Auth::user();
        $sent = [];
        try {
            NotificationService::notifyRegistration(
                ['name' => 'Empresa de Prueba', 'slug' => 'empresa-prueba', 'email' => $u['email'] ?? 'test@kyrosrd.com',
                 'phone' => '+1 809 555 0000', 'plan_name' => 'Starter', 'status' => 'pending_approval'],
                ['name' => $u['name'] ?? 'Prueba', 'email' => $u['email'] ?? 'test@kyrosrd.com']
            );
            $sent[] = 'Registro';
        } catch (\Throwable $e) {}

        try {
            NotificationService::notifyDemoCreated(
                ['name' => 'Demo de Prueba', 'slug' => 'demo-prueba',
                 'demo_expires_at' => date('Y-m-d H:i:s', time() + 5*3600)],
                ['name' => $u['name'] ?? 'Prueba', 'email' => $u['email'] ?? 'test@kyrosrd.com'],
                ['code' => 'TEST-DEMO', 'plan_name' => 'Premium', 'hours_valid' => 5]
            );
            $sent[] = 'Demo';
        } catch (\Throwable $e) {}

        try {
            // Force-enable logins for the test so the recipient list is read,
            // ignoring the master switch.
            $origEnabled = Setting::getPlatform('notify_logins_enabled', '0');
            Setting::setPlatform('notify_logins_enabled', '1');
            NotificationService::notifyLogin(
                ['name' => $u['name'] ?? 'Prueba', 'email' => $u['email'] ?? 'test@kyrosrd.com', 'tenant_id' => null],
                $request->ip(), $_SERVER['HTTP_USER_AGENT'] ?? null, true
            );
            Setting::setPlatform('notify_logins_enabled', $origEnabled);
            $sent[] = 'Login';
        } catch (\Throwable $e) {}

        if ($sent) {
            Session::flash('success', 'Correos de prueba disparados: ' . implode(', ', $sent) . '. Revisa las bandejas configuradas.');
        } else {
            Session::flash('error', 'No se pudieron disparar los correos de prueba.');
        }
        $this->redirect('/super-admin/notifications');
    }
}
