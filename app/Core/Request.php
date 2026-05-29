<?php
namespace App\Core;

/**
 * Request abstraction over PHP superglobals.
 */
class Request
{
    protected array $params = []; // route params

    public function method(): string
    {
        $m = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Method spoofing via _method for forms
        if ($m === 'POST' && isset($_POST['_method'])) {
            $spoof = strtoupper($_POST['_method']);
            if (in_array($spoof, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $spoof;
            }
        }
        return $m;
    }

    public function uri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = Config::get('app.base_path', '');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . ltrim($uri, '/');
        return rtrim($uri, '/') ?: '/';
    }

    public function isPost(): bool { return $this->method() === 'POST'; }
    public function isGet(): bool  { return $this->method() === 'GET'; }

    public function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /** Trimmed string input. */
    public function str(string $key, string $default = ''): string
    {
        $v = $this->input($key, $default);
        return is_string($v) ? trim($v) : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $v = $this->input($key, $default);
        return is_numeric($v) ? (int) $v : $default;
    }

    public function float(string $key, float $default = 0.0): float
    {
        $v = $this->input($key, $default);
        return is_numeric($v) ? (float) $v : $default;
    }

    public function all(): array { return array_merge($_GET, $_POST); }
    public function only(array $keys): array
    {
        $out = [];
        foreach ($keys as $k) { $out[$k] = $this->input($k); }
        return $out;
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public function setParams(array $params): void { $this->params = $params; }
    public function param(string $key, $default = null) { return $this->params[$key] ?? $default; }

    public function ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
    public function userAgent(): string { return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255); }
    public function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xrw    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || strtolower($xrw) === 'xmlhttprequest';
    }
}
