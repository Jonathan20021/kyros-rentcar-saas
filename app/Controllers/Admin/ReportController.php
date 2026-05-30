<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Database;
use App\Models\Payment;
use App\Models\Expense;

class ReportController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();

        $topVehicles = Database::select(
            "SELECT v.brand, v.model, v.plate_number, COUNT(r.id) AS rentals,
                    COALESCE(SUM(r.total_amount),0) AS revenue
               FROM vehicles v
               LEFT JOIN reservations r ON r.vehicle_id = v.id AND r.status IN ('converted','finished','confirmed')
              WHERE v.tenant_id = :t AND v.deleted_at IS NULL
              GROUP BY v.id ORDER BY rentals DESC LIMIT 8",
            ['t' => $tid]
        );

        $reservationStatus = \App\Models\Reservation::statusCounts($tid);

        // Performance per branch (revenue from contracts, expenses, fleet size)
        $branchPerformance = Database::select(
            "SELECT l.id, l.name,
                    (SELECT COUNT(*) FROM vehicles v WHERE v.location_id = l.id AND v.deleted_at IS NULL) AS vehicles,
                    (SELECT COALESCE(SUM(ct.total_amount),0) FROM contracts ct JOIN vehicles v ON v.id = ct.vehicle_id
                       WHERE v.location_id = l.id AND ct.tenant_id = :t2 AND ct.deleted_at IS NULL) AS revenue,
                    (SELECT COALESCE(SUM(e.amount),0) FROM expenses e
                       WHERE e.location_id = l.id AND e.tenant_id = :t3 AND e.deleted_at IS NULL) AS expenses
               FROM locations l
              WHERE l.tenant_id = :t1 AND l.deleted_at IS NULL AND l.status = 'active'
              ORDER BY revenue DESC",
            ['t1' => $tid, 't2' => $tid, 't3' => $tid]
        );

        $this->renderAdmin('admin/reports/index', [
            'title'   => 'Reportes · Kyros Rent Car',
            'active'  => 'reports',
            'topVehicles'   => $topVehicles,
            'monthlyIncome' => Payment::monthlyIncome($tid),
            'monthlyExpenses' => Expense::monthly($tid),
            'expensesByCategory' => Expense::byCategory($tid),
            'expenseYear'   => Expense::totalThisYear($tid),
            'branchPerformance' => $branchPerformance,
            'reservationStatus' => $reservationStatus,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Reportes']],
        ]);
    }

    /** Profit & Loss statement for a month (printable). ?month=YYYY-MM */
    public function pnl(Request $request): void
    {
        $tid   = $this->tenantId();
        $month = $request->str('month');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) { $month = date('Y-m'); }
        $from = $month . '-01';
        $to   = date('Y-m-t', strtotime($from));

        $income   = Payment::incomeBetween($tid, $from, $to);
        $expTotal = Expense::totalBetween($tid, $from, $to);
        $net      = $income - $expTotal;

        $this->view('admin/reports/pnl', [
            'title'          => 'Estado de Resultados · ' . $month,
            'tenant'         => \App\Models\Tenant::find($tid, null),
            'month'          => $month,
            'from'           => $from,
            'to'             => $to,
            'income'         => $income,
            'incomeByMethod' => Payment::incomeByMethodBetween($tid, $from, $to),
            'expByCategory'  => Expense::byCategoryBetween($tid, $from, $to),
            'expTotal'       => $expTotal,
            'net'            => $net,
            'margin'         => $income > 0 ? round($net / $income * 100, 1) : 0.0,
            'backUrl'        => url('/admin/reports'),
        ], 'print');
    }

    /** Export a report as CSV. type = income | vehicles | payments */
    public function export(Request $request, string $type): void
    {
        $tid = $this->tenantId();
        $rows = [];
        $name = 'reporte';

        if ($type === 'income') {
            $name = 'ingresos-mensuales';
            $rows[] = ['Mes', 'Ingresos'];
            foreach (Payment::monthlyIncome($tid) as $r) { $rows[] = [$r['ym'], number_format((float)$r['total'], 2, '.', '')]; }
        } elseif ($type === 'vehicles') {
            $name = 'vehiculos-mas-rentados';
            $rows[] = ['Marca', 'Modelo', 'Placa', 'Rentas', 'Ingresos'];
            foreach (Database::select(
                "SELECT v.brand, v.model, v.plate_number, COUNT(r.id) AS rentals, COALESCE(SUM(r.total_amount),0) AS revenue
                   FROM vehicles v LEFT JOIN reservations r ON r.vehicle_id=v.id AND r.status IN ('converted','finished','confirmed')
                  WHERE v.tenant_id=:t AND v.deleted_at IS NULL GROUP BY v.id ORDER BY rentals DESC",
                ['t'=>$tid]
            ) as $r) { $rows[] = [$r['brand'], $r['model'], $r['plate_number'], (int)$r['rentals'], number_format((float)$r['revenue'],2,'.','')]; }
        } elseif ($type === 'payments') {
            $name = 'pagos';
            $rows[] = ['Código', 'Fecha', 'Cliente', 'Método', 'Estado', 'Monto'];
            foreach (Database::select(
                "SELECT p.payment_code, p.payment_date, p.method, p.status, p.amount, CONCAT(c.first_name,' ',c.last_name) AS customer
                   FROM payments p LEFT JOIN customers c ON c.id=p.customer_id WHERE p.tenant_id=:t ORDER BY p.payment_date DESC",
                ['t'=>$tid]
            ) as $r) { $rows[] = [$r['payment_code'], $r['payment_date'], trim($r['customer'] ?? ''), $r['method'], $r['status'], number_format((float)$r['amount'],2,'.','')]; }
        } elseif ($type === 'expenses') {
            $name = 'gastos';
            $rows[] = ['Fecha', 'Descripción', 'Categoría', 'Sucursal', 'Vehículo', 'Método', 'Proveedor', 'Monto'];
            foreach (Expense::listForTenant($tid) as $r) {
                $rows[] = [$r['expense_date'], $r['description'], Expense::CATEGORIES[$r['category']] ?? $r['category'],
                    $r['location_name'] ?? '', $r['vehicle_name'] ?? '', Expense::METHODS[$r['payment_method']] ?? $r['payment_method'],
                    $r['vendor'] ?? '', number_format((float)$r['amount'],2,'.','')];
            }
        } else {
            $this->abort(404, 'Reporte no encontrado');
        }

        \App\Models\ActivityLog::record('export', 'reports', null, 'Exportó reporte: ' . $type);

        $filename = 'kyros-' . $name . '-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fprintf($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        foreach ($rows as $row) { fputcsv($out, $row); }
        fclose($out);
        exit;
    }
}
