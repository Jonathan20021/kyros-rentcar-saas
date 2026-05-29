<?php
/**
 * KYROS RENT CAR - REST API v1.
 *
 * Auth: per-tenant Bearer token. Tokens are stored as SHA-256 hashes in
 * `api_keys.token_hash`; the raw token (prefix `kyro_`) is shown once at
 * creation. Every query is tenant-scoped, exactly like the web app.
 *
 *   Authorization: Bearer kyro_xxxxxxxx
 *
 * Endpoints:
 *   GET  /                       API info + rate context
 *   GET  /vehicles               list fleet
 *   GET  /vehicles/{id}          vehicle detail
 *   GET  /customers              list customers
 *   GET  /reservations           list reservations
 *   GET  /reservations/{id}      reservation detail
 *   POST /reservations           create a reservation (pending)
 *   GET  /contracts              list contracts
 *   GET  /payments               list payments
 */

require dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Logger;
use App\Models\Vehicle;
use App\Models\Reservation;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('X-Content-Type-Options: nosniff');

function api_json($data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function api_error(string $msg, int $status = 400, array $extra = []): void
{
    api_json(array_merge(['ok' => false, 'error' => $msg], $extra), $status);
}

if (strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---- Resolve tenant from Bearer token --------------------------------
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
if (!preg_match('/Bearer\s+(.+)/i', $authHeader, $m)) {
    api_error('Token requerido (Authorization: Bearer <token>)', 401);
}
$tokenHash = hash('sha256', trim($m[1]));
$key = Database::selectOne(
    "SELECT * FROM api_keys WHERE token_hash = :h AND status = 'active' LIMIT 1",
    ['h' => $tokenHash]
);
if (!$key) {
    api_error('Token invalido o revocado', 401);
}
$tenantId = (int) $key['tenant_id'];
Database::execute("UPDATE api_keys SET last_used_at = NOW() WHERE id = :id", ['id' => $key['id']]);

// ---- Resolve path ----------------------------------------------------
$base = '/api/v1';
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$pos  = strpos($uri, $base);
$path = '/' . trim($pos !== false ? substr($uri, $pos + strlen($base)) : $uri, '/');
$method = strtoupper($_SERVER['REQUEST_METHOD']);

/** Clamp a ?limit param to a sane range. */
$limit = (int) ($_GET['limit'] ?? 50);
$limit = max(1, min($limit, 200));

function body_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) return $_POST ?: [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : ($_POST ?: []);
}

try {
    // ---- Info -------------------------------------------------------
    if ($method === 'GET' && $path === '/') {
        api_json([
            'ok' => true,
            'api' => 'Kyros Rent Car API',
            'version' => 'v1',
            'tenant_id' => $tenantId,
            'endpoints' => ['/vehicles','/vehicles/{id}','/customers','/reservations','/reservations/{id}','POST /reservations','/contracts','/payments'],
        ]);
    }

    // ---- Vehicles ---------------------------------------------------
    if ($method === 'GET' && $path === '/vehicles') {
        api_json(['ok' => true, 'data' => Database::select(
            "SELECT id, brand, model, year, slug, plate_number, daily_price, status
               FROM vehicles WHERE tenant_id = :t AND deleted_at IS NULL
              ORDER BY id DESC LIMIT {$limit}",
            ['t' => $tenantId]
        )]);
    }

    if ($method === 'GET' && preg_match('#^/vehicles/(\d+)$#', $path, $mm)) {
        $row = Database::selectOne(
            "SELECT * FROM vehicles WHERE id = :id AND tenant_id = :t AND deleted_at IS NULL",
            ['id' => (int) $mm[1], 't' => $tenantId]
        );
        $row ? api_json(['ok' => true, 'data' => $row]) : api_error('Vehículo no encontrado', 404);
    }

    // ---- Customers --------------------------------------------------
    if ($method === 'GET' && $path === '/customers') {
        api_json(['ok' => true, 'data' => Database::select(
            "SELECT id, first_name, last_name, phone, email, document_number, status
               FROM customers WHERE tenant_id = :t AND deleted_at IS NULL
              ORDER BY id DESC LIMIT {$limit}",
            ['t' => $tenantId]
        )]);
    }

    // ---- Reservations -----------------------------------------------
    if ($method === 'GET' && $path === '/reservations') {
        api_json(['ok' => true, 'data' => Database::select(
            "SELECT id, reservation_code, vehicle_id, customer_id, lead_name,
                    start_datetime, end_datetime, status, total_amount
               FROM reservations WHERE tenant_id = :t AND deleted_at IS NULL
              ORDER BY id DESC LIMIT {$limit}",
            ['t' => $tenantId]
        )]);
    }

    if ($method === 'GET' && preg_match('#^/reservations/(\d+)$#', $path, $mm)) {
        $row = Database::selectOne(
            "SELECT r.*, v.brand, v.model, v.plate_number,
                    COALESCE(CONCAT(c.first_name,' ',c.last_name), r.lead_name) AS customer_name
               FROM reservations r JOIN vehicles v ON v.id = r.vehicle_id
               LEFT JOIN customers c ON c.id = r.customer_id
              WHERE r.id = :id AND r.tenant_id = :t AND r.deleted_at IS NULL",
            ['id' => (int) $mm[1], 't' => $tenantId]
        );
        $row ? api_json(['ok' => true, 'data' => $row]) : api_error('Reserva no encontrada', 404);
    }

    if ($method === 'POST' && $path === '/reservations') {
        $b = body_json();
        $vehicleId = (int) ($b['vehicle_id'] ?? 0);
        $start = trim((string) ($b['start_datetime'] ?? ''));
        $end   = trim((string) ($b['end_datetime'] ?? ''));
        $leadName  = trim((string) ($b['lead_name'] ?? ''));
        $leadPhone = trim((string) ($b['lead_phone'] ?? ''));
        $leadEmail = trim((string) ($b['lead_email'] ?? ''));

        $errors = [];
        if ($vehicleId <= 0) $errors['vehicle_id'] = 'Requerido';
        $ts = strtotime($start); $te = strtotime($end);
        if (!$ts) $errors['start_datetime'] = 'Fecha inválida (YYYY-MM-DD HH:MM:SS)';
        if (!$te) $errors['end_datetime']   = 'Fecha inválida (YYYY-MM-DD HH:MM:SS)';
        if ($ts && $te && $te <= $ts) $errors['end_datetime'] = 'Debe ser posterior al inicio';
        if ($leadName === '') $errors['lead_name'] = 'Requerido';
        if ($errors) api_error('Datos inválidos', 422, ['fields' => $errors]);

        $vehicle = Vehicle::find($vehicleId, $tenantId);
        if (!$vehicle) api_error('Vehículo no encontrado', 404);

        $startSql = date('Y-m-d H:i:s', $ts);
        $endSql   = date('Y-m-d H:i:s', $te);
        if (!Vehicle::isAvailable($tenantId, $vehicleId, $startSql, $endSql)) {
            api_error('El vehículo no está disponible en ese rango', 409);
        }

        $days     = max(1, (int) ceil(($te - $ts) / 86400));
        $rate     = (float) $vehicle['daily_price'];
        $subtotal = round($days * $rate, 2);
        $taxRate  = (float) Database::scalar("SELECT tax_rate FROM tenants WHERE id = :t", ['t' => $tenantId]);
        $tax      = round($subtotal * ($taxRate / 100), 2);
        $total    = round($subtotal + $tax, 2);
        $code     = Reservation::nextCode($tenantId);

        $id = Reservation::create([
            'tenant_id'      => $tenantId,
            'reservation_code' => $code,
            'vehicle_id'     => $vehicleId,
            'customer_id'    => null,
            'lead_name'      => mb_substr($leadName, 0, 150),
            'lead_phone'     => mb_substr($leadPhone, 0, 30),
            'lead_email'     => mb_substr($leadEmail, 0, 150),
            'start_datetime' => $startSql,
            'end_datetime'   => $endSql,
            'daily_rate'     => $rate,
            'days_count'     => $days,
            'subtotal'       => $subtotal,
            'tax_amount'     => $tax,
            'deposit_amount' => (float) ($vehicle['deposit_amount'] ?? 0),
            'total_amount'   => $total,
            'status'         => 'pending',
            'source'         => 'api',
        ]);

        Database::execute(
            "INSERT INTO activity_logs (tenant_id, user_id, action, module, entity_id, description, ip_address, user_agent)
             VALUES (:t, NULL, 'created', 'reservations', :e, :d, :ip, 'API v1')",
            ['t' => $tenantId, 'e' => $id, 'd' => 'Reserva vía API: ' . $code, 'ip' => $_SERVER['REMOTE_ADDR'] ?? null]
        );
        api_json(['ok' => true, 'data' => [
            'id' => $id, 'reservation_code' => $code, 'status' => 'pending',
            'days' => $days, 'subtotal' => $subtotal, 'tax_amount' => $tax, 'total_amount' => $total,
        ]], 201);
    }

    // ---- Contracts --------------------------------------------------
    if ($method === 'GET' && $path === '/contracts') {
        api_json(['ok' => true, 'data' => Database::select(
            "SELECT id, contract_number, vehicle_id, customer_id, start_datetime, end_datetime,
                    status, total_amount, balance_due
               FROM contracts WHERE tenant_id = :t AND deleted_at IS NULL
              ORDER BY id DESC LIMIT {$limit}",
            ['t' => $tenantId]
        )]);
    }

    // ---- Payments ---------------------------------------------------
    if ($method === 'GET' && $path === '/payments') {
        api_json(['ok' => true, 'data' => Database::select(
            "SELECT id, payment_code, contract_id, customer_id, amount, method, status, payment_date
               FROM payments WHERE tenant_id = :t
              ORDER BY id DESC LIMIT {$limit}",
            ['t' => $tenantId]
        )]);
    }

    api_error('Endpoint no encontrado: ' . $method . ' ' . $path, 404);
} catch (\Throwable $e) {
    Logger::error('API error: ' . $e->getMessage());
    api_error('Error interno', 500);
}
