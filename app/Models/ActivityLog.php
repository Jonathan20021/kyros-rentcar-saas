<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Auth;

class ActivityLog
{
    /** Record an auditable action for the current user/tenant. */
    public static function record(string $action, ?string $module = null, ?int $entityId = null, ?string $description = null): void
    {
        Database::execute(
            "INSERT INTO activity_logs (tenant_id, user_id, action, module, entity_id, description, ip_address, user_agent)
             VALUES (:tenant_id, :user_id, :action, :module, :entity_id, :description, :ip, :ua)",
            [
                'tenant_id'   => Auth::tenantId(),
                'user_id'     => Auth::id(),
                'action'      => $action,
                'module'      => $module,
                'entity_id'   => $entityId,
                'description' => $description,
                'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
                'ua'          => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]
        );
    }

    public static function recent(int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));
        return Database::select(
            "SELECT a.*, u.name AS user_name, t.name AS tenant_name
               FROM activity_logs a
               LEFT JOIN users u ON u.id = a.user_id
               LEFT JOIN tenants t ON t.id = a.tenant_id
              ORDER BY a.created_at DESC LIMIT {$limit}"
        );
    }
}
