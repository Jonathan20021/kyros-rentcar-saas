<?php
namespace App\Core;

/**
 * Loads and caches configuration arrays from /config.
 * Usage: Config::get('app.url'), Config::get('security.headers').
 */
class Config
{
    protected static array $cache = [];
    protected static string $path;

    public static function setPath(string $path): void
    {
        self::$path = rtrim($path, DIRECTORY_SEPARATOR);
    }

    public static function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $file  = array_shift($parts);

        if (!isset(self::$cache[$file])) {
            $filePath = self::$path . DIRECTORY_SEPARATOR . $file . '.php';
            self::$cache[$file] = is_file($filePath) ? require $filePath : [];
        }

        $value = self::$cache[$file];
        foreach ($parts as $p) {
            if (is_array($value) && array_key_exists($p, $value)) {
                $value = $value[$p];
            } else {
                return $default;
            }
        }
        return $value;
    }
}
