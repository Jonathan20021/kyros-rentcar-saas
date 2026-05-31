<?php
/**
 * KYROS RENT CAR — Hourly maintenance sweep.
 *
 * Run every hour (or as often as needed) to keep auto-managed state in sync:
 *   1. Demo tenants past their 5h window are hard-deleted (FK CASCADE).
 *   2. Vehicles parked in `cleaning` for > 24h are auto-promoted to
 *      `available` with an audit-logged transition.
 *
 * Cron / Task Scheduler:
 *   0 * * * *  /usr/bin/php /var/www/kyros-rentcar-saas/cron_sweep.php
 *   (Windows) Task Scheduler → C:\xampp\php\php.exe cron_sweep.php
 *
 * Or call manually:
 *   php cron_sweep.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo CLI.');
}

require __DIR__ . '/app/bootstrap.php';

$ts = date('Y-m-d H:i:s');
$totalMs = 0;

try {
    $start = microtime(true);
    $demosPurged = \App\Services\DemoService::sweep();
    $ms = (int) ((microtime(true) - $start) * 1000);
    $totalMs += $ms;
    echo "[$ts] demos_purged=$demosPurged duration={$ms}ms" . PHP_EOL;
} catch (\Throwable $e) {
    echo "[$ts] demo_sweep ERROR: " . $e->getMessage() . PHP_EOL;
    \App\Core\Logger::error('demo sweep cron: ' . $e->getMessage());
}

try {
    $start = microtime(true);
    $vehicles = \App\Services\VehicleStatusService::sweepCleaning(24);
    $ms = (int) ((microtime(true) - $start) * 1000);
    $totalMs += $ms;
    echo "[$ts] vehicles_cleaning_promoted=$vehicles duration={$ms}ms" . PHP_EOL;
} catch (\Throwable $e) {
    echo "[$ts] cleaning_sweep ERROR: " . $e->getMessage() . PHP_EOL;
    \App\Core\Logger::error('cleaning sweep cron: ' . $e->getMessage());
}

echo "[$ts] total_duration={$totalMs}ms" . PHP_EOL;
