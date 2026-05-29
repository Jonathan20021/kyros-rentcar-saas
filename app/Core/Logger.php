<?php
namespace App\Core;

/**
 * Minimal file logger. Errors go to storage/logs and are never shown to users.
 */
class Logger
{
    protected static function write(string $level, string $message): void
    {
        $dir = Config::get('app.storage_path') . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = sprintf("[%s] %s: %s%s", date('Y-m-d H:i:s'), strtoupper($level), $message, PHP_EOL);
        @file_put_contents($dir . DIRECTORY_SEPARATOR . $level . '.log', $line, FILE_APPEND | LOCK_EX);
    }

    public static function error(string $message): void { self::write('error', $message); }
    public static function info(string $message): void  { self::write('info', $message); }
    public static function warning(string $message): void { self::write('warning', $message); }
}
