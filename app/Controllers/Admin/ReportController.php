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

        // Date range: defaults to current month-to-date. Accepts ?from=YYYY-MM-DD&to=YYYY-MM-DD
        $from = $request->str('from') ?: date('Y-m-01');
        $to   = $request->str('to')   ?: date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');
        if ($from > $to) { [$from, $to] = [$to, $from]; }

        // Previous period of equal length for comparison.
        $days = max(1, (int) ((strtotime($to) - strtotime($from)) / 86400) + 1);
        $prevTo   = date('Y-m-d', strtotime($from) - 86400);
        $prevFrom = date('Y-m-d', strtotime($prevTo) - ($days - 1) * 86400);

        // KPIs current + previous period
        $incomeCur  = Payment::incomeBetween($tid, $from, $to);
        $incomePrev = Payment::incomeBetween($tid, $prevFrom, $prevTo);
        $expCur     = Expense::totalBetween($tid, $from, $to);
        $expPrev    = Expense::totalBetween($tid, $prevFrom, $prevTo);
        $netCur     = $incomeCur - $expCur;
        $netPrev    = $incomePrev - $expPrev;

        $resCur  = (int) Database::scalar(
            "SELECT COUNT(*) FROM reservations WHERE tenant_id=:t AND deleted_at IS NULL AND DATE(created_at) BETWEEN :f AND :tt",
            ['t'=>$tid,'f'=>$from,'tt'=>$to]
        );
        $resPrev = (int) Database::scalar(
            "SELECT COUNT(*) FROM reservations WHERE tenant_id=:t AND deleted_at IS NULL AND DATE(created_at) BETWEEN :f AND :tt",
            ['t'=>$tid,'f'=>$prevFrom,'tt'=>$prevTo]
        );

        $contractsCur = (int) Database::scalar(
            "SELECT COUNT(*) FROM contracts WHERE tenant_id=:t AND deleted_at IS NULL AND DATE(start_datetime) BETWEEN :f AND :tt",
            ['t'=>$tid,'f'=>$from,'tt'=>$to]
        );
        $contractsPrev = (int) Database::scalar(
            "SELECT COUNT(*) FROM contracts WHERE tenant_id=:t AND deleted_at IS NULL AND DATE(start_datetime) BETWEEN :f AND :tt",
            ['t'=>$tid,'f'=>$prevFrom,'tt'=>$prevTo]
        );

        $delta = fn($cur, $prev) => $prev > 0 ? round((($cur - $prev) / $prev) * 100, 1) : ($cur > 0 ? 100.0 : 0.0);

        $kpis = [
            ['label'=>'Ingresos',         'value'=>$incomeCur,   'prev'=>$incomePrev,   'delta'=>$delta($incomeCur,$incomePrev),     'icon'=>'arrow-down-to-line','tone'=>'emerald','isMoney'=>true],
            ['label'=>'Gastos',           'value'=>$expCur,      'prev'=>$expPrev,      'delta'=>$delta($expCur,$expPrev),           'icon'=>'arrow-up-from-line','tone'=>'red',     'isMoney'=>true,'invertGood'=>true],
            ['label'=>'Utilidad neta',    'value'=>$netCur,      'prev'=>$netPrev,      'delta'=>$delta($netCur,$netPrev),           'icon'=>'wallet','tone'=>$netCur>=0?'emerald':'red','isMoney'=>true],
            ['label'=>'Margen',           'value'=>$incomeCur>0?round(($netCur/$incomeCur)*100,1):0, 'prev'=>$incomePrev>0?round(($netPrev/$incomePrev)*100,1):0, 'delta'=>0,'icon'=>'percent','tone'=>'indigo','isPercent'=>true],
            ['label'=>'Reservas',         'value'=>$resCur,      'prev'=>$resPrev,      'delta'=>$delta($resCur,$resPrev),           'icon'=>'calendar-check','tone'=>'amber'],
            ['label'=>'Contratos',        'value'=>$contractsCur,'prev'=>$contractsPrev,'delta'=>$delta($contractsCur,$contractsPrev),'icon'=>'file-text','tone'=>'cyan'],
        ];

        // Daily revenue + expenses for the range (for line chart)
        $dailyRows = Database::select(
            "SELECT DATE(payment_date) d, COALESCE(SUM(amount),0) total
               FROM payments
              WHERE tenant_id=:t AND status='paid'
                AND payment_date BETWEEN :f AND :tt
              GROUP BY DATE(payment_date) ORDER BY d ASC",
            ['t'=>$tid,'f'=>$from,'tt'=>$to]
        );
        $dailyExp = Database::select(
            "SELECT DATE(expense_date) d, COALESCE(SUM(amount),0) total
               FROM expenses
              WHERE tenant_id=:t AND deleted_at IS NULL
                AND expense_date BETWEEN :f AND :tt
              GROUP BY DATE(expense_date) ORDER BY d ASC",
            ['t'=>$tid,'f'=>$from,'tt'=>$to]
        );
        // Fill missing days with zeros so the chart is gap-free
        $dailySeries = ['labels'=>[], 'income'=>[], 'expense'=>[]];
        $incMap = []; foreach ($dailyRows as $r) $incMap[$r['d']] = (float) $r['total'];
        $expMap = []; foreach ($dailyExp as $r) $expMap[$r['d']] = (float) $r['total'];
        for ($t = strtotime($from); $t <= strtotime($to); $t += 86400) {
            $d = date('Y-m-d', $t);
            $dailySeries['labels'][] = date('d/m', $t);
            $dailySeries['income'][] = $incMap[$d] ?? 0;
            $dailySeries['expense'][] = $expMap[$d] ?? 0;
        }

        // Top vehicles in the date range (revenue contribution)
        $topVehicles = Database::select(
            "SELECT v.brand, v.model, v.plate_number, v.main_image,
                    COUNT(ct.id) AS rentals,
                    COALESCE(SUM(ct.total_amount),0) AS revenue
               FROM vehicles v
          LEFT JOIN contracts ct ON ct.vehicle_id = v.id AND ct.deleted_at IS NULL
                                AND DATE(ct.start_datetime) BETWEEN :f AND :tt
              WHERE v.tenant_id=:t AND v.deleted_at IS NULL
              GROUP BY v.id ORDER BY revenue DESC, rentals DESC LIMIT 8",
            ['t'=>$tid,'f'=>$from,'tt'=>$to]
        );

        // Top customers in the date range
        $topCustomers = Database::select(
            "SELECT c.id, c.first_name, c.last_name, c.email,
                    COUNT(ct.id) AS contracts,
                    COALESCE(SUM(ct.total_amount),0) AS revenue
               FROM customers c
               JOIN contracts ct ON ct.customer_id = c.id AND ct.deleted_at IS NULL
                                AND DATE(ct.start_datetime) BETWEEN :f AND :tt
              WHERE c.tenant_id=:t AND c.deleted_at IS NULL
              GROUP BY c.id ORDER BY revenue DESC, contracts DESC LIMIT 6",
            ['t'=>$tid,'f'=>$from,'tt'=>$to]
        );

        // Payment methods breakdown (donut)
        $paymentMethods = Database::select(
            "SELECT method, COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total
               FROM payments
              WHERE tenant_id=:t AND status='paid'
                AND payment_date BETWEEN :f AND :tt
              GROUP BY method ORDER BY total DESC",
            ['t'=>$tid,'f'=>$from,'tt'=>$to]
        );

        // Expenses by category for the range
        $expensesByCategory = Database::select(
            "SELECT category, COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total
               FROM expenses WHERE tenant_id=:t AND deleted_at IS NULL
                AND expense_date BETWEEN :f AND :tt
              GROUP BY category ORDER BY total DESC",
            ['t'=>$tid,'f'=>$from,'tt'=>$to]
        );

        $reservationStatus = \App\Models\Reservation::statusCounts($tid);

        // Performance per branch (revenue from contracts, expenses, fleet size) for the range
        $branchPerformance = Database::select(
            "SELECT l.id, l.name,
                    (SELECT COUNT(*) FROM vehicles v WHERE v.location_id = l.id AND v.deleted_at IS NULL) AS vehicles,
                    (SELECT COALESCE(SUM(ct.total_amount),0) FROM contracts ct JOIN vehicles v ON v.id = ct.vehicle_id
                       WHERE v.location_id = l.id AND ct.tenant_id = :t2 AND ct.deleted_at IS NULL
                         AND DATE(ct.start_datetime) BETWEEN :f1 AND :t1) AS revenue,
                    (SELECT COALESCE(SUM(e.amount),0) FROM expenses e
                       WHERE e.location_id = l.id AND e.tenant_id = :t3 AND e.deleted_at IS NULL
                         AND e.expense_date BETWEEN :f2 AND :t4) AS expenses
               FROM locations l
              WHERE l.tenant_id = :t1q AND l.deleted_at IS NULL AND l.status = 'active'
              ORDER BY revenue DESC",
            ['t1q' => $tid, 't2' => $tid, 't3' => $tid,
             'f1' => $from, 't1' => $to, 'f2' => $from, 't4' => $to]
        );

        $this->renderAdmin('admin/reports/index', [
            'title'   => 'Reportes · Kyros Rent Car',
            'active'  => 'reports',
            'from' => $from, 'to' => $to, 'days' => $days,
            'prevFrom' => $prevFrom, 'prevTo' => $prevTo,
            'kpis' => $kpis,
            'dailySeries' => $dailySeries,
            'topVehicles' => $topVehicles,
            'topCustomers' => $topCustomers,
            'paymentMethods' => $paymentMethods,
            'monthlyIncome' => Payment::monthlyIncome($tid),
            'monthlyExpenses' => Expense::monthly($tid),
            'expensesByCategory' => $expensesByCategory,
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
