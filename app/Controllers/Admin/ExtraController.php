<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\Extra;
use App\Models\ActivityLog;

class ExtraController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $editId = $request->int('edit');
        $this->renderAdmin('admin/extras/index', [
            'title'   => 'Servicios adicionales · Kyros Rent Car',
            'active'  => 'extras',
            'extras'  => Extra::listWithUsage($tid),
            'editing' => $editId ? Extra::find($editId, $tid) : null,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Servicios']],
        ]);
    }

    public function store(Request $request): void
    {
        if (!can('catalog.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        $data = $this->validated($request);
        Extra::create(array_merge(['tenant_id' => $tid], $data));
        ActivityLog::record('created', 'extras', null, 'Servicio: ' . $data['name']);
        Session::flash('success', 'Servicio creado.');
        $this->redirect('/admin/extras');
    }

    public function update(Request $request, string $id): void
    {
        if (!can('catalog.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        Extra::findOrFail((int) $id, $tid);
        Extra::update((int) $id, $tid, $this->validated($request));
        ActivityLog::record('updated', 'extras', (int) $id, 'Servicio actualizado');
        Session::flash('success', 'Servicio actualizado.');
        $this->redirect('/admin/extras');
    }

    public function destroy(Request $request, string $id): void
    {
        if (!can('catalog.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        Extra::findOrFail((int) $id, $tid);
        Extra::delete((int) $id, $tid); // FK sets reservation_extras.extra_id NULL
        ActivityLog::record('deleted', 'extras', (int) $id, 'Servicio eliminado');
        Session::flash('success', 'Servicio eliminado.');
        $this->redirect('/admin/extras');
    }

    protected function validated(Request $request): array
    {
        $v = $this->validateOrBack($request->all(), [
            'name'        => 'required|max:120',
            'price'       => 'required|numeric|min:0',
            'charge_type' => 'required|in:per_day,per_reservation,one_time',
            'status'      => 'required|in:active,inactive',
        ], '/admin/extras');
        return [
            'name'        => $v['name'],
            'description' => $request->str('description') ?: null,
            'price'       => (float) $v['price'],
            'charge_type' => $v['charge_type'],
            'status'      => $v['status'],
        ];
    }
}
