<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Logger;

/**
 * VehicleStatusService — automated lifecycle transitions for a vehicle's
 * `status` field, with full audit trail in `vehicle_status_log`.
 *
 * Drive points:
 *   - Contract.start  → 'rented'
 *   - Contract.finish → 'cleaning' (24h grace) then 'available'
 *   - Manual change   → via UI
 *   - Maintenance.start → 'maintenance'
 *
 * Always go through this service when programmatically changing status so
 * the audit log stays accurate.
 */
class VehicleStatusService
{
    /** Atomically transition a vehicle. Returns true on success. */
    public static function transition(
        int $tenantId,
        int $vehicleId,
        string $toStatus,
        string $source,
        ?int $sourceId = null,
        ?int $performedBy = null,
        ?string $note = null
    ): bool {
        $cur = Database::scalar(
            "SELECT status FROM vehicles WHERE id = :v AND tenant_id = :t",
            ['v' => $vehicleId, 't' => $tenantId]
        );
        if ($cur === null) return false;
        if ($cur === $toStatus) return true; // no-op

        Database::beginTransaction();
        try {
            Database::execute(
                "UPDATE vehicles SET status = :s WHERE id = :v AND tenant_id = :t",
                ['s' => $toStatus, 'v' => $vehicleId, 't' => $tenantId]
            );
            Database::execute(
                "INSERT INTO vehicle_status_log (tenant_id, vehicle_id, from_status, to_status, source, source_id, performed_by, note)
                 VALUES (:t, :v, :fs, :ts, :src, :sid, :u, :n)",
                ['t' => $tenantId, 'v' => $vehicleId, 'fs' => $cur, 'ts' => $toStatus,
                 'src' => $source, 'sid' => $sourceId, 'u' => $performedBy, 'n' => $note]
            );
            Database::commit();
            return true;
        } catch (\Throwable $e) {
            Database::rollBack();
            Logger::error('VehicleStatusService transition failed: ' . $e->getMessage());
            return false;
        }
    }

    /** Contract just became active — mark the vehicle rented. */
    public static function onContractStart(int $tenantId, int $contractId, int $vehicleId, ?int $userId = null): void
    {
        self::transition($tenantId, $vehicleId, 'rented', 'contract.start', $contractId, $userId,
            'Contrato activado · vehículo en alquiler');
    }

    /** Contract closed/finished — vehicle goes to cleaning, then available. */
    public static function onContractFinish(int $tenantId, int $contractId, int $vehicleId, ?int $userId = null): void
    {
        self::transition($tenantId, $vehicleId, 'cleaning', 'contract.finish', $contractId, $userId,
            'Contrato cerrado · vehículo en limpieza');
    }

    /**
     * Sweep: any vehicle that has been in 'cleaning' for > $hours hours
     * gets auto-promoted to 'available'. Call from a cron / opportunistic hook.
     * Returns the number of vehicles moved.
     */
    public static function sweepCleaning(int $hours = 24): int
    {
        $rows = Database::select(
            "SELECT v.id, v.tenant_id FROM vehicles v
              JOIN vehicle_status_log l ON l.vehicle_id = v.id
              WHERE v.status = 'cleaning'
                AND l.to_status = 'cleaning'
                AND l.created_at <= DATE_SUB(NOW(), INTERVAL :h HOUR)
              GROUP BY v.id, v.tenant_id"
            , ['h' => $hours]
        );
        $n = 0;
        foreach ($rows as $r) {
            if (self::transition((int)$r['tenant_id'], (int)$r['id'], 'available', 'auto.cleaning_done', null, null,
                'Limpieza completada (auto)')) {
                $n++;
            }
        }
        return $n;
    }

    /** Maintenance started for a vehicle. */
    public static function onMaintenanceStart(int $tenantId, int $maintenanceId, int $vehicleId, ?int $userId = null): void
    {
        self::transition($tenantId, $vehicleId, 'maintenance', 'maintenance.start', $maintenanceId, $userId);
    }

    /** Maintenance finished — back to available. */
    public static function onMaintenanceFinish(int $tenantId, int $maintenanceId, int $vehicleId, ?int $userId = null): void
    {
        self::transition($tenantId, $vehicleId, 'available', 'maintenance.finish', $maintenanceId, $userId);
    }

    /** Recent transitions for a vehicle (audit panel). */
    public static function recentForVehicle(int $tenantId, int $vehicleId, int $limit = 20): array
    {
        return Database::select(
            "SELECT * FROM vehicle_status_log
              WHERE tenant_id = :t AND vehicle_id = :v
              ORDER BY id DESC
              LIMIT " . max(1, (int) $limit),
            ['t' => $tenantId, 'v' => $vehicleId]
        );
    }
}
