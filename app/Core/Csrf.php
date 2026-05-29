<?php
namespace App\Core;

/**
 * CSRF token generation + verification (synchronizer token pattern).
 */
class Csrf
{
    public static function token(): string
    {
        $name = Config::get('security.csrf_token_name', '_csrf');
        if (empty($_SESSION[$name])) {
            $_SESSION[$name] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$name];
    }

    public static function field(): string
    {
        $name = Config::get('security.csrf_token_name', '_csrf');
        return '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    public static function verify(?string $token): bool
    {
        $name    = Config::get('security.csrf_token_name', '_csrf');
        $session = $_SESSION[$name] ?? '';
        return is_string($token) && $token !== '' && hash_equals($session, $token);
    }

    /** Validate the CSRF token from the current request, abort on failure. */
    public static function check(): void
    {
        if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $name  = Config::get('security.csrf_token_name', '_csrf');
            $token = $_POST[$name] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!self::verify($token)) {
                Logger::warning('CSRF validation failed from IP ' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
                http_response_code(419);
                exit('Sesion expirada o token CSRF invalido. Recarga la pagina e intenta de nuevo.');
            }
        }
    }
}
