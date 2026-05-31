<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Model;

/**
 * StorageRequest — a tenant's ask for an extra MB bump on top of their plan.
 *
 * Lifecycle:
 *   pending  → approved | rejected | cancelled
 *
 * Approving sets `tenants.storage_extra_mb += granted_mb` (or = requested_mb
 * if the super admin doesn't override granted_mb).
 */
class StorageRequest extends Model
{
    protected static string $table = 'storage_requests';

    /** Tenant-side: open new request. Caps to one open at a time. */
    public static function submit(int $tenantId, int $requestedMb, ?string $reason, int $userId): ?int
    {
        if ($requestedMb <= 0) return null;
        $hasOpen = Database::scalar(
            "SELECT id FROM storage_requests WHERE tenant_id = :t AND status = 'pending' LIMIT 1",
            ['t' => $tenantId]
        );
        if ($hasOpen) return (int) $hasOpen;
        Database::execute(
            "INSERT INTO storage_requests (tenant_id, requested_mb, reason, status, requested_by)
             VALUES (:t, :mb, :r, 'pending', :u)",
            ['t' => $tenantId, 'mb' => $requestedMb, 'r' => $reason, 'u' => $userId]
        );
        return (int) Database::connection()->lastInsertId();
    }

    /** Currently pending request for this tenant (or null). */
    public static function pendingForTenant(int $tenantId): ?array
    {
        return Database::selectOne(
            "SELECT * FROM storage_requests WHERE tenant_id = :t AND status = 'pending' ORDER BY id DESC LIMIT 1",
            ['t' => $tenantId]
        ) ?: null;
    }

    /** Recent history for this tenant. */
    public static function historyForTenant(int $tenantId, int $limit = 20): array
    {
        return Database::select(
            "SELECT * FROM storage_requests WHERE tenant_id = :t ORDER BY id DESC LIMIT " . max(1, (int) $limit),
            ['t' => $tenantId]
        );
    }

    /** Super admin: open queue across all tenants. */
    public static function queue(): array
    {
        return Database::select(
            "SELECT sr.*, t.name AS tenant_name, t.slug AS tenant_slug, p.name AS plan_name
               FROM storage_requests sr
               JOIN tenants t ON t.id = sr.tenant_id
          LEFT JOIN plans p ON p.id = t.plan_id
              WHERE sr.status = 'pending'
           ORDER BY sr.created_at ASC"
        );
    }

    /** Super admin: approve. Bumps tenant's extra quota. */
    public static function approve(int $id, int $reviewerId, int $grantedMb, ?string $note = null): bool
    {
        $row = Database::selectOne("SELECT * FROM storage_requests WHERE id = :id", ['id' => $id]);
        if (!$row || $row['status'] !== 'pending') return false;
        $grant = max(1, $grantedMb);
        Database::beginTransaction();
        try {
            Database::execute(
                "UPDATE storage_requests
                    SET status='approved', reviewed_by=:u, reviewed_at=NOW(), review_note=:n, granted_mb=:g
                  WHERE id=:id",
                ['u' => $reviewerId, 'n' => $note, 'g' => $grant, 'id' => $id]
            );
            Database::execute(
                "UPDATE tenants SET storage_extra_mb = storage_extra_mb + :g WHERE id = :t",
                ['g' => $grant, 't' => (int) $row['tenant_id']]
            );
            Database::commit();
            return true;
        } catch (\Throwable $e) {
            Database::rollBack();
            return false;
        }
    }

    /** Super admin: reject. */
    public static function reject(int $id, int $reviewerId, ?string $note = null): bool
    {
        return Database::execute(
            "UPDATE storage_requests
                SET status='rejected', reviewed_by=:u, reviewed_at=NOW(), review_note=:n
              WHERE id=:id AND status='pending'",
            ['u' => $reviewerId, 'n' => $note, 'id' => $id]
        ) > 0;
    }

    /** Tenant: cancel their own pending request. */
    public static function cancel(int $id, int $tenantId): bool
    {
        return Database::execute(
            "UPDATE storage_requests
                SET status='cancelled'
              WHERE id=:id AND tenant_id=:t AND status='pending'",
            ['id' => $id, 't' => $tenantId]
        ) > 0;
    }
}
