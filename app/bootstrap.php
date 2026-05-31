<?php
/**
 * Application bootstrap: autoloader, config, error handling, session, security.
 * Included by public/index.php and api entrypoints.
 */

define('KYROS_START', microtime(true));
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config');

// ---------------------------------------------------------------------
// Composer autoload (dompdf, future deps). Optional — falls through to the
// PSR-4 mini-loader below for App\ classes when vendor/ is not present.
// ---------------------------------------------------------------------
$composerAutoload = ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
}

// ---------------------------------------------------------------------
// PSR-4-ish autoloader: App\ => /app/
// ---------------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = APP_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use App\Core\Config;
use App\Core\Env;
use App\Core\Session;
use App\Core\View;
use App\Core\Logger;

// ---------------------------------------------------------------------
// Environment (.env) — loaded before any config so KYROS_ENV / DB_* apply.
// Real environment variables always take precedence over the file.
// ---------------------------------------------------------------------
Env::load(ROOT_PATH . DIRECTORY_SEPARATOR . '.env');

// ---------------------------------------------------------------------
// Config
// ---------------------------------------------------------------------
Config::setPath(CONFIG_PATH);
View::setPath(APP_PATH . DIRECTORY_SEPARATOR . 'Views');

date_default_timezone_set(Config::get('app.timezone', 'America/Santo_Domingo'));

// ---------------------------------------------------------------------
// Error handling — never leak details to users in production
// ---------------------------------------------------------------------
$debug = (bool) Config::get('app.debug', false);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

set_exception_handler(function (\Throwable $e) use ($debug): void {
    Logger::error($e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if ($debug) {
        echo '<pre style="padding:20px;font-family:monospace;color:#b91c1c;">';
        echo 'EXCEPTION: ' . htmlspecialchars($e->getMessage()) . "\n";
        echo $e->getFile() . ':' . $e->getLine() . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        try { View::display('errors/500', ['message' => 'Ocurrio un error inesperado.']); }
        catch (\Throwable $x) { echo '<h1>500</h1><p>Error interno del servidor.</p>'; }
    }
    exit;
});

// ---------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------
require APP_PATH . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR . 'functions.php';

// ---------------------------------------------------------------------
// Security headers
// ---------------------------------------------------------------------
foreach ((array) Config::get('security.headers', []) as $name => $value) {
    header($name . ': ' . $value);
}

// ---------------------------------------------------------------------
// Session
// ---------------------------------------------------------------------
Session::start();
