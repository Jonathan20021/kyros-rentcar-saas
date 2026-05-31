<?php
/**
 * Database configuration.
 *
 * Production credentials are NOT stored here — they live in the project-root
 * `.env` file (git-ignored, uploaded manually to the server). See `.env.example`.
 *
 * Resolution order for each value: real env var / .env  →  local XAMPP default.
 * So local dev needs no .env (falls back to root@127.0.0.1), while production
 * supplies DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS via .env.
 */

// Make this config self-sufficient: load .env even when included by a CLI
// script (e.g. install.php) that doesn't go through app/bootstrap.php.
require_once __DIR__ . '/../app/Core/Env.php';
\App\Core\Env::load(__DIR__ . '/../.env');

$pass = getenv('DB_PASS');

return [
    'driver'   => 'mysql',
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => (int) (getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_NAME') ?: 'kyros_rentcar',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => $pass !== false ? $pass : '',
    'charset'  => 'utf8mb4',
    'collation'=> 'utf8mb4_unicode_ci',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ],
];
