<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Core\Auth;
use App\Models\Incident;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\ActivityLog;

class IncidentController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = ['status' => $request->str('status'), 'type' => $request->str('type')];
        $this->renderAdmin('admin/incidents/index', [
            'title'    => 'Incidencias · Kyros Rent Car',
            'active'   => 'incidents',
            'incidents'=> Incident::listForTenant($tid, $filters),
            'counts'   => Incident::statusCounts($tid),
            'filters'  => $filters,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Incidencias']],
        ]);
    }

    public function create(Request $request): void
    {
        $tid = $this->tenantId();
        $this->renderAdmin('admin/incidents/form', [
            'title'     => 'Nueva incidencia · Kyros Rent Car',
            'active'    => 'incidents',
            'customers' => Customer::listForTenant($tid),
            'vehicles'  => Vehicle::listForTenant($tid, ['status'=>'']),
            'contracts' => Database::select("SELECT id, contract_number FROM contracts WHERE tenant_id=:t AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 100", ['t'=>$tid]),
            'breadcrumbs' => [['label'=>'Incidencias','url'=>url('/admin/incidents')],['label'=>'Nueva']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        $data = $this->validateOrBack($request->all(), [
            'type'   => 'required|in:'.implode(',', Incident::TYPES),
            'amount' => 'numeric|min:0',
        ], '/admin/incidents/create');

        $id = Incident::create([
            'tenant_id'   => $tid,
            'contract_id' => $request->int('contract_id') ?: null,
            'customer_id' => $request->int('customer_id') ?: null,
            'vehicle_id'  => $request->int('vehicle_id') ?: null,
            'type'        => $data['type'],
            'description' => $request->str('description') ?: null,
            'amount'      => (float) ($data['amount'] ?? 0),
            'status'      => 'open',
            'created_by'  => Auth::id(),
        ]);
        ActivityLog::record('created','incidents',$id,'Incidencia '.$data['type']);
        Session::flash('success','Incidencia registrada.');
        $this->redirect('/admin/incidents');
    }

    public function show(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $incident = Incident::findOrFail((int) $id, $tid);
        $incident['customer'] = $incident['customer_id'] ? Customer::find((int) $incident['customer_id'], $tid) : null;
        $incident['vehicle']  = $incident['vehicle_id']  ? Vehicle::find((int) $incident['vehicle_id'], $tid)  : null;
        $incident['contract'] = $incident['contract_id'] ? Database::selectOne("SELECT id, contract_number, status, total_amount FROM contracts WHERE id=:id AND tenant_id=:t", ['id'=>(int)$incident['contract_id'],'t'=>$tid]) : null;
        $incident['created_by_name'] = $incident['created_by'] ? Database::scalar("SELECT name FROM users WHERE id = :id", ['id'=>(int)$incident['created_by']]) : null;

        $this->renderAdmin('admin/incidents/show', [
            'title'    => 'Incidencia #' . $incident['id'] . ' · Kyros Rent Car',
            'active'   => 'incidents',
            'incident' => $incident,
            'breadcrumbs' => [['label'=>'Incidencias','url'=>url('/admin/incidents')],['label'=>'#' . $incident['id']]],
        ]);
    }

    public function changeStatus(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Incident::findOrFail((int) $id, $tid);
        $status = $request->str('status');
        if (!in_array($status, Incident::STATUSES, true)) { Session::flash('error','Estado invalido.'); $this->redirect('/admin/incidents'); }
        Incident::update((int) $id, $tid, ['status' => $status]);
        ActivityLog::record('change_status','incidents',(int)$id,'Incidencia → '.$status);
        Session::flash('success','Estado actualizado.');
        $this->back();
    }
}
