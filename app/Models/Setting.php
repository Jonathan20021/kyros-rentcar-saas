<?php
namespace App\Models;

use App\Core\Database;

/**
 * Key/value settings. Platform settings use tenant_id IS NULL.
 * (ON DUPLICATE KEY can't be used reliably with NULL tenant_id, so we
 *  do an explicit exists-check upsert.)
 */
class Setting
{
    public static function getPlatform(string $key, $default = null)
    {
        $v = Database::scalar(
            "SELECT value FROM settings WHERE tenant_id IS NULL AND key_name = :k LIMIT 1",
            ['k' => $key]
        );
        return $v === false || $v === null ? $default : $v;
    }

    public static function allPlatform(): array
    {
        $rows = Database::select("SELECT key_name, value FROM settings WHERE tenant_id IS NULL");
        $out = [];
        foreach ($rows as $r) { $out[$r['key_name']] = $r['value']; }
        return $out;
    }

    public static function setPlatform(string $key, ?string $value): void
    {
        $exists = Database::scalar("SELECT id FROM settings WHERE tenant_id IS NULL AND key_name = :k LIMIT 1", ['k' => $key]);
        if ($exists) {
            Database::execute("UPDATE settings SET value = :v WHERE id = :id", ['v' => $value, 'id' => $exists]);
        } else {
            Database::execute("INSERT INTO settings (tenant_id, key_name, value) VALUES (NULL, :k, :v)", ['k' => $key, 'v' => $value]);
        }
    }
}
