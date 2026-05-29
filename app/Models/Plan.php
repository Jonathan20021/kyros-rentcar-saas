<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Plan extends Model
{
    protected static string $table = 'plans';
    protected static bool $tenantScoped = false;
    protected static bool $softDeletes = false;

    /**
     * Feature flag matrix: key => list of plan slugs that include it.
     * Anything NOT in this matrix is considered available to every plan.
     */
    public const FEATURES = [
        'storefront'      => ['starter','business','premium'],
        'fleet'           => ['starter','business','premium'],
        'reservations'    => ['starter','business','premium'],
        'customers'       => ['starter','business','premium'],
        'catalog'         => ['starter','business','premium'],
        // Business+
        'contracts'       => ['business','premium'],
        'payments'        => ['business','premium'],
        'invoices'        => ['business','premium'],
        'maintenance'     => ['business','premium'],
        'incidents'       => ['business','premium'],
        'expenses'        => ['business','premium'],
        'cashbox'         => ['business','premium'],
        'reports'         => ['business','premium'],
        'multi_location'  => ['business','premium'],
        'drivers'         => ['business','premium'],
        'promos'          => ['business','premium'],
        'email_templates' => ['business','premium'],
        'documents'       => ['business','premium'],
        // Premium only
        'api'             => ['premium'],
        'unlimited_fleet' => ['premium'],
        'unlimited_users' => ['premium'],
        'custom_domain'   => ['premium'],
        'whatsapp_auto'   => ['premium'],
        'advanced_reports'=> ['premium'],
    ];

    public static function publicPlans(): array
    {
        return Database::select(
            "SELECT * FROM plans WHERE status = 'active' AND is_public = 1 ORDER BY price_monthly ASC"
        );
    }

    /** Returns true if the given plan slug includes the feature. */
    public static function planHas(?string $slug, string $feature): bool
    {
        if (!$slug) return false;
        // Unknown feature = open by default (so new modules don't blow up old plans).
        if (!isset(self::FEATURES[$feature])) return true;
        return in_array($slug, self::FEATURES[$feature], true);
    }

    /** Resolve the plan slug for a tenant id. */
    public static function slugForTenant(int $tenantId): ?string
    {
        return Database::scalar(
            "SELECT p.slug FROM plans p JOIN tenants t ON t.plan_id = p.id WHERE t.id = :t LIMIT 1",
            ['t' => $tenantId]
        ) ?: null;
    }

    /** vehicles / users count vs plan max. Returns [count, limit, isUnlimited]. */
    public static function usage(int $tenantId, string $resource): array
    {
        $col = $resource === 'users' ? 'max_users' : 'max_vehicles';
        $plan = Database::selectOne(
            "SELECT p.{$col} AS lim FROM plans p JOIN tenants t ON t.plan_id = p.id WHERE t.id = :t LIMIT 1",
            ['t' => $tenantId]
        );
        $lim = $plan ? (int) $plan['lim'] : 0;
        $count = 0;
        if ($resource === 'users') {
            $count = (int) Database::scalar("SELECT COUNT(*) FROM users WHERE tenant_id = :t AND deleted_at IS NULL", ['t' => $tenantId]);
        } elseif ($resource === 'vehicles') {
            $count = (int) Database::scalar("SELECT COUNT(*) FROM vehicles WHERE tenant_id = :t AND deleted_at IS NULL", ['t' => $tenantId]);
        }
        return [$count, $lim, $lim === -1];
    }

    /** Returns true if the tenant can create one more of the resource. */
    public static function canAdd(int $tenantId, string $resource): bool
    {
        [$count, $limit, $unlim] = self::usage($tenantId, $resource);
        return $unlim || $count < $limit;
    }

    /** Friendly label for plans referenced in upsell UI. */
    public static function labelFor(string $feature): string
    {
        $plans = self::FEATURES[$feature] ?? [];
        if (in_array('business', $plans, true) && !in_array('starter', $plans, true)) return 'Business';
        if ($plans === ['premium']) return 'Premium';
        return 'Starter';
    }
}
