<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Customer;
use App\Models\ActivityLog;
use App\Services\FileUploader;

class CustomerController extends AdminController
{
    public function show(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $customer = Customer::findOrFail((int) $id, $tid);
        $reservations = Database::select(
            "SELECT r.reservation_code, r.start_datetime, r.end_datetime, r.status, r.total_amount, v.brand, v.model
               FROM reservations r JOIN vehicles v ON v.id=r.vehicle_id
              WHERE r.tenant_id=:t AND r.customer_id=:c AND r.deleted_at IS NULL ORDER BY r.start_datetime DESC LIMIT 8",
            ['t'=>$tid,'c'=>(int)$id]
        );
        $contracts = Database::select(
            "SELECT contract_number, start_datetime, status, total_amount, balance_due FROM contracts
              WHERE tenant_id=:t AND customer_id=:c AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 8",
            ['t'=>$tid,'c'=>(int)$id]
        );
        $payments = Database::select(
            "SELECT payment_code, amount, method, payment_date FROM payments WHERE tenant_id=:t AND customer_id=:c ORDER BY payment_date DESC LIMIT 8",
            ['t'=>$tid,'c'=>(int)$id]
        );
        $stats = [
            'reservations' => (int) Database::scalar("SELECT COUNT(*) FROM reservations WHERE tenant_id=:t AND customer_id=:c AND deleted_at IS NULL", ['t'=>$tid,'c'=>(int)$id]),
            'contracts'    => (int) Database::scalar("SELECT COUNT(*) FROM contracts WHERE tenant_id=:t AND customer_id=:c AND deleted_at IS NULL", ['t'=>$tid,'c'=>(int)$id]),
            'paid'         => (float) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM payments WHERE tenant_id=:t AND customer_id=:c AND status='paid'", ['t'=>$tid,'c'=>(int)$id]),
            'balance'      => (float) Database::scalar("SELECT COALESCE(SUM(balance_due),0) FROM contracts WHERE tenant_id=:t AND customer_id=:c AND deleted_at IS NULL", ['t'=>$tid,'c'=>(int)$id]),
        ];
        $this->renderAdmin('admin/customers/show', [
            'title'    => trim($customer['first_name'].' '.$customer['last_name']).' · Kyros Rent Car',
            'active'   => 'customers',
            'customer' => $customer,
            'reservations' => $reservations,
            'contracts' => $contracts,
            'payments' => $payments,
            'stats'    => $stats,
            'breadcrumbs' => [['label'=>'Clientes','url'=>url('/admin/customers')],['label'=>trim($customer['first_name'].' '.$customer['last_name'])]],
        ]);
    }

    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = ['status' => $request->str('status'), 'search' => $request->str('search')];
        $this->renderAdmin('admin/customers/index', [
            'title'     => 'Clientes · Kyros Rent Car',
            'active'    => 'customers',
            'customers' => Customer::listForTenant($tid, $filters),
            'filters'   => $filters,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Clientes']],
        ]);
    }

    public function create(Request $request): void
    {
        $this->renderAdmin('admin/customers/form', [
            'title'    => 'Nuevo cliente · Kyros Rent Car',
            'active'   => 'customers',
            'customer' => null,
            'breadcrumbs' => [['label'=>'Clientes','url'=>url('/admin/customers')],['label'=>'Nuevo']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        $data = $this->validated($request, '/admin/customers/create');
        $payload = $this->payload($tid, $request, $data);

        if ($f = $request->file('license_front_image')) {
            if ($p = FileUploader::document($f, 'licenses')) $payload['license_front_image'] = $p;
        }
        $id = Customer::create($payload);
        ActivityLog::record('created', 'customers', $id, 'Cliente creado: ' . $data['first_name']);
        Session::flash('success', 'Cliente creado correctamente.');
        $this->redirect('/admin/customers');
    }

    public function edit(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $customer = Customer::findOrFail((int) $id, $tid);
        $this->renderAdmin('admin/customers/form', [
            'title'    => 'Editar cliente · Kyros Rent Car',
            'active'   => 'customers',
            'customer' => $customer,
            'breadcrumbs' => [['label'=>'Clientes','url'=>url('/admin/customers')],['label'=>'Editar']],
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Customer::findOrFail((int) $id, $tid);
        $data = $this->validated($request, '/admin/customers/edit/' . $id);
        $payload = $this->payload($tid, $request, $data);
        unset($payload['tenant_id']);
        if ($f = $request->file('license_front_image')) {
            if ($p = FileUploader::document($f, 'licenses')) $payload['license_front_image'] = $p;
        }
        Customer::update((int) $id, $tid, $payload);
        ActivityLog::record('updated', 'customers', (int) $id, 'Cliente actualizado');
        Session::flash('success', 'Cliente actualizado.');
        $this->redirect('/admin/customers');
    }

    public function destroy(Request $request, string $id): void
    {
        if (!can('customers.delete')) { $this->abort(403); }
        $tid = $this->tenantId();
        Customer::findOrFail((int) $id, $tid);

        $activeContracts = (int) Database::scalar(
            "SELECT COUNT(*) FROM contracts WHERE tenant_id = :t AND customer_id = :c AND status IN ('active','overdue','claim') AND deleted_at IS NULL",
            ['t' => $tid, 'c' => (int) $id]
        );
        if ($activeContracts > 0) {
            Session::flash('error', 'No se puede eliminar: el cliente tiene ' . $activeContracts . ' contrato(s) activo(s).');
            $this->redirect('/admin/customers/show/' . $id);
        }

        Customer::delete((int) $id, $tid);
        ActivityLog::record('deleted', 'customers', (int) $id, 'Cliente eliminado');
        Session::flash('success', 'Cliente eliminado.');
        $this->redirect('/admin/customers');
    }

    /** Bulk delete from the list page. Skips customers with active contracts. */
    public function bulkDestroy(Request $request): void
    {
        if (!can('customers.delete')) { $this->abort(403); }
        $tid = $this->tenantId();
        $raw = (string) $request->input('ids', '');
        $ids = array_filter(array_map('intval', explode(',', $raw)), fn($n) => $n > 0);
        if (!$ids) {
            Session::flash('error', 'No se seleccionaron clientes.');
            $this->redirect('/admin/customers');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        // Customers with at least one active contract — skipped.
        $skipRows = Database::select(
            "SELECT customer_id, COUNT(*) AS n FROM contracts
              WHERE tenant_id = ? AND customer_id IN ($placeholders)
                AND status IN ('active','overdue','claim') AND deleted_at IS NULL
           GROUP BY customer_id",
            array_merge([$tid], $ids)
        );
        $skipIds = array_map(fn($r) => (int) $r['customer_id'], $skipRows);
        $deleteIds = array_values(array_diff($ids, $skipIds));

        $n = 0;
        foreach ($deleteIds as $cid) {
            try {
                Customer::delete($cid, $tid);
                ActivityLog::record('deleted', 'customers', $cid, 'Eliminación masiva');
                $n++;
            } catch (\Throwable $e) {
                \App\Core\Logger::warning('Bulk delete customer ' . $cid . ' failed: ' . $e->getMessage());
            }
        }

        $msg = $n . ' cliente' . ($n === 1 ? '' : 's') . ' eliminado' . ($n === 1 ? '' : 's') . '.';
        if ($skipIds) {
            $msg .= ' Se omitieron ' . count($skipIds) . ' con contratos activos.';
        }
        Session::flash($n > 0 ? 'success' : 'warning', $msg);
        $this->redirect('/admin/customers');
    }

    protected function validated(Request $request, string $back): array
    {
        return $this->validateOrBack($request->all(), [
            'first_name'    => 'required|max:80',
            'document_type' => 'required|in:cedula,passport,license,rnc',
            'email'         => 'email|max:150',
            'status'        => 'in:active,blocked,blacklist,pending',
        ], $back);
    }

    protected function payload(int $tid, Request $request, array $data): array
    {
        return [
            'tenant_id'          => $tid,
            'first_name'         => $data['first_name'],
            'last_name'          => $request->str('last_name') ?: null,
            'document_type'      => $data['document_type'],
            'document_number'    => $request->str('document_number') ?: null,
            'nationality'        => $request->str('nationality') ?: null,
            'birth_date'         => $request->str('birth_date') ?: null,
            'phone'              => $request->str('phone') ?: null,
            'whatsapp'           => $request->str('whatsapp') ?: null,
            'email'              => $request->str('email') ? strtolower($request->str('email')) : null,
            'address'            => $request->str('address') ?: null,
            'license_number'     => $request->str('license_number') ?: null,
            'license_expiration' => $request->str('license_expiration') ?: null,
            'risk_level'         => $request->str('risk_level', 'low'),
            'notes'              => $request->str('notes') ?: null,
            'status'             => $data['status'] ?? 'active',
        ];
    }
}
