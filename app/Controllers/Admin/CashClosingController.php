<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Auth;
use App\Models\CashClosing;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\ActivityLog;

class CashClosingController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $this->renderAdmin('admin/cashbox/index', [
            'title'    => 'Cierre de caja · Kyros Rent Car',
            'active'   => 'cashbox',
            'closings' => CashClosing::listForTenant($tid),
            'todayDone'=> CashClosing::existsForDate($tid, date('Y-m-d')),
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Cierre de caja']],
        ]);
    }

    public function create(Request $request): void
    {
        $tid  = $this->tenantId();
        $date = $request->str('date') ?: date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { $date = date('Y-m-d'); }
        $this->renderAdmin('admin/cashbox/create', [
            'title'     => 'Nuevo cierre · Kyros Rent Car',
            'active'    => 'cashbox',
            'date'      => $date,
            'movements' => CashClosing::computeForDate($tid, $date),
            'locations' => Location::activeForTenant($tid),
            'already'   => CashClosing::existsForDate($tid, $date),
            'breadcrumbs' => [['label'=>'Cierre de caja','url'=>url('/admin/cashbox')],['label'=>'Nuevo']],
        ]);
    }

    public function store(Request $request): void
    {
        if (!can('cashbox.manage')) { $this->abort(403); }
        $tid  = $this->tenantId();
        $date = $request->str('closing_date');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            Session::flash('error', 'Fecha inválida.');
            $this->redirect('/admin/cashbox/create');
        }
        $m = CashClosing::computeForDate($tid, $date);
        $counted = $request->float('counted_cash');
        $difference = round($counted - $m['expected_cash'], 2);

        $id = CashClosing::create([
            'tenant_id'       => $tid,
            'location_id'     => $request->int('location_id') ?: null,
            'closing_date'    => $date,
            'income_cash'     => $m['income']['cash'],
            'income_card'     => $m['income']['card'],
            'income_transfer' => $m['income']['transfer'],
            'income_other'    => $m['income']['other'],
            'income_total'    => $m['income_total'],
            'expense_cash'    => $m['expense_cash'],
            'expense_total'   => $m['expense_total'],
            'expected_cash'   => $m['expected_cash'],
            'counted_cash'    => $counted,
            'difference'      => $difference,
            'notes'           => $request->str('notes') ?: null,
            'closed_by'       => Auth::id(),
        ]);
        ActivityLog::record('created', 'cash_closings', $id, 'Cierre de caja ' . $date . ' (dif: ' . $difference . ')');
        Session::flash('success', 'Cierre de caja registrado.');
        $this->redirect('/admin/cashbox/show/' . $id);
    }

    public function show(Request $request, string $id): void
    {
        $cc = CashClosing::withRelations($this->tenantId(), (int) $id);
        if (!$cc) { $this->abort(404); }
        $this->view('admin/cashbox/show', [
            'title'   => 'Cierre ' . $cc['closing_date'],
            'cc'      => $cc,
            'tenant'  => Tenant::find($this->tenantId(), null),
            'backUrl' => url('/admin/cashbox'),
        ], 'print');
    }
}
