<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model
{
    protected static string $table = 'users';
    protected static bool $tenantScoped = false; // users can be system-level

    public static function findByEmail(string $email, ?int $tenantId): ?array
    {
        if ($tenantId === null) {
            return Database::selectOne(
                "SELECT * FROM users WHERE email = :e AND tenant_id IS NULL AND deleted_at IS NULL LIMIT 1",
                ['e' => $email]
            );
        }
        return Database::selectOne(
            "SELECT * FROM users WHERE email = :e AND tenant_id = :t AND deleted_at IS NULL LIMIT 1",
            ['e' => $email, 't' => $tenantId]
        );
    }

    public static function emailExists(string $email, ?int $tenantId): bool
    {
        return self::findByEmail($email, $tenantId) !== null;
    }

    public static function forTenant(int $tenantId): array
    {
        return Database::select(
            "SELECT u.*, r.name AS role_name FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.tenant_id = :t AND u.deleted_at IS NULL
              ORDER BY u.created_at DESC",
            ['t' => $tenantId]
        );
    }

    public static function countForTenant(int $tenantId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM users WHERE tenant_id = :t AND deleted_at IS NULL",
            ['t' => $tenantId]
        );
    }

    public static function allSystem(): array
    {
        return Database::select(
            "SELECT u.*, r.name AS role_name, t.name AS tenant_name
               FROM users u
               JOIN roles r ON r.id = u.role_id
               LEFT JOIN tenants t ON t.id = u.tenant_id
              WHERE u.deleted_at IS NULL
              ORDER BY u.created_at DESC"
        );
    }
}
