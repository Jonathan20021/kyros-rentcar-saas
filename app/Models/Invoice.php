<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Invoice extends Model
{
    protected static string $table = 'invoices';
    protected static bool $softDeletes = false;

    public const STATUSES = ['draft','issued','paid','void'];

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT i.*, CONCAT(c.first_name,' ',c.last_name) AS customer_name
                  FROM invoices i LEFT JOIN customers c ON c.id = i.customer_id
                 WHERE i.tenant_id = :t";
        $params = ['t' => $tenantId];
        if (!empty($filters['status'])) { $sql .= " AND i.status = :st"; $params['st'] = $filters['status']; }
        $sql .= " ORDER BY i.issue_date DESC, i.id DESC";
        return Database::select($sql, $params);
    }

    public static function nextNumber(int $tenantId): string
    {
        $year = date('Y');
        $max = (int) Database::scalar(
            "SELECT MAX(CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED))
               FROM invoices WHERE tenant_id = :t AND invoice_number LIKE :pfx",
            ['t'=>$tenantId,'pfx'=>"INV-{$year}-%"]
        );
        return sprintf('INV-%s-%04d', $year, $max + 1);
    }

    public static function withItems(int $tenantId, int $id): ?array
    {
        $inv = Database::selectOne("SELECT * FROM invoices WHERE id = :id AND tenant_id = :t", ['id'=>$id,'t'=>$tenantId]);
        if (!$inv) return null;
        $inv['items']    = Database::select("SELECT * FROM invoice_items WHERE invoice_id = :id AND tenant_id = :t ORDER BY id", ['id'=>$id,'t'=>$tenantId]);
        $inv['customer'] = $inv['customer_id'] ? Customer::find((int)$inv['customer_id'], $tenantId) : null;
        $inv['tenant']   = Tenant::find($tenantId, null);
        return $inv;
    }

    public static function monthTotal(int $tenantId): float
    {
        return (float) Database::scalar(
            "SELECT COALESCE(SUM(total),0) FROM invoices WHERE tenant_id=:t AND status IN ('issued','paid')
              AND YEAR(issue_date)=YEAR(CURDATE()) AND MONTH(issue_date)=MONTH(CURDATE())",
            ['t'=>$tenantId]
        );
    }
}
