<?php
namespace App\Models;

use App\Core\Database;

/**
 * NcfSequence — DGII NCF rolling counters for RD legal invoicing.
 *
 * Each tenant can have an active sequence per type (B01, B02, B14, B15, B16).
 * When an invoice is issued for a tenant in RD, the controller calls
 * `consume($tenantId, $type)` which atomically reserves the next number and
 * returns the formatted NCF (e.g. "B0200001234").
 */
class NcfSequence
{
    /** Known DGII types + their human label. */
    public const TYPES = [
        'B01' => 'Crédito Fiscal',
        'B02' => 'Consumidor Final',
        'B14' => 'Régimen Especial',
        'B15' => 'Gubernamental',
        'B16' => 'Exportación',
    ];

    /** All sequences belonging to a tenant. */
    public static function forTenant(int $tenantId): array
    {
        return Database::select(
            "SELECT * FROM ncf_sequences WHERE tenant_id = :t ORDER BY status, ncf_type",
            ['t' => $tenantId]
        );
    }

    /** Active sequence row for one type, or null. */
    public static function activeFor(int $tenantId, string $type): ?array
    {
        return Database::selectOne(
            "SELECT * FROM ncf_sequences
              WHERE tenant_id = :t AND ncf_type = :tp AND status = 'active'
              LIMIT 1",
            ['t' => $tenantId, 'tp' => $type]
        ) ?: null;
    }

    /** Register a new NCF series. */
    public static function add(int $tenantId, string $type, int $start, int $max, ?string $validUntil, ?string $notes = null): ?int
    {
        if (!isset(self::TYPES[$type])) return null;
        if ($start < 1 || $max < $start) return null;
        // Close any other active sequence of the same type first — only one active per type.
        Database::execute(
            "UPDATE ncf_sequences SET status = 'disabled' WHERE tenant_id = :t AND ncf_type = :tp AND status = 'active'",
            ['t' => $tenantId, 'tp' => $type]
        );
        Database::execute(
            "INSERT INTO ncf_sequences (tenant_id, ncf_type, prefix, current_seq, max_seq, valid_until, notes, status)
             VALUES (:t, :tp, :pr, :seq, :mx, :vu, :n, 'active')",
            ['t' => $tenantId, 'tp' => $type, 'pr' => $type, 'seq' => max(0, $start - 1),
             'mx' => $max, 'vu' => $validUntil, 'n' => $notes]
        );
        return (int) Database::connection()->lastInsertId();
    }

    /**
     * Reserve the next NCF for a type. Returns ['ncf' => 'B0200001234', 'type' => 'B02']
     * or null if no usable sequence is available (UI should warn the tenant).
     */
    public static function consume(int $tenantId, string $type): ?array
    {
        $row = self::activeFor($tenantId, $type);
        if (!$row) return null;
        // Expiry check
        if (!empty($row['valid_until']) && strtotime($row['valid_until']) < strtotime(date('Y-m-d'))) {
            Database::execute("UPDATE ncf_sequences SET status = 'expired' WHERE id = :id", ['id' => $row['id']]);
            return null;
        }
        $next = (int)$row['current_seq'] + 1;
        if ($next > (int)$row['max_seq']) {
            Database::execute("UPDATE ncf_sequences SET status = 'exhausted' WHERE id = :id", ['id' => $row['id']]);
            return null;
        }
        Database::execute(
            "UPDATE ncf_sequences SET current_seq = :s WHERE id = :id",
            ['s' => $next, 'id' => $row['id']]
        );
        // DGII format: prefix + 8-digit padded number = 11 chars total.
        $ncf = $row['prefix'] . str_pad((string) $next, 8, '0', STR_PAD_LEFT);
        return ['ncf' => $ncf, 'type' => $type];
    }

    /** Free / remaining count for the UI. */
    public static function remaining(array $seq): int
    {
        return max(0, (int)$seq['max_seq'] - (int)$seq['current_seq']);
    }

    public static function percentUsed(array $seq): float
    {
        $cap = max(1, (int)$seq['max_seq']);
        return min(100.0, ((int)$seq['current_seq'] / $cap) * 100);
    }

    /** Disable a sequence (tenant can stop using it manually). */
    public static function disable(int $tenantId, int $id): bool
    {
        return Database::execute(
            "UPDATE ncf_sequences SET status = 'disabled' WHERE id = :id AND tenant_id = :t",
            ['id' => $id, 't' => $tenantId]
        ) > 0;
    }
}
