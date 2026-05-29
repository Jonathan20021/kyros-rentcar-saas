<?php
namespace App\Core;

/**
 * Authentication + authorization helper.
 * Session stores: user_id, tenant_id, role_id, role_slug, name, email, permissions[].
 * Multi-tenant rule: tenantId() is the ONLY source of truth for tenant scoping.
 */
class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $email = strtolower(trim($email));
        $user = Database::selectOne(
            "SELECT u.*, r.slug AS role_slug
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.email = :email AND u.deleted_at IS NULL
              LIMIT 1",
            ['email' => $email]
        );

        if (!$user || $user['status'] !== 'active') {
            return false;
        }
        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Rehash if algorithm/cost changed
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
            Database::execute(
                "UPDATE users SET password = :p WHERE id = :id",
                ['p' => password_hash($password, PASSWORD_BCRYPT), 'id' => $user['id']]
            );
        }

        self::login($user);
        return true;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('auth', [
            'user_id'     => (int) $user['id'],
            'tenant_id'   => $user['tenant_id'] !== null ? (int) $user['tenant_id'] : null,
            'role_id'     => (int) $user['role_id'],
            'role_slug'   => $user['role_slug'] ?? null,
            'name'        => $user['name'],
            'email'       => $user['email'],
            'permissions' => self::loadPermissions((int) $user['role_id'], $user['role_slug'] ?? null),
        ]);
        Database::execute("UPDATE users SET last_login_at = NOW() WHERE id = :id", ['id' => $user['id']]);
    }

    protected static function loadPermissions(int $roleId, ?string $roleSlug): array
    {
        if ($roleSlug === 'super-admin') {
            return ['*'];
        }
        $rows = Database::select(
            "SELECT p.slug FROM role_permissions rp
               JOIN permissions p ON p.id = rp.permission_id
              WHERE rp.role_id = :rid",
            ['rid' => $roleId]
        );
        return array_column($rows, 'slug');
    }

    public static function check(): bool { return Session::has('auth'); }

    public static function user(): ?array { return Session::get('auth'); }

    public static function id(): ?int
    {
        $a = self::user();
        return $a ? $a['user_id'] : null;
    }

    public static function tenantId(): ?int
    {
        $a = self::user();
        return $a ? $a['tenant_id'] : null;
    }

    public static function roleSlug(): ?string
    {
        $a = self::user();
        return $a ? $a['role_slug'] : null;
    }

    public static function isSuperAdmin(): bool
    {
        return self::roleSlug() === 'super-admin';
    }

    public static function can(string $permission): bool
    {
        $a = self::user();
        if (!$a) return false;
        $perms = $a['permissions'] ?? [];
        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
