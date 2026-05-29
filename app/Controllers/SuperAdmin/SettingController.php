<?php
namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Auth;
use App\Models\Setting;
use App\Models\ActivityLog;
use App\Services\Mailer;

class SettingController extends Controller
{
    public function index(Request $request): void
    {
        $s = Setting::allPlatform();
        $this->view('superadmin/settings', [
            'title'  => 'Configuración · Super Admin',
            'panel'  => 'super',
            'active' => 'settings',
            's'      => $s,
            'hasKey' => !empty($s['resend_api_key']),
        ], 'app');
    }

    public function update(Request $request): void
    {
        Setting::setPlatform('mail_enabled', $request->input('mail_enabled') ? '1' : '0');
        Setting::setPlatform('mail_from_email', $request->str('mail_from_email') ?: 'onboarding@resend.dev');
        Setting::setPlatform('mail_from_name', $request->str('mail_from_name') ?: 'Kyros Rent Car');
        // Only overwrite the key when a new value is provided (field left blank keeps current).
        $key = $request->str('resend_api_key');
        if ($key !== '') {
            Setting::setPlatform('resend_api_key', $key);
        }
        ActivityLog::record('updated', 'settings', null, 'Configuración de correo (Resend) actualizada');
        Session::flash('success', 'Configuración guardada.');
        $this->redirect('/super-admin/settings');
    }

    public function test(Request $request): void
    {
        $to = Auth::user()['email'] ?? '';
        if (!$to) { Session::flash('error', 'No se encontró tu correo.'); $this->redirect('/super-admin/settings'); }
        [$ok, $msg] = Mailer::test($to);
        Session::flash($ok ? 'success' : 'error', $msg);
        $this->redirect('/super-admin/settings');
    }
}
