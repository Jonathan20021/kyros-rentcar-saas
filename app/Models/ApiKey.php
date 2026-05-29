<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

/**
 * Per-tenant API keys. The raw token is shown exactly once at creation;
 * only its SHA-256 hash is stored. Tokens are prefixed `kyro_` for clarity.
 */
class ApiKey extends Model
{
    protected static string $table = 'api_keys';
    protected static bool $softDeletes = false;

    public static function listForTenant(int $tenantId): array
    {
        return Database::select(
            "SELECT * FROM api_keys WHERE tenant_id = :t ORDER BY id DESC",
            ['t' => $tenantId]
        );
    }

    /** Create a key and return [id, rawToken]. The raw token is never stored. */
    public static function issue(int $tenantId, string $name): array
    {
        $raw  = 'kyro_' . bin2hex(random_bytes(24));
        $id   = self::create([
            'tenant_id'  => $tenantId,
            'name'       => $name,
            'token_hash' => hash('sha256', $raw),
            'status'     => 'active',
        ]);
        return [$id, $raw];
    }

    public static function revoke(int $id, int $tenantId): int
    {
        return self::update($id, $tenantId, ['status' => 'revoked']);
    }

    public static function activeCount(int $tenantId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM api_keys WHERE tenant_id = :t AND status = 'active'",
            ['t' => $tenantId]
        );
    }
}
