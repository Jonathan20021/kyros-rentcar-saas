<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class CashClosing extends Model
{
    protected static string $table = 'cash_closings';
    protected static bool $softDeletes = false;

    public static function listForTenant(int $tenantId, int $limit = 60): array
    {
        $limit = max(1, min($limit, 200));
        return Database::select(
            "SELECT cc.*, l.name AS location_name, u.name AS closed_by_name
               FROM cash_closings cc
               LEFT JOIN locations l ON l.id = cc.location_id
               LEFT JOIN users u ON u.id = cc.closed_by
              WHERE cc.tenant_id = :t
              ORDER BY cc.closing_date DESC, cc.id DESC LIMIT {$limit}",
            ['t' => $tenantId]
        );
    }

    /**
     * Compute the day's cash movements from payments + expenses.
     * Income is grouped by payment method; cash expenses reduce expected cash.
     */
    public static function computeForDate(int $tenantId, string $date): array
    {
        $income = ['cash' => 0.0, 'card' => 0.0, 'transfer' => 0.0, 'other' => 0.0];
        foreach (Database::select(
            "SELECT method, COALESCE(SUM(amount),0) AS total
               FROM payments WHERE tenant_id = :t AND status = 'paid' AND DATE(payment_date) = :d
              GROUP BY method", ['t' => $tenantId, 'd' => $date]
        ) as $r) {
            $key = in_array($r['method'], ['cash','card','transfer'], true) ? $r['method'] : 'other';
            $income[$key] += (float) $r['total'];
        }
        $incomeTotal = array_sum($income);

        $expenseCash = (float) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM expenses
              WHERE tenant_id = :t AND deleted_at IS NULL AND expense_date = :d AND payment_method = 'cash'",
            ['t' => $tenantId, 'd' => $date]
        );
        $expenseTotal = (float) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM expenses
              WHERE tenant_id = :t AND deleted_at IS NULL AND expense_date = :d",
            ['t' => $tenantId, 'd' => $date]
        );

        return [
            'income'        => $income,
            'income_total'  => $incomeTotal,
            'expense_cash'  => $expenseCash,
            'expense_total' => $expenseTotal,
            'expected_cash' => round($income['cash'] - $expenseCash, 2),
            'payments'      => Database::select(
                "SELECT payment_code, method, amount, payment_date FROM payments
                  WHERE tenant_id = :t AND status='paid' AND DATE(payment_date)=:d ORDER BY id DESC",
                ['t' => $tenantId, 'd' => $date]
            ),
        ];
    }

    public static function existsForDate(int $tenantId, string $date): bool
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM cash_closings WHERE tenant_id = :t AND closing_date = :d",
            ['t' => $tenantId, 'd' => $date]
        ) > 0;
    }

    public static function withRelations(int $tenantId, int $id): ?array
    {
        return Database::selectOne(
            "SELECT cc.*, l.name AS location_name, u.name AS closed_by_name, t.name AS tenant_name
               FROM cash_closings cc
               LEFT JOIN locations l ON l.id = cc.location_id
               LEFT JOIN users u ON u.id = cc.closed_by
               LEFT JOIN tenants t ON t.id = cc.tenant_id
              WHERE cc.id = :id AND cc.tenant_id = :t",
            ['id' => $id, 't' => $tenantId]
        );
    }
}
