<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Core\Auth;
use App\Models\Payment;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\ActivityLog;
use App\Services\Mailer;

class PaymentController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = ['status' => $request->str('status')];
        $this->renderAdmin('admin/payments/index', [
            'title'    => 'Pagos · Kyros',
            'active'   => 'payments',
            'payments' => Payment::listForTenant($tid, $filters),
            'filters'  => $filters,
            'incomeMonth' => Payment::incomeThisMonth($tid),
            'pending'  => Payment::pendingCount($tid),
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Pagos']],
        ]);
    }

    public function create(Request $request): void
    {
        $tid = $this->tenantId();
        $contractId = $request->int('contract_id');
        $contract = $contractId ? Database::selectOne(
            "SELECT ct.*, CONCAT(c.first_name,' ',c.last_name) AS customer_name FROM contracts ct JOIN customers c ON c.id=ct.customer_id WHERE ct.id=:id AND ct.tenant_id=:t",
            ['id'=>$contractId,'t'=>$tid]
        ) : null;
        $this->renderAdmin('admin/payments/form', [
            'title'    => 'Registrar pago · Kyros',
            'active'   => 'payments',
            'contract' => $contract,
            'contracts'=> Database::select("SELECT ct.id, ct.contract_number, ct.balance_due, CONCAT(c.first_name,' ',c.last_name) AS customer_name FROM contracts ct JOIN customers c ON c.id=ct.customer_id WHERE ct.tenant_id=:t AND ct.deleted_at IS NULL AND ct.status IN ('active','overdue','finished') ORDER BY ct.created_at DESC", ['t'=>$tid]),
            'customers'=> Customer::listForTenant($tid),
            'breadcrumbs' => [['label'=>'Pagos','url'=>url('/admin/payments')],['label'=>'Registrar']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        $back = '/admin/payments/create';
        $data = $this->validateOrBack($request->all(), [
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,transfer,card,paypal,stripe,azul,cardnet,other',
        ], $back);

        $contractId = $request->int('contract_id') ?: null;
        $customerId = $request->int('customer_id') ?: null;
        $amount = (float) $data['amount'];

        // If applied to a contract, guard against overpay (business rule #10).
        if ($contractId) {
            $ct = Contract::find($contractId, $tid);
            if (!$ct) { Session::flash('error','Contrato invalido.'); $this->redirect($back); }
            $customerId = (int) $ct['customer_id'];
            if ($amount > (float) $ct['balance_due'] + 0.01 && !$request->input('allow_overpay')) {
                Session::flash('error','El monto excede el balance pendiente ('.money($ct['balance_due']).'). Marca "permitir excedente" si es intencional.');
                Session::flashInput($request->all());
                $this->redirect($back);
            }
        }

        try {
            Database::beginTransaction();
            $code = Payment::nextCode($tid);
            $pid = Payment::create([
                'tenant_id'=>$tid,'payment_code'=>$code,'customer_id'=>$customerId,'reservation_id'=>null,
                'contract_id'=>$contractId,'amount'=>$amount,'method'=>$data['method'],
                'reference'=>$request->str('reference') ?: null,'payment_date'=>$request->str('payment_date') ?: date('Y-m-d'),
                'status'=>'paid','notes'=>$request->str('notes') ?: null,'received_by'=>Auth::id(),
            ]);
            if ($contractId) {
                $ct = Contract::find($contractId, $tid);
                $paid = (float) $ct['paid_amount'] + $amount;
                $balance = max(0, (float) $ct['total_amount'] - $paid);
                Contract::update($contractId, $tid, ['paid_amount'=>$paid,'balance_due'=>$balance]);
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            \App\Core\Logger::error('Payment failed: '.$e->getMessage());
            Session::flash('error','No se pudo registrar el pago.');
            $this->redirect($back);
        }

        // Receipt email to the customer
        if ($customerId) {
            try {
                $cust = Customer::find((int) $customerId, $tid);
                $to = $cust['email'] ?? null;
                if ($to) {
                    $tenant = Tenant::find($tid, null);
                    $body = '<p>Hola <strong>'.e(trim($cust['first_name'].' '.$cust['last_name'])).'</strong>,</p>'
                        . '<p>Recibimos tu pago. ¡Gracias!</p>'
                        . '<div style="margin:14px 0;padding:16px;background:#fafbfc;border:1px solid #eef1f6;border-radius:12px;text-align:center">'
                        . '<div style="font-size:12px;color:#6b7280">Monto recibido</div>'
                        . '<div style="font-size:28px;font-weight:800;color:#1c2433">'.money($amount).'</div>'
                        . '<div style="font-size:12px;color:#9aa3b2;margin-top:4px">'.e($code).' · '.ucfirst($data['method']).'</div></div>';
                    Mailer::send($to, 'Recibo de pago '.$code, Mailer::layout('Pago recibido ✓', $body, $tenant), $tenant['email'] ?? null);
                }
            } catch (\Throwable $e) { \App\Core\Logger::error('receipt mail: '.$e->getMessage()); }
        }

        ActivityLog::record('created','payments',$pid,'Pago '.$code.' ('.money($amount).')');
        Session::flash('success','Pago '.$code.' registrado.');
        $this->redirect('/admin/payments/receipt/'.$pid);
    }

    public function receipt(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $p = Payment::find((int) $id, $tid);
        if (!$p) { $this->abort(404); }
        $p['tenant']   = Tenant::find($tid, null);
        $p['customer'] = $p['customer_id'] ? Customer::find((int) $p['customer_id'], $tid) : null;
        $p['contract'] = $p['contract_id'] ? Contract::find((int) $p['contract_id'], $tid) : null;
        $this->view('admin/payments/receipt', [
            'title'   => 'Recibo '.$p['payment_code'],
            'p'       => $p,
            'backUrl' => url('/admin/payments'),
        ], 'print');
    }

    /** Void / refund a payment and recompute the contract balance. */
    public function void(Request $request, string $id): void
    {
        if (!can('payments.edit')) { $this->abort(403); }
        $tid = $this->tenantId();
        $p = Payment::find((int) $id, $tid);
        if (!$p) { $this->abort(404); }
        if (in_array($p['status'], ['void','refunded'], true)) {
            Session::flash('warning', 'Este pago ya está anulado.');
            $this->redirect('/admin/payments');
        }

        try {
            Database::beginTransaction();
            Database::execute("UPDATE payments SET status = 'void' WHERE id = :id AND tenant_id = :t", ['id'=>$p['id'],'t'=>$tid]);
            if (!empty($p['contract_id'])) {
                $ct = Contract::find((int) $p['contract_id'], $tid);
                if ($ct) {
                    $paid = max(0, (float) $ct['paid_amount'] - (float) $p['amount']);
                    $balance = max(0, (float) $ct['total_amount'] - $paid);
                    Contract::update((int) $ct['id'], $tid, ['paid_amount'=>$paid, 'balance_due'=>$balance]);
                }
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            \App\Core\Logger::error('Payment void failed: '.$e->getMessage());
            Session::flash('error', 'No se pudo anular el pago.');
            $this->redirect('/admin/payments');
        }

        ActivityLog::record('updated', 'payments', (int) $p['id'], 'Pago '.$p['payment_code'].' anulado');
        Session::flash('success', 'Pago anulado y balance del contrato recalculado.');
        $this->redirect('/admin/payments');
    }
}
