<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Payment extends Model
{
    protected static string $table = 'payments';
    protected static bool $softDeletes = false;

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT p.*, CONCAT(c.first_name,' ',c.last_name) AS customer_name
                  FROM payments p
                  LEFT JOIN customers c ON c.id = p.customer_id
                 WHERE p.tenant_id = :t";
        $params = ['t' => $tenantId];
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :st"; $params['st'] = $filters['status'];
        }
        $sql .= " ORDER BY p.payment_date DESC, p.id DESC";
        return Database::select($sql, $params);
    }

    public static function incomeThisMonth(int $tenantId): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM payments
              WHERE tenant_id = :t AND status = 'paid'
                AND YEAR(payment_date) = YEAR(CURDATE()) AND MONTH(payment_date) = MONTH(CURDATE())",
            ['t' => $tenantId]
        );
    }

    public static function pendingCount(int $tenantId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM payments WHERE tenant_id = :t AND status = 'pending'",
            ['t' => $tenantId]
        );
    }

    /** Last 12 months income for the chart. */
    public static function monthlyIncome(int $tenantId): array
    {
        $rows = Database::select(
            "SELECT DATE_FORMAT(payment_date,'%Y-%m') AS ym, COALESCE(SUM(amount),0) AS total
               FROM payments
              WHERE tenant_id = :t AND status = 'paid'
                AND payment_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
              GROUP BY ym ORDER BY ym ASC",
            ['t' => $tenantId]
        );
        return $rows;
    }

    /** Paid income within an inclusive date range. */
    public static function incomeBetween(int $tenantId, string $from, string $to): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM payments
              WHERE tenant_id = :t AND status = 'paid' AND payment_date BETWEEN :from AND :to",
            ['t' => $tenantId, 'from' => $from, 'to' => $to]
        );
    }

    /** Paid income grouped by method within a date range. */
    public static function incomeByMethodBetween(int $tenantId, string $from, string $to): array
    {
        return Database::select(
            "SELECT method, COALESCE(SUM(amount),0) AS total, COUNT(*) AS cnt
               FROM payments
              WHERE tenant_id = :t AND status = 'paid' AND payment_date BETWEEN :from AND :to
              GROUP BY method ORDER BY total DESC",
            ['t' => $tenantId, 'from' => $from, 'to' => $to]
        );
    }

    public static function nextCode(int $tenantId): string
    {
        $year = date('Y');
        $max = (int) Database::scalar(
            "SELECT MAX(CAST(SUBSTRING_INDEX(payment_code, '-', -1) AS UNSIGNED))
               FROM payments WHERE tenant_id = :t AND payment_code LIKE :pfx",
            ['t' => $tenantId, 'pfx' => "PAY-{$year}-%"]
        );
        return sprintf('PAY-%s-%04d', $year, $max + 1);
    }
}
