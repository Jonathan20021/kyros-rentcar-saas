<?php
namespace App\Core;

/**
 * Minimal .env loader (no external dependency).
 *
 * Parses KEY=VALUE lines and exposes them through getenv()/$_ENV/$_SERVER.
 * Rules:
 *   - Lines starting with '#' (after trim) are comments.
 *   - Blank lines are ignored.
 *   - The value is everything after the first '='; surrounding single or
 *     double quotes are stripped. No inline comments — so secrets containing
 *     '#' (e.g. passwords) are preserved verbatim.
 *   - Real environment variables already set take precedence over the file,
 *     so server-level config can always override .env.
 */
class Env
{
    protected static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded || !is_file($path) || !is_readable($path)) {
            self::$loaded = true;
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip matching surrounding quotes.
            $len = strlen($value);
            if ($len >= 2 && (($value[0] === '"' && $value[$len - 1] === '"') ||
                              ($value[0] === "'" && $value[$len - 1] === "'"))) {
                $value = substr($value, 1, -1);
            }

            if ($key === '' || getenv($key) !== false) {
                continue; // never override a real environment variable
            }

            putenv("$key=$value");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }

        self::$loaded = true;
    }
}
