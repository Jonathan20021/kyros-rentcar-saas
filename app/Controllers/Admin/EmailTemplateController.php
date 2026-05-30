<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\EmailTemplate;
use App\Models\Tenant;
use App\Models\ActivityLog;
use App\Services\Mailer;

class EmailTemplateController extends AdminController
{
    public function index(Request $request): void
    {
        $this->renderAdmin('admin/emails/index', [
            'title'     => 'Plantillas de correo · Kyros Rent Car',
            'active'    => 'emails',
            'templates' => EmailTemplate::listForTenant($this->tenantId()),
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Plantillas de correo']],
        ]);
    }

    public function edit(Request $request, string $code): void
    {
        $tpl = EmailTemplate::get($this->tenantId(), $code);
        if (!$tpl) { $this->abort(404); }
        $this->renderAdmin('admin/emails/form', [
            'title'    => 'Editar plantilla · Kyros Rent Car',
            'active'   => 'emails',
            'tpl'      => $tpl,
            'breadcrumbs' => [['label'=>'Plantillas','url'=>url('/admin/emails')],['label'=>$tpl['label']]],
        ]);
    }

    public function update(Request $request, string $code): void
    {
        if (!can('settings.edit')) { $this->abort(403); }
        $tid = $this->tenantId();
        if (!EmailTemplate::get($tid, $code)) { $this->abort(404); }
        $subject = trim($request->str('subject'));
        $body    = (string) $request->input('body', '');
        $status  = $request->str('status') === 'inactive' ? 'inactive' : 'active';
        if ($subject === '') {
            Session::flash('error', 'El asunto es obligatorio.');
            $this->redirect('/admin/emails/edit/' . $code);
        }
        EmailTemplate::save($tid, $code, mb_substr($subject, 0, 200), $body, $status);
        ActivityLog::record('updated', 'email_templates', null, 'Plantilla: ' . $code);
        Session::flash('success', 'Plantilla guardada.');
        $this->redirect('/admin/emails/edit/' . $code);
    }

    public function reset(Request $request, string $code): void
    {
        if (!can('settings.edit')) { $this->abort(403); }
        EmailTemplate::reset($this->tenantId(), $code);
        ActivityLog::record('updated', 'email_templates', null, 'Plantilla restablecida: ' . $code);
        Session::flash('success', 'Plantilla restablecida a los valores por defecto.');
        $this->redirect('/admin/emails/edit/' . $code);
    }

    public function test(Request $request, string $code): void
    {
        if (!can('settings.edit')) { $this->abort(403); }
        $tid    = $this->tenantId();
        $tenant = Tenant::find($tid, null);
        $tpl    = EmailTemplate::get($tid, $code);
        if (!$tpl) { $this->abort(404); }
        $to = $request->str('to') ?: ($tenant['email'] ?? '');
        if ($to === '') {
            Session::flash('error', 'Indica un correo de prueba.');
            $this->redirect('/admin/emails/edit/' . $code);
        }
        // Sample values for every known variable.
        $sample = [];
        foreach ($tpl['vars'] as $v) { $sample[$v] = '[' . $v . ']'; }
        $sample = array_merge($sample, [
            'tenant'=>$tenant['name'] ?? 'Tu empresa', 'customer'=>'Cliente Demo', 'name'=>'Usuario Demo',
            'vehicle'=>'Toyota Corolla 2023', 'code'=>'RSV-2026-0001', 'start'=>'01/06/2026', 'end'=>'05/06/2026',
            'total'=>money(18880), 'balance'=>money(5000), 'amount'=>money(10000), 'method'=>'Tarjeta',
            'date'=>date('d/m/Y'), 'email'=>$to, 'password'=>'••••••••',
        ]);
        $ok = Mailer::fromTemplate($code, $to, $tenant ?? [], $sample);
        Session::flash($ok ? 'success' : 'warning',
            $ok ? 'Correo de prueba enviado a ' . $to . '.' : 'No se envió (correo deshabilitado/plantilla inactiva). Revisa la config en Super Admin.');
        $this->redirect('/admin/emails/edit/' . $code);
    }
}
