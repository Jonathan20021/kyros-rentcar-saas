<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Extra extends Model
{
    protected static string $table = 'extras';
    protected static bool $softDeletes = false;

    public const CHARGE_TYPES = ['per_day' => 'Por día', 'per_reservation' => 'Por reserva', 'one_time' => 'Único'];

    public static function activeForTenant(int $tenantId): array
    {
        return Database::select(
            "SELECT * FROM extras WHERE tenant_id = :t AND status = 'active' ORDER BY name",
            ['t' => $tenantId]
        );
    }

    public static function listWithUsage(int $tenantId): array
    {
        return Database::select(
            "SELECT e.*, (SELECT COUNT(*) FROM reservation_extras re WHERE re.extra_id = e.id) AS used_count
               FROM extras e
              WHERE e.tenant_id = :t
              ORDER BY e.status = 'active' DESC, e.name",
            ['t' => $tenantId]
        );
    }
}
