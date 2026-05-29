<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Config;

/**
 * Brute-force protection: counts failed login attempts per email+IP within a
 * decay window and locks out after a threshold.
 */
class LoginThrottle
{
    public static function tooManyAttempts(string $email, string $ip): bool
    {
        $max   = (int) Config::get('security.login_max_attempts', 5);
        $decay = (int) Config::get('security.login_decay_minutes', 15);

        $count = (int) Database::scalar(
            "SELECT COUNT(*) FROM login_attempts
              WHERE email = :e AND ip_address = :ip AND success = 0
                AND created_at > DATE_SUB(NOW(), INTERVAL :d MINUTE)",
            ['e' => strtolower($email), 'ip' => $ip, 'd' => $decay]
        );
        return $count >= $max;
    }

    public static function record(string $email, string $ip, bool $success): void
    {
        Database::execute(
            "INSERT INTO login_attempts (email, ip_address, success) VALUES (:e, :ip, :s)",
            ['e' => strtolower($email), 'ip' => $ip, 's' => $success ? 1 : 0]
        );
    }

    /** Clear failed attempts after a successful login. */
    public static function clear(string $email, string $ip): void
    {
        Database::execute(
            "DELETE FROM login_attempts WHERE email = :e AND ip_address = :ip AND success = 0",
            ['e' => strtolower($email), 'ip' => $ip]
        );
    }

    public static function secondsRemaining(string $email, string $ip): int
    {
        $decay = (int) Config::get('security.login_decay_minutes', 15);
        $last = Database::scalar(
            "SELECT created_at FROM login_attempts
              WHERE email = :e AND ip_address = :ip AND success = 0
              ORDER BY id DESC LIMIT 1",
            ['e' => strtolower($email), 'ip' => $ip]
        );
        if (!$last) return 0;
        $elapsed = time() - strtotime($last);
        return max(0, ($decay * 60) - $elapsed);
    }
}
