<?php
/**
 * KYROS RENT CAR - CLI installer.
 * Usage (from project root):
 *   php install.php            # schema + seeders + demo data
 *   php install.php --no-demo  # schema + seeders only
 *
 * Reads credentials from config/database.php (XAMPP defaults: root / no password).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este instalador solo puede ejecutarse por consola: php install.php');
}

$cfg = require __DIR__ . '/config/database.php';
$withDemo = !in_array('--no-demo', $argv, true);

function out(string $msg) { echo $msg . PHP_EOL; }

out('==================================================');
out('  KYROS RENT CAR - Instalador');
out('==================================================');

try {
    // Connect WITHOUT selecting a database (schema.sql creates it).
    $dsn = sprintf('%s:host=%s;port=%d;charset=%s', $cfg['driver'], $cfg['host'], $cfg['port'], $cfg['charset']);
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    out('[ok] Conectado a MySQL en ' . $cfg['host'] . ':' . $cfg['port']);
} catch (PDOException $e) {
    out('[ERROR] No se pudo conectar a MySQL: ' . $e->getMessage());
    out('Revisa config/database.php (usuario/clave) y que MySQL este activo.');
    exit(1);
}

$files = [
    'database/schema.sql'   => 'Esquema de base de datos',
    'database/seeders.sql'  => 'Datos base (planes, roles, permisos, super admin)',
];
if ($withDemo) {
    $files['database/demo_data.sql'] = 'Datos demo (empresa, vehiculos, reservas...)';
}

foreach ($files as $file => $label) {
    $path = __DIR__ . '/' . $file;
    if (!is_file($path)) { out("[skip] No encontrado: $file"); continue; }
    out("[..] Importando $label ...");
    $sql = file_get_contents($path);
    try {
        $pdo->exec($sql);
        out("[ok] $label importado.");
    } catch (PDOException $e) {
        out("[ERROR] Fallo al importar $file: " . $e->getMessage());
        exit(1);
    }
}

// Ensure storage/upload dirs are writable
foreach (['storage/logs','storage/contracts','storage/invoices','storage/documents','storage/temp','public/assets/uploads'] as $dir) {
    $full = __DIR__ . '/' . $dir;
    if (!is_dir($full)) @mkdir($full, 0775, true);
}

out('==================================================');
out('  Instalacion completada.');
out('==================================================');
out('Accede a: ' . (require __DIR__ . '/config/app.php')['url']);
out('');
out('Credenciales demo:');
out('  Super Admin: admin@kyrosrentcar.com / Admin123*');
out('  Rent Car:    owner@demo.com         / Demo123*');
out('');
out('IMPORTANTE: cambia estas credenciales en produccion.');
