<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Location;
use App\Models\ActivityLog;

class LocationController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $this->renderAdmin('admin/locations/index', [
            'title'     => 'Sucursales · Kyros',
            'active'    => 'locations',
            'locations' => Location::listWithCounts($tid),
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Sucursales']],
        ]);
    }

    public function create(Request $request): void
    {
        $this->form(null);
    }

    public function edit(Request $request, string $id): void
    {
        $location = Location::findOrFail((int) $id, $this->tenantId());
        $this->form($location);
    }

    protected function form(?array $location): void
    {
        $this->renderAdmin('admin/locations/form', [
            'title'    => ($location ? 'Editar' : 'Nueva') . ' sucursal · Kyros',
            'active'   => 'locations',
            'location' => $location,
            'breadcrumbs' => [['label'=>'Sucursales','url'=>url('/admin/locations')],['label'=>$location ? 'Editar' : 'Nueva']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid  = $this->tenantId();
        $data = $this->validated($request, '/admin/locations/create');
        $id = Location::create(array_merge(['tenant_id' => $tid], $data));
        ActivityLog::record('created', 'locations', $id, 'Sucursal creada: ' . $data['name']);
        Session::flash('success', 'Sucursal creada.');
        $this->redirect('/admin/locations');
    }

    public function update(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Location::findOrFail((int) $id, $tid);
        $data = $this->validated($request, '/admin/locations/edit/' . $id);
        Location::update((int) $id, $tid, $data);
        ActivityLog::record('updated', 'locations', (int) $id, 'Sucursal actualizada');
        Session::flash('success', 'Sucursal actualizada.');
        $this->redirect('/admin/locations');
    }

    public function destroy(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Location::findOrFail((int) $id, $tid);
        // Soft-delete doesn't trigger the FK's SET NULL, so detach fleet/staff
        // explicitly to honor the "quedaron sin sucursal" promise.
        Database::execute("UPDATE vehicles SET location_id = NULL WHERE location_id = :l AND tenant_id = :t", ['l' => (int) $id, 't' => $tid]);
        Database::execute("UPDATE users SET location_id = NULL WHERE location_id = :l AND tenant_id = :t", ['l' => (int) $id, 't' => $tid]);
        Location::delete((int) $id, $tid);
        ActivityLog::record('deleted', 'locations', (int) $id, 'Sucursal eliminada');
        Session::flash('success', 'Sucursal eliminada. Los vehículos quedaron sin sucursal asignada.');
        $this->redirect('/admin/locations');
    }

    protected function validated(Request $request, string $back): array
    {
        $v = $this->validateOrBack($request->all(), [
            'name'    => 'required|max:120',
            'address' => 'max:255',
            'phone'   => 'max:30',
            'manager_name' => 'max:120',
            'status'  => 'required|in:active,inactive',
        ], $back);
        return [
            'name'         => $v['name'],
            'address'      => $request->str('address') ?: null,
            'phone'        => $request->str('phone') ?: null,
            'manager_name' => $request->str('manager_name') ?: null,
            'status'       => $v['status'],
        ];
    }
}
