<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Contract;
use App\Models\Tenant;
use App\Models\ActivityLog;

class InvoiceController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = ['status' => $request->str('status')];
        $this->renderAdmin('admin/invoices/index', [
            'title'    => 'Facturas · Kyros Rent Car',
            'active'   => 'invoices',
            'invoices' => Invoice::listForTenant($tid, $filters),
            'monthTotal' => Invoice::monthTotal($tid),
            'filters'  => $filters,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Facturas']],
        ]);
    }

    public function create(Request $request): void
    {
        $tid = $this->tenantId();
        $tenant = Tenant::find($tid, null);
        $prefill = null;
        $contractId = $request->int('contract_id');
        if ($contractId) {
            $ct = Database::selectOne(
                "SELECT ct.*, CONCAT(c.first_name,' ',c.last_name) AS customer_name, v.brand, v.model
                   FROM contracts ct JOIN customers c ON c.id=ct.customer_id JOIN vehicles v ON v.id=ct.vehicle_id
                  WHERE ct.id=:id AND ct.tenant_id=:t", ['id'=>$contractId,'t'=>$tid]
            );
            if ($ct) {
                $prefill = [
                    'customer_id' => (int) $ct['customer_id'],
                    'contract_id' => (int) $ct['id'],
                    'description' => 'Alquiler '.$ct['brand'].' '.$ct['model'].' · '.$ct['contract_number'],
                    'amount'      => (float) $ct['subtotal'],
                ];
            }
        }
        $this->renderAdmin('admin/invoices/form', [
            'title'     => 'Nueva factura · Kyros Rent Car',
            'active'    => 'invoices',
            'customers' => Customer::listForTenant($tid),
            'taxRate'   => (float) $tenant['tax_rate'],
            'prefill'   => $prefill,
            'breadcrumbs' => [['label'=>'Facturas','url'=>url('/admin/invoices')],['label'=>'Nueva']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        $tenant = Tenant::find($tid, null);
        $back = '/admin/invoices/create';

        $items = [];
        foreach ((array) $request->input('items', []) as $row) {
            $desc = trim((string)($row['description'] ?? ''));
            $qty  = (float) ($row['quantity'] ?? 0);
            $price= (float) ($row['unit_price'] ?? 0);
            if ($desc === '' || $qty <= 0) continue;
            $items[] = ['description'=>$desc,'quantity'=>$qty,'unit_price'=>$price,'line_total'=>round($qty*$price,2)];
        }
        if (empty($items)) {
            Session::flash('error','Agrega al menos un concepto a la factura.');
            $this->redirect($back);
        }

        $subtotal = array_sum(array_column($items, 'line_total'));
        $discount = $request->float('discount_amount');
        $taxBase  = max(0, $subtotal - $discount);
        $tax      = round($taxBase * ((float)$tenant['tax_rate']/100), 2);
        $total    = round($taxBase + $tax, 2);

        try {
            Database::beginTransaction();
            $number = Invoice::nextNumber($tid);
            $invId = Invoice::create([
                'tenant_id'=>$tid,'invoice_number'=>$number,
                'customer_id'=>$request->int('customer_id') ?: null,
                'contract_id'=>$request->int('contract_id') ?: null,
                'subtotal'=>$subtotal,'tax_amount'=>$tax,'discount_amount'=>$discount,'total'=>$total,
                'status'=>'issued','issue_date'=>$request->str('issue_date') ?: date('Y-m-d'),
                'due_date'=>$request->str('due_date') ?: null,
            ]);
            foreach ($items as $it) {
                Database::execute(
                    "INSERT INTO invoice_items (tenant_id,invoice_id,description,quantity,unit_price,line_total) VALUES (:t,:i,:d,:q,:u,:l)",
                    ['t'=>$tid,'i'=>$invId,'d'=>$it['description'],'q'=>$it['quantity'],'u'=>$it['unit_price'],'l'=>$it['line_total']]
                );
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            \App\Core\Logger::error('Invoice store failed: '.$e->getMessage());
            Session::flash('error','No se pudo crear la factura.');
            $this->redirect($back);
        }

        ActivityLog::record('created','invoices',$invId,'Factura '.$number);
        Session::flash('success','Factura '.$number.' creada.');
        $this->redirect('/admin/invoices/show/'.$invId);
    }

    public function show(Request $request, string $id): void
    {
        $inv = Invoice::withItems($this->tenantId(), (int) $id);
        if (!$inv) { $this->abort(404); }
        $this->renderAdmin('admin/invoices/show', [
            'title'  => 'Factura '.$inv['invoice_number'],
            'active' => 'invoices',
            'inv'    => $inv,
            'breadcrumbs' => [['label'=>'Facturas','url'=>url('/admin/invoices')],['label'=>$inv['invoice_number']]],
        ]);
    }

    public function pdf(Request $request, string $id): void
    {
        $inv = Invoice::withItems($this->tenantId(), (int) $id);
        if (!$inv) { $this->abort(404); }
        $this->view('admin/invoices/pdf', [
            'title'   => 'Factura '.$inv['invoice_number'],
            'inv'     => $inv,
            'backUrl' => url('/admin/invoices/show/'.$id),
        ], 'print');
    }

    public function status(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Invoice::findOrFail((int) $id, $tid);
        $status = $request->str('status');
        if (!in_array($status, Invoice::STATUSES, true)) { Session::flash('error','Estado inválido.'); $this->redirect('/admin/invoices/show/'.$id); }
        Invoice::update((int) $id, $tid, ['status' => $status]);
        ActivityLog::record('change_status','invoices',(int)$id,'Factura → '.$status);
        Session::flash('success','Factura actualizada.');
        $this->redirect('/admin/invoices/show/'.$id);
    }
}
