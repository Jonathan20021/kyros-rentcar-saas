<?php
/**
 * KYROS RENT CAR — Demo cleanup CLI.
 * Purges every demo tenant whose 5h window has expired (FK CASCADE handles
 * users, vehicles, customers, contracts, reservations, etc.).
 *
 * Run from cron / Task Scheduler:
 *   * * * * *  C:\xampp\php\php.exe C:\xampp\htdocs\kyros-rentcar-saas\cleanup_demos.php
 *
 * Or call manually:
 *   php cleanup_demos.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo CLI.');
}

require __DIR__ . '/app/bootstrap.php';

$start = microtime(true);
$n = \App\Services\DemoService::sweep();
$ms = (int) ((microtime(true) - $start) * 1000);

$ts = date('Y-m-d H:i:s');
echo "[$ts] purged=$n duration={$ms}ms" . PHP_EOL;
