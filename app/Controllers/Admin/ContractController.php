<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Contract;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\ActivityLog;

class ContractController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = ['status' => $request->str('status')];
        $this->renderAdmin('admin/contracts/index', [
            'title'     => 'Contratos · Kyros',
            'active'    => 'contracts',
            'contracts' => Contract::listForTenant($tid, $filters),
            'filters'   => $filters,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Contratos']],
        ]);
    }

    protected function load(int $id): array
    {
        $tid = $this->tenantId();
        $c = Contract::findOrFail($id, $tid);
        $c['vehicle']  = Vehicle::find((int) $c['vehicle_id'], $tid);
        $c['customer'] = Customer::find((int) $c['customer_id'], $tid);
        $c['tenant']   = Tenant::find($tid, null);
        $c['payments'] = Database::select("SELECT * FROM payments WHERE tenant_id=:t AND contract_id=:c ORDER BY payment_date DESC, id DESC", ['t'=>$tid,'c'=>$id]);
        $photos = Database::select("SELECT phase, path FROM contract_photos WHERE tenant_id=:t AND contract_id=:c ORDER BY id ASC", ['t'=>$tid,'c'=>$id]);
        $c['photos_delivery'] = array_values(array_filter($photos, fn($p)=>$p['phase']==='delivery'));
        $c['photos_return']   = array_values(array_filter($photos, fn($p)=>$p['phase']==='return'));
        return $c;
    }

    /** Store a canvas signature (base64 PNG) on the contract. */
    public function sign(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Contract::findOrFail((int) $id, $tid);
        $dataUrl = (string) $request->input('signature', '');
        $path = \App\Services\FileUploader::saveDataUrlPng($dataUrl, 'signatures');
        if (!$path) { Session::flash('error', 'Firma inválida. Inténtalo de nuevo.'); $this->redirect('/admin/contracts/show/'.$id); }
        Contract::update((int) $id, $tid, ['customer_signature' => $path]);
        ActivityLog::record('updated', 'contracts', (int) $id, 'Firma del cliente registrada');
        Session::flash('success', 'Firma guardada.');
        $this->redirect('/admin/contracts/show/'.$id);
    }

    protected function savePhotos(int $tid, int $contractId, string $field, string $phase): void
    {
        foreach (\App\Services\FileUploader::imagesFromField($field, 'contracts') as $p) {
            Database::execute(
                "INSERT INTO contract_photos (tenant_id, contract_id, phase, path) VALUES (:t,:c,:ph,:p)",
                ['t'=>$tid,'c'=>$contractId,'ph'=>$phase,'p'=>$p]
            );
        }
    }

    public function show(Request $request, string $id): void
    {
        $c = $this->load((int) $id);
        $this->renderAdmin('admin/contracts/show', [
            'title'  => 'Contrato '.$c['contract_number'],
            'active' => 'contracts',
            'c'      => $c,
            'breadcrumbs' => [['label'=>'Contratos','url'=>url('/admin/contracts')],['label'=>$c['contract_number']]],
        ]);
    }

    public function pdf(Request $request, string $id): void
    {
        $c = $this->load((int) $id);
        $this->view('admin/contracts/pdf', [
            'title'   => 'Contrato '.$c['contract_number'],
            'c'       => $c,
            'backUrl' => url('/admin/contracts/show/'.$id),
        ], 'print');
    }

    public function closeForm(Request $request, string $id): void
    {
        $c = $this->load((int) $id);
        if ($c['status'] === 'finished') { Session::flash('warning','El contrato ya esta cerrado.'); $this->redirect('/admin/contracts/show/'.$id); }
        $this->renderAdmin('admin/contracts/close', [
            'title'  => 'Cerrar contrato '.$c['contract_number'],
            'active' => 'contracts',
            'c'      => $c,
            'breadcrumbs' => [['label'=>'Contratos','url'=>url('/admin/contracts')],['label'=>$c['contract_number'],'url'=>url('/admin/contracts/show/'.$id)],['label'=>'Devolucion']],
        ]);
    }

    /** Process the return: end mileage/fuel, penalties, set finished + free the vehicle. */
    public function close(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $c = Contract::findOrFail((int) $id, $tid);
        $penalties = $request->float('penalties_total');
        $newTotal  = (float) $c['total_amount'] + $penalties;
        $balance   = max(0, $newTotal - (float) $c['paid_amount']);

        Contract::update((int) $id, $tid, [
            'actual_return_datetime' => date('Y-m-d H:i:s'),
            'end_mileage'    => $request->int('end_mileage') ?: null,
            'end_fuel_level' => $request->int('end_fuel_level'),
            'penalties_total'=> $penalties,
            'total_amount'   => $newTotal,
            'balance_due'    => $balance,
            'status'         => 'finished',
        ]);

        // Vehicle goes to cleaning if it needs it, else available.
        $newVehStatus = $request->str('vehicle_status', 'cleaning');
        Vehicle::update((int) $c['vehicle_id'], $tid, ['status' => $newVehStatus, 'mileage' => $request->int('end_mileage') ?: null]);

        // Return photos
        $this->savePhotos($tid, (int) $id, 'return_photos', 'return');

        // Optional damage incident
        if ($request->str('damage_note')) {
            Database::execute(
                "INSERT INTO incidents (tenant_id,contract_id,customer_id,vehicle_id,type,description,amount,status,created_by)
                 VALUES (:t,:c,:cu,:v,'exterior_damage',:d,:a,'open',:by)",
                ['t'=>$tid,'c'=>$id,'cu'=>$c['customer_id'],'v'=>$c['vehicle_id'],'d'=>$request->str('damage_note'),'a'=>$penalties,'by'=>\App\Core\Auth::id()]
            );
        }

        ActivityLog::record('updated','contracts',(int)$id,'Contrato '.$c['contract_number'].' cerrado (devolucion)');
        Session::flash('success', $balance > 0 ? 'Contrato cerrado. Balance pendiente: '.money($balance) : 'Contrato cerrado y saldado.');
        $this->redirect('/admin/contracts/show/'.$id);
    }
}
