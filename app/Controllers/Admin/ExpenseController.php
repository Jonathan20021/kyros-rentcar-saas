<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Auth;
use App\Models\Expense;
use App\Models\Location;
use App\Models\Vehicle;
use App\Models\Payment;
use App\Models\ActivityLog;

class ExpenseController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = [
            'category'    => $request->str('category'),
            'location_id' => $request->int('location_id'),
            'from'        => $request->str('from'),
            'to'          => $request->str('to'),
            'search'      => $request->str('search'),
        ];
        $monthExpense = Expense::totalThisMonth($tid);
        $monthIncome  = Payment::incomeThisMonth($tid);
        $this->renderAdmin('admin/expenses/index', [
            'title'    => 'Gastos · Kyros',
            'active'   => 'expenses',
            'expenses' => Expense::listForTenant($tid, $filters),
            'filters'  => $filters,
            'locations'=> Location::activeForTenant($tid),
            'monthExpense' => $monthExpense,
            'monthIncome'  => $monthIncome,
            'monthNet'     => $monthIncome - $monthExpense,
            'yearExpense'  => Expense::totalThisYear($tid),
            'breadcrumbs'  => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Gastos']],
        ]);
    }

    public function create(Request $request): void
    {
        $this->form(null);
    }

    public function edit(Request $request, string $id): void
    {
        $this->form(Expense::findOrFail((int) $id, $this->tenantId()));
    }

    protected function form(?array $expense): void
    {
        $tid = $this->tenantId();
        $this->renderAdmin('admin/expenses/form', [
            'title'     => ($expense ? 'Editar' : 'Nuevo') . ' gasto · Kyros',
            'active'    => 'expenses',
            'expense'   => $expense,
            'locations' => Location::activeForTenant($tid),
            'vehicles'  => Vehicle::listForTenant($tid),
            'breadcrumbs' => [['label'=>'Gastos','url'=>url('/admin/expenses')],['label'=>$expense ? 'Editar' : 'Nuevo']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        $data = $this->validated($request, '/admin/expenses/create');
        $id = Expense::create(array_merge(['tenant_id' => $tid, 'created_by' => Auth::id()], $data));
        ActivityLog::record('created', 'expenses', $id, 'Gasto: ' . $data['description'] . ' (' . $data['amount'] . ')');
        Session::flash('success', 'Gasto registrado.');
        $this->redirect('/admin/expenses');
    }

    public function update(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Expense::findOrFail((int) $id, $tid);
        $data = $this->validated($request, '/admin/expenses/edit/' . $id);
        Expense::update((int) $id, $tid, $data);
        ActivityLog::record('updated', 'expenses', (int) $id, 'Gasto actualizado');
        Session::flash('success', 'Gasto actualizado.');
        $this->redirect('/admin/expenses');
    }

    public function destroy(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Expense::findOrFail((int) $id, $tid);
        Expense::delete((int) $id, $tid);
        ActivityLog::record('deleted', 'expenses', (int) $id, 'Gasto eliminado');
        Session::flash('success', 'Gasto eliminado.');
        $this->redirect('/admin/expenses');
    }

    protected function validated(Request $request, string $back): array
    {
        $v = $this->validateOrBack($request->all(), [
            'description'    => 'required|max:200',
            'amount'         => 'required|numeric|min:0',
            'category'       => 'required|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'expense_date'   => 'required',
            'payment_method' => 'required|in:' . implode(',', array_keys(Expense::METHODS)),
        ], $back);
        return [
            'description'    => $v['description'],
            'amount'         => (float) $v['amount'],
            'category'       => $v['category'],
            'expense_date'   => $v['expense_date'],
            'payment_method' => $v['payment_method'],
            'location_id'    => $request->int('location_id') ?: null,
            'vehicle_id'     => $request->int('vehicle_id') ?: null,
            'vendor'         => $request->str('vendor') ?: null,
            'reference'      => $request->str('reference') ?: null,
            'notes'          => $request->str('notes') ?: null,
        ];
    }
}
