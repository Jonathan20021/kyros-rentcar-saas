<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Expense extends Model
{
    protected static string $table = 'expenses';

    public const CATEGORIES = [
        'fuel'        => 'Combustible',
        'insurance'   => 'Seguros',
        'repairs'     => 'Reparaciones',
        'maintenance' => 'Mantenimiento',
        'salaries'    => 'Nómina',
        'rent'        => 'Alquiler',
        'utilities'   => 'Servicios',
        'marketing'   => 'Marketing',
        'taxes'       => 'Impuestos',
        'fees'        => 'Comisiones',
        'supplies'    => 'Insumos',
        'other'       => 'Otros',
    ];

    public const METHODS = [
        'cash'     => 'Efectivo',
        'card'     => 'Tarjeta',
        'transfer' => 'Transferencia',
        'check'    => 'Cheque',
        'other'    => 'Otro',
    ];

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT e.*, l.name AS location_name,
                       CONCAT(v.brand,' ',v.model) AS vehicle_name
                  FROM expenses e
                  LEFT JOIN locations l ON l.id = e.location_id
                  LEFT JOIN vehicles v ON v.id = e.vehicle_id
                 WHERE e.tenant_id = :t AND e.deleted_at IS NULL";
        $p = ['t' => $tenantId];
        if (!empty($filters['category']))   { $sql .= " AND e.category = :c"; $p['c'] = $filters['category']; }
        if (!empty($filters['location_id'])){ $sql .= " AND e.location_id = :l"; $p['l'] = (int) $filters['location_id']; }
        if (!empty($filters['from']))       { $sql .= " AND e.expense_date >= :from"; $p['from'] = $filters['from']; }
        if (!empty($filters['to']))         { $sql .= " AND e.expense_date <= :to"; $p['to'] = $filters['to']; }
        if (!empty($filters['search']))     { $sql .= " AND (e.description LIKE :s OR e.vendor LIKE :s)"; $p['s'] = '%'.$filters['search'].'%'; }
        $sql .= " ORDER BY e.expense_date DESC, e.id DESC";
        return Database::select($sql, $p);
    }

    public static function totalThisMonth(int $tenantId): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM expenses
              WHERE tenant_id = :t AND deleted_at IS NULL
                AND YEAR(expense_date)=YEAR(CURDATE()) AND MONTH(expense_date)=MONTH(CURDATE())",
            ['t' => $tenantId]
        );
    }

    public static function totalThisYear(int $tenantId): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM expenses
              WHERE tenant_id = :t AND deleted_at IS NULL AND YEAR(expense_date)=YEAR(CURDATE())",
            ['t' => $tenantId]
        );
    }

    public static function totalBetween(int $tenantId, string $from, string $to): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM expenses
              WHERE tenant_id = :t AND deleted_at IS NULL AND expense_date BETWEEN :from AND :to",
            ['t' => $tenantId, 'from' => $from, 'to' => $to]
        );
    }

    /** Sum by category within a date range (for the P&L statement). */
    public static function byCategoryBetween(int $tenantId, string $from, string $to): array
    {
        return Database::select(
            "SELECT category, SUM(amount) AS total, COUNT(*) AS cnt
               FROM expenses
              WHERE tenant_id = :t AND deleted_at IS NULL AND expense_date BETWEEN :from AND :to
              GROUP BY category ORDER BY total DESC",
            ['t' => $tenantId, 'from' => $from, 'to' => $to]
        );
    }

    /** Sum by category for the current year (for the reports breakdown). */
    public static function byCategory(int $tenantId): array
    {
        return Database::select(
            "SELECT category, SUM(amount) AS total, COUNT(*) AS cnt
               FROM expenses
              WHERE tenant_id = :t AND deleted_at IS NULL AND YEAR(expense_date)=YEAR(CURDATE())
              GROUP BY category ORDER BY total DESC",
            ['t' => $tenantId]
        );
    }

    /** Monthly totals for the last 12 months, keyed YYYY-MM (for net chart). */
    public static function monthly(int $tenantId): array
    {
        $rows = Database::select(
            "SELECT DATE_FORMAT(expense_date,'%Y-%m') AS ym, SUM(amount) AS total
               FROM expenses
              WHERE tenant_id = :t AND deleted_at IS NULL
                AND expense_date >= DATE_SUB(DATE_FORMAT(CURDATE(),'%Y-%m-01'), INTERVAL 11 MONTH)
              GROUP BY ym ORDER BY ym",
            ['t' => $tenantId]
        );
        $out = [];
        foreach ($rows as $r) { $out[$r['ym']] = (float) $r['total']; }
        return $out;
    }
}
