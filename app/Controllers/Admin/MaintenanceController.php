<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\ActivityLog;

class MaintenanceController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = ['status' => $request->str('status')];
        $this->renderAdmin('admin/maintenance/index', [
            'title'   => 'Mantenimiento · Kyros Rent Car',
            'active'  => 'maintenance',
            'records' => Maintenance::listForTenant($tid, $filters),
            'filters' => $filters,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Mantenimiento']],
        ]);
    }

    public function create(Request $request): void
    {
        $tid = $this->tenantId();
        $this->renderAdmin('admin/maintenance/form', [
            'title'    => 'Nuevo mantenimiento · Kyros Rent Car',
            'active'   => 'maintenance',
            'record'   => null,
            'vehicles' => Vehicle::listForTenant($tid, ['status' => '']),
            'breadcrumbs' => [['label'=>'Mantenimiento','url'=>url('/admin/maintenance')],['label'=>'Nuevo']],
        ]);
    }

    public function edit(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $record = Maintenance::findOrFail((int) $id, $tid);
        $this->renderAdmin('admin/maintenance/form', [
            'title'    => 'Editar mantenimiento · Kyros Rent Car',
            'active'   => 'maintenance',
            'record'   => $record,
            'vehicles' => Vehicle::listForTenant($tid, ['status' => '']),
            'breadcrumbs' => [['label'=>'Mantenimiento','url'=>url('/admin/maintenance')],['label'=>'Editar']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        $data = $this->validated($request, '/admin/maintenance/create');
        $vehicle = Vehicle::find((int) $data['vehicle_id'], $tid);
        if (!$vehicle) { Session::flash('error','Vehiculo invalido.'); $this->redirect('/admin/maintenance/create'); }

        $id = Maintenance::create(array_merge(['tenant_id' => $tid], $data));
        $this->syncVehicleStatus($tid, (int) $data['vehicle_id'], $data['status'], $vehicle['status']);

        ActivityLog::record('created','maintenance',$id,'Mantenimiento '.$data['maintenance_type'].' '.$vehicle['brand'].' '.$vehicle['model']);
        Session::flash('success','Registro de mantenimiento creado.');
        $this->redirect('/admin/maintenance');
    }

    public function update(Request $request, string $id): void
    {
        if (!can('maintenance.edit')) { $this->abort(403); }
        $tid = $this->tenantId();
        $existing = Maintenance::findOrFail((int) $id, $tid);
        $data = $this->validated($request, '/admin/maintenance/edit/' . $id);
        $vehicle = Vehicle::find((int) $data['vehicle_id'], $tid);
        if (!$vehicle) { Session::flash('error','Vehiculo invalido.'); $this->redirect('/admin/maintenance/edit/' . $id); }

        Maintenance::update((int) $id, $tid, $data);
        $this->syncVehicleStatus($tid, (int) $data['vehicle_id'], $data['status'], $vehicle['status']);

        ActivityLog::record('updated','maintenance',(int)$id,'Mantenimiento actualizado');
        Session::flash('success','Mantenimiento actualizado.');
        $this->redirect('/admin/maintenance');
    }

    /** Quick action: mark a maintenance record completed (releases the vehicle). */
    public function complete(Request $request, string $id): void
    {
        if (!can('maintenance.edit')) { $this->abort(403); }
        $tid = $this->tenantId();
        $record = Maintenance::findOrFail((int) $id, $tid);
        if ($record['status'] === 'completed') {
            Session::flash('warning','Este registro ya está completado.');
            $this->redirect('/admin/maintenance');
        }
        Maintenance::update((int) $id, $tid, [
            'status'   => 'completed',
            'end_date' => $record['end_date'] ?: date('Y-m-d'),
        ]);
        // Free the vehicle if it was held for maintenance.
        $vehicle = Vehicle::find((int) $record['vehicle_id'], $tid);
        if ($vehicle && $vehicle['status'] === 'maintenance') {
            Vehicle::update((int) $record['vehicle_id'], $tid, ['status' => 'available']);
        }
        ActivityLog::record('updated','maintenance',(int)$id,'Mantenimiento completado');
        Session::flash('success','Mantenimiento marcado como completado.');
        $this->redirect('/admin/maintenance');
    }

    public function destroy(Request $request, string $id): void
    {
        if (!can('maintenance.delete')) { $this->abort(403); }
        $tid = $this->tenantId();
        $record = Maintenance::findOrFail((int) $id, $tid);
        Maintenance::delete((int) $id, $tid);
        // If we removed an "in_progress" record, free the vehicle.
        if ($record['status'] === 'in_progress') {
            $veh = Vehicle::find((int) $record['vehicle_id'], $tid);
            if ($veh && $veh['status'] === 'maintenance') {
                Vehicle::update((int) $record['vehicle_id'], $tid, ['status' => 'available']);
            }
        }
        ActivityLog::record('deleted','maintenance',(int)$id,'Mantenimiento eliminado');
        Session::flash('success','Registro de mantenimiento eliminado.');
        $this->redirect('/admin/maintenance');
    }

    protected function validated(Request $request, string $back): array
    {
        $v = $this->validateOrBack($request->all(), [
            'vehicle_id'       => 'required|integer',
            'maintenance_type' => 'required|in:oil,tires,brakes,battery,alignment,mechanical,deep_clean,paint,inspection,other',
            'status'           => 'required|in:scheduled,in_progress,completed,cancelled',
        ], $back);
        return [
            'vehicle_id'       => (int) $v['vehicle_id'],
            'maintenance_type' => $v['maintenance_type'],
            'description'      => $request->str('description') ?: null,
            'provider'         => $request->str('provider') ?: null,
            'cost'             => $request->float('cost'),
            'mileage'          => $request->int('mileage') ?: null,
            'start_date'       => $request->str('start_date') ?: date('Y-m-d'),
            'end_date'         => $request->str('end_date') ?: null,
            'next_due_date'    => $request->str('next_due_date') ?: null,
            'next_due_mileage' => $request->int('next_due_mileage') ?: null,
            'status'           => $v['status'],
            'notes'            => $request->str('notes') ?: null,
        ];
    }

    protected function syncVehicleStatus(int $tid, int $vehicleId, string $newStatus, string $currentVehicleStatus): void
    {
        if ($newStatus === 'in_progress') {
            Vehicle::update($vehicleId, $tid, ['status' => 'maintenance']);
        } elseif ($newStatus === 'completed' && $currentVehicleStatus === 'maintenance') {
            Vehicle::update($vehicleId, $tid, ['status' => 'available']);
        }
    }
}
