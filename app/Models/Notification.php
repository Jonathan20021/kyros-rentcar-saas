<?php
namespace App\Models;

use App\Core\Database;

class Notification
{
    public static function create(?int $tenantId, ?int $userId, string $title, string $message, string $type = 'info', ?string $actionUrl = null): void
    {
        Database::execute(
            "INSERT INTO notifications (tenant_id, user_id, title, message, type, action_url)
             VALUES (:t, :u, :title, :msg, :type, :url)",
            ['t' => $tenantId, 'u' => $userId, 'title' => $title, 'msg' => $message, 'type' => $type, 'url' => $actionUrl]
        );
    }

    public static function forTenant(int $tenantId, int $limit = 10): array
    {
        $limit = max(1, min($limit, 50));
        return Database::select(
            "SELECT * FROM notifications WHERE tenant_id = :t ORDER BY created_at DESC LIMIT {$limit}",
            ['t' => $tenantId]
        );
    }

    public static function unreadCount(int $tenantId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM notifications WHERE tenant_id = :t AND is_read = 0",
            ['t' => $tenantId]
        );
    }

    public static function allForTenant(int $tenantId, bool $onlyUnread = false): array
    {
        $sql = "SELECT * FROM notifications WHERE tenant_id = :t" . ($onlyUnread ? " AND is_read = 0" : "") . " ORDER BY created_at DESC LIMIT 200";
        return Database::select($sql, ['t' => $tenantId]);
    }

    public static function markRead(int $tenantId, int $id): void
    {
        Database::execute("UPDATE notifications SET is_read = 1 WHERE id = :id AND tenant_id = :t", ['id' => $id, 't' => $tenantId]);
    }

    public static function markAllRead(int $tenantId): void
    {
        Database::execute("UPDATE notifications SET is_read = 1 WHERE tenant_id = :t AND is_read = 0", ['t' => $tenantId]);
    }

    public static function find(int $tenantId, int $id): ?array
    {
        return Database::selectOne("SELECT * FROM notifications WHERE id = :id AND tenant_id = :t", ['id' => $id, 't' => $tenantId]);
    }
}
