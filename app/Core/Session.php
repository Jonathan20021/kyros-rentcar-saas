<?php
namespace App\Core;

/**
 * Secure session manager + flash messages.
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $name     = Config::get('security.session_name', 'KYROS_SESSID');
        $lifetime = (int) Config::get('security.session_lifetime', 7200);

        session_name($name);
        session_set_cookie_params([
            'lifetime' => 0, // session cookie; absolute lifetime enforced below
            'path'     => '/',
            'domain'   => '',
            'secure'   => (bool) Config::get('security.cookie_secure', false),
            'httponly' => (bool) Config::get('security.cookie_httponly', true),
            'samesite' => Config::get('security.cookie_samesite', 'Lax'),
        ]);

        session_start();

        // Idle-timeout enforcement
        $now = time();
        if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity']) > $lifetime) {
            self::destroy();
            session_start();
        }
        $_SESSION['_last_activity'] = $now;

        // Periodic id rotation to mitigate fixation
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = $now;
        } elseif ($now - $_SESSION['_created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_created'] = $now;
        }
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
    }

    public static function set(string $key, $value): void { $_SESSION[$key] = $value; }
    public static function get(string $key, $default = null) { return $_SESSION[$key] ?? $default; }
    public static function has(string $key): bool { return isset($_SESSION[$key]); }
    public static function forget(string $key): void { unset($_SESSION[$key]); }

    /** Read a value once and remove it. */
    public static function pull(string $key, $default = null)
    {
        $val = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $val;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // ---- Flash messages -------------------------------------------------
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    public static function getFlashes(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    // ---- Old input (form repopulation) ---------------------------------
    public static function flashInput(array $input): void
    {
        // strip sensitive keys
        unset($input['password'], $input['password_confirmation'], $input['_csrf']);
        $_SESSION['_old'] = $input;
    }

    public static function old(string $key, $default = '')
    {
        return $_SESSION['_old'][$key] ?? $default;
    }

    public static function clearOld(): void
    {
        unset($_SESSION['_old']);
    }
}
