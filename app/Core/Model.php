<?php
namespace App\Core;

/**
 * Base model. Tenant-scoped helpers enforce tenant_id on every query to
 * prevent IDOR / cross-tenant data leaks. Models that are tenant-scoped set
 * $tenantScoped = true; system tables (plans, roles) set it false.
 */
abstract class Model
{
    protected static string $table = '';
    protected static bool $tenantScoped = true;
    protected static bool $softDeletes = true;

    protected static function table(): string { return static::$table; }

    /** Build the "tenant_id = ?" guard, returning [sqlFragment, params]. */
    protected static function tenantGuard(?int $tenantId): array
    {
        if (!static::$tenantScoped) {
            return ['1=1', []];
        }
        if ($tenantId === null) {
            // No tenant context on a tenant-scoped table => match nothing.
            return ['1=0', []];
        }
        return ['tenant_id = :__tenant', ['__tenant' => $tenantId]];
    }

    protected static function softGuard(): string
    {
        return static::$softDeletes ? ' AND deleted_at IS NULL' : '';
    }

    /** Find one row by id, scoped to tenant. */
    public static function find(int $id, ?int $tenantId): ?array
    {
        [$g, $p] = static::tenantGuard($tenantId);
        $p['id'] = $id;
        $sql = "SELECT * FROM " . static::table() . " WHERE id = :id AND {$g}" . static::softGuard() . " LIMIT 1";
        return Database::selectOne($sql, $p);
    }

    /** Find one row by id or abort 404 (tenant-scoped). */
    public static function findOrFail(int $id, ?int $tenantId): array
    {
        $row = static::find($id, $tenantId);
        if (!$row) {
            http_response_code(404);
            View::display('errors/404', ['message' => 'Recurso no encontrado']);
            exit;
        }
        return $row;
    }

    public static function all(?int $tenantId, string $orderBy = 'id DESC'): array
    {
        [$g, $p] = static::tenantGuard($tenantId);
        $sql = "SELECT * FROM " . static::table() . " WHERE {$g}" . static::softGuard() . " ORDER BY {$orderBy}";
        return Database::select($sql, $p);
    }

    public static function create(array $data): int
    {
        $cols = array_keys($data);
        $place = array_map(fn($c) => ':' . $c, $cols);
        $sql = "INSERT INTO " . static::table() . " (" . implode(',', $cols) . ") VALUES (" . implode(',', $place) . ")";
        return Database::insert($sql, $data);
    }

    /** Update by id within tenant scope. Returns affected rows. */
    public static function update(int $id, ?int $tenantId, array $data): int
    {
        [$g, $p] = static::tenantGuard($tenantId);
        $sets = [];
        foreach ($data as $col => $val) {
            $sets[] = "{$col} = :{$col}";
            $p[$col] = $val;
        }
        $p['id'] = $id;
        $sql = "UPDATE " . static::table() . " SET " . implode(',', $sets) . " WHERE id = :id AND {$g}";
        return Database::execute($sql, $p);
    }

    /** Soft delete (or hard delete if soft deletes disabled). */
    public static function delete(int $id, ?int $tenantId): int
    {
        [$g, $p] = static::tenantGuard($tenantId);
        $p['id'] = $id;
        if (static::$softDeletes) {
            $sql = "UPDATE " . static::table() . " SET deleted_at = NOW() WHERE id = :id AND {$g}";
        } else {
            $sql = "DELETE FROM " . static::table() . " WHERE id = :id AND {$g}";
        }
        return Database::execute($sql, $p);
    }

    public static function count(?int $tenantId, string $where = '', array $params = []): int
    {
        [$g, $p] = static::tenantGuard($tenantId);
        $p = array_merge($p, $params);
        $extra = $where !== '' ? " AND ({$where})" : '';
        $sql = "SELECT COUNT(*) FROM " . static::table() . " WHERE {$g}" . static::softGuard() . $extra;
        return (int) Database::scalar($sql, $p);
    }
}
