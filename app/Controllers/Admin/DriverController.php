<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Driver;
use App\Models\ActivityLog;
use App\Services\FileUploader;

class DriverController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = [
            'status' => $request->str('status'),
            'search' => $request->str('search'),
        ];
        $this->renderAdmin('admin/drivers/index', [
            'title'        => 'Choferes · Kyros Rent Car',
            'active'       => 'drivers',
            'drivers'      => Driver::listForTenant($tid, $filters),
            'statusCounts' => Driver::statusCounts($tid),
            'filters'      => $filters,
            'breadcrumbs'  => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Choferes']],
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $driver = Driver::findOrFail((int) $id, $tid);
        $recentTrips = Database::select(
            "SELECT c.contract_number, c.start_datetime, c.end_datetime, c.status, c.total_amount,
                    v.brand, v.model, v.plate_number
               FROM contracts c
               JOIN vehicles v ON v.id = c.vehicle_id
              WHERE c.tenant_id=:t AND c.driver_id=:d AND c.deleted_at IS NULL
              ORDER BY c.start_datetime DESC LIMIT 10",
            ['t'=>$tid, 'd'=>$driver['id']]
        );
        $stats = [
            'trips'   => (int) Database::scalar("SELECT COUNT(*) FROM contracts WHERE tenant_id=:t AND driver_id=:d AND deleted_at IS NULL", ['t'=>$tid,'d'=>$driver['id']]),
            'earned'  => (float) Database::scalar("SELECT COALESCE(SUM(driver_cost),0) FROM contracts WHERE tenant_id=:t AND driver_id=:d AND deleted_at IS NULL", ['t'=>$tid,'d'=>$driver['id']]),
        ];
        $this->renderAdmin('admin/drivers/show', [
            'title'  => trim($driver['first_name'].' '.$driver['last_name']).' · Kyros Rent Car',
            'active' => 'drivers',
            'driver' => $driver,
            'recentTrips' => $recentTrips,
            'stats'  => $stats,
            'breadcrumbs' => [['label'=>'Choferes','url'=>url('/admin/drivers')],['label'=>trim($driver['first_name'].' '.$driver['last_name'])]],
        ]);
    }

    public function create(Request $request): void
    {
        $this->renderAdmin('admin/drivers/form', [
            'title'  => 'Nuevo chofer · Kyros Rent Car',
            'active' => 'drivers',
            'driver' => null,
            'breadcrumbs' => [['label'=>'Choferes','url'=>url('/admin/drivers')],['label'=>'Nuevo']],
        ]);
    }

    public function edit(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $driver = Driver::findOrFail((int) $id, $tid);
        $this->renderAdmin('admin/drivers/form', [
            'title'  => 'Editar chofer · Kyros Rent Car',
            'active' => 'drivers',
            'driver' => $driver,
            'breadcrumbs' => [['label'=>'Choferes','url'=>url('/admin/drivers')],['label'=>'Editar']],
        ]);
    }

    public function store(Request $request): void
    {
        if (!can('drivers.create')) { $this->abort(403); }
        $tid = $this->tenantId();
        $data = $this->validated($request, '/admin/drivers/create');
        $data['tenant_id'] = $tid;
        if ($f = $request->file('photo')) {
            if ($p = FileUploader::document($f, 'drivers')) $data['photo'] = $p;
        }
        $id = Driver::create($data);
        ActivityLog::record('created', 'drivers', $id, 'Chofer creado: ' . $data['first_name']);
        Session::flash('success', 'Chofer creado.');
        $this->redirect('/admin/drivers');
    }

    public function update(Request $request, string $id): void
    {
        if (!can('drivers.edit')) { $this->abort(403); }
        $tid = $this->tenantId();
        Driver::findOrFail((int) $id, $tid);
        $data = $this->validated($request, '/admin/drivers/edit/' . $id);
        unset($data['tenant_id']);
        if ($f = $request->file('photo')) {
            if ($p = FileUploader::document($f, 'drivers')) $data['photo'] = $p;
        }
        Driver::update((int) $id, $tid, $data);
        ActivityLog::record('updated', 'drivers', (int) $id, 'Chofer actualizado');
        Session::flash('success', 'Chofer actualizado.');
        $this->redirect('/admin/drivers');
    }

    public function destroy(Request $request, string $id): void
    {
        if (!can('drivers.delete')) { $this->abort(403); }
        $tid = $this->tenantId();
        Driver::findOrFail((int) $id, $tid);
        Driver::delete((int) $id, $tid);
        ActivityLog::record('deleted', 'drivers', (int) $id, 'Chofer eliminado');
        Session::flash('success', 'Chofer eliminado.');
        $this->redirect('/admin/drivers');
    }

    protected function validated(Request $request, string $back): array
    {
        $v = $this->validateOrBack($request->all(), [
            'first_name' => 'required|max:80',
            'status'     => 'required|in:active,vacation,inactive',
            'email'      => 'email|max:150',
        ], $back);
        return [
            'first_name'         => $v['first_name'],
            'last_name'          => $request->str('last_name') ?: null,
            'document_number'    => $request->str('document_number') ?: null,
            'license_number'     => $request->str('license_number') ?: null,
            'license_expiration' => $request->str('license_expiration') ?: null,
            'phone'              => $request->str('phone') ?: null,
            'email'              => $request->str('email') ? strtolower($request->str('email')) : null,
            'address'            => $request->str('address') ?: null,
            'daily_rate'         => (float) ($request->str('daily_rate') ?: 0),
            'hourly_rate'        => (float) ($request->str('hourly_rate') ?: 0),
            'notes'              => $request->str('notes') ?: null,
            'status'             => $v['status'],
        ];
    }
}
