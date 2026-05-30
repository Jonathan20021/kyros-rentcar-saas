<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Config;
use App\Models\ApiKey;
use App\Models\ActivityLog;

/**
 * Tenant-facing API key management. The raw token is flashed once after
 * creation; thereafter only the masked prefix and metadata are shown.
 */
class ApiController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        // The API lives at the project root (sibling of /public), so strip a
        // trailing "/public" from the configured app URL before appending.
        $root = preg_replace('#/public/?$#', '', rtrim(Config::get('app.url'), '/'));
        $base = $root . '/api/v1';
        $this->renderAdmin('admin/api/index', [
            'title'   => 'API & Integraciones · Kyros Rent Car',
            'active'  => 'api',
            'keys'    => ApiKey::listForTenant($tid),
            'apiBase' => $base,
            'newToken' => Session::pull('api_new_token'),
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'API']],
        ]);
    }

    public function store(Request $request): void
    {
        if (!can('api.manage')) { $this->abort(403); }
        $tid  = $this->tenantId();
        $name = trim($request->str('name'));
        if ($name === '') { $name = 'Clave API'; }
        if (ApiKey::activeCount($tid) >= 10) {
            Session::flash('error', 'Alcanzaste el máximo de 10 claves activas. Revoca alguna primero.');
            $this->redirect('/admin/api');
        }
        [$id, $raw] = ApiKey::issue($tid, mb_substr($name, 0, 120));
        ActivityLog::record('created', 'api_keys', $id, 'Generó clave API: ' . $name);
        Session::set('api_new_token', $raw);
        Session::flash('success', 'Clave creada. Cópiala ahora — no se volverá a mostrar.');
        $this->redirect('/admin/api');
    }

    public function revoke(Request $request, string $id): void
    {
        if (!can('api.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        ApiKey::findOrFail((int) $id, $tid);
        ApiKey::revoke((int) $id, $tid);
        ActivityLog::record('revoked', 'api_keys', (int) $id, 'Revocó clave API');
        Session::flash('success', 'Clave revocada.');
        $this->redirect('/admin/api');
    }
}
