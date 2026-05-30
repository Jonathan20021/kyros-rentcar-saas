<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\Tenant;
use App\Models\ActivityLog;
use App\Services\FileUploader;

class SettingController extends AdminController
{
    public function index(Request $request): void
    {
        $this->renderAdmin('admin/settings/index', [
            'title'  => 'Configuracion · Kyros Rent Car',
            'active' => 'settings',
            'tenant' => Tenant::find($this->tenantId(), null),
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Configuracion']],
        ]);
    }

    public function update(Request $request): void
    {
        $tid = $this->tenantId();
        $data = $this->validateOrBack($request->all(), [
            'name'  => 'required|max:150',
            'email' => 'email|max:150',
            'primary_color'   => 'max:9',
            'secondary_color' => 'max:9',
            'tax_rate' => 'numeric|min:0|max:100',
        ], '/admin/settings');

        $payload = [
            'name'            => $data['name'],
            'legal_name'      => $request->str('legal_name') ?: null,
            'rnc'             => $request->str('rnc') ?: null,
            'email'           => $request->str('email') ? strtolower($request->str('email')) : null,
            'phone'           => $request->str('phone') ?: null,
            'whatsapp'        => $request->str('whatsapp') ?: null,
            'address'         => $request->str('address') ?: null,
            'description'     => $request->str('description') ?: null,
            'primary_color'   => $request->str('primary_color', '#4F46E5'),
            'secondary_color' => $request->str('secondary_color', '#06B6D4'),
            'currency'        => $request->str('currency', 'DOP'),
            'tax_rate'        => (float) $request->float('tax_rate', 18),
        ];

        if ($f = $request->file('logo')) {
            if ($p = FileUploader::image($f, 'branding')) $payload['logo'] = $p;
        }
        if ($f = $request->file('cover_image')) {
            if ($p = FileUploader::image($f, 'branding')) $payload['cover_image'] = $p;
        }

        Tenant::update($tid, null, $payload);
        ActivityLog::record('updated', 'settings', $tid, 'Configuracion actualizada');
        Session::flash('success', 'Configuracion guardada.');
        $this->redirect('/admin/settings');
    }
}
