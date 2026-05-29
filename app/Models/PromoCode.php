<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * Promo codes / coupons. Discount can be percentage or fixed amount,
 * with optional date window, minimum purchase, and max usage cap.
 */
class PromoCode extends Model
{
    protected static string $table = 'promo_codes';

    public const TYPES = ['percent' => 'Porcentaje (%)', 'fixed' => 'Monto fijo'];

    public static function listForTenant(int $tenantId, array $filters = []): array
    {
        $sql = "SELECT * FROM promo_codes WHERE tenant_id = :t AND deleted_at IS NULL";
        $p = ['t' => $tenantId];
        if (!empty($filters['status'])) { $sql .= " AND status = :s"; $p['s'] = $filters['status']; }
        if (!empty($filters['search'])) { $sql .= " AND (code LIKE :q OR description LIKE :q)"; $p['q'] = '%'.$filters['search'].'%'; }
        $sql .= " ORDER BY status='active' DESC, created_at DESC";
        return Database::select($sql, $p);
    }

    /** Find by code (case-insensitive) for redemption. Returns null if invalid/expired. */
    public static function findByCode(int $tenantId, string $code): ?array
    {
        $row = Database::selectOne(
            "SELECT * FROM promo_codes
              WHERE tenant_id = :t AND deleted_at IS NULL
                AND UPPER(code) = UPPER(:c) AND status = 'active'
              LIMIT 1",
            ['t' => $tenantId, 'c' => $code]
        );
        return $row ?: null;
    }

    /** True if the promo is currently redeemable (date window + uses left). */
    public static function isUsable(array $promo, float $subtotal): bool
    {
        if (($promo['status'] ?? '') !== 'active') return false;
        $today = date('Y-m-d');
        if (!empty($promo['valid_from']) && $today < $promo['valid_from']) return false;
        if (!empty($promo['valid_to'])   && $today > $promo['valid_to'])   return false;
        if ($promo['max_uses'] !== null && (int) $promo['used_count'] >= (int) $promo['max_uses']) return false;
        if ((float) ($promo['min_amount'] ?? 0) > $subtotal) return false;
        return true;
    }

    /** Compute the discount in money terms for a given subtotal. */
    public static function discountFor(array $promo, float $subtotal): float
    {
        $val = (float) ($promo['discount_value'] ?? 0);
        if ($promo['discount_type'] === 'percent') {
            return round($subtotal * ($val / 100), 2);
        }
        return min(round($val, 2), $subtotal);
    }

    public static function incrementUse(int $id, int $tenantId): void
    {
        Database::execute(
            "UPDATE promo_codes SET used_count = used_count + 1 WHERE id = :id AND tenant_id = :t",
            ['id' => $id, 't' => $tenantId]
        );
    }

    /** Active, public promos for the storefront ribbon. */
    public static function publicForTenant(int $tenantId, int $limit = 3): array
    {
        return Database::select(
            "SELECT code, description, discount_type, discount_value, valid_to
               FROM promo_codes
              WHERE tenant_id = :t AND deleted_at IS NULL AND status = 'active' AND is_public = 1
                AND (valid_from IS NULL OR valid_from <= CURDATE())
                AND (valid_to   IS NULL OR valid_to   >= CURDATE())
                AND (max_uses IS NULL OR used_count < max_uses)
              ORDER BY discount_value DESC
              LIMIT " . max(1, (int) $limit),
            ['t' => $tenantId]
        );
    }
}
