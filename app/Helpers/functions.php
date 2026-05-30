<?php
/**
 * Global helper functions.
 */

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Auth;

if (!function_exists('e')) {
    /** HTML-escape output (XSS protection). */
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /** Build an absolute app URL from a path. */
    function url(string $path = '/'): string
    {
        $base = rtrim(Config::get('app.base_path', ''), '/');
        $path = '/' . ltrim($path, '/');
        return $base . $path;
    }
}

if (!function_exists('abs_url')) {
    /** Absolute URL (scheme+host+path) for use in emails. */
    function abs_url(string $path = '/'): string
    {
        $base = rtrim((string) Config::get('app.url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /** Build a URL to a public asset. */
    function asset(string $path): string
    {
        return url('/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string { return Csrf::field(); }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string { return Csrf::token(); }
}

if (!function_exists('old')) {
    function old(string $key, $default = ''): string
    {
        return e(Session::old($key, $default));
    }
}

if (!function_exists('auth')) {
    function auth(): ?array { return Auth::user(); }
}

if (!function_exists('can')) {
    function can(string $permission): bool { return Auth::can($permission); }
}

if (!function_exists('money')) {
    function money($amount): string
    {
        $symbol = Config::get('app.currency_symbol', 'RD$');
        return $symbol . ' ' . number_format((float) $amount, 2);
    }
}

if (!function_exists('slugify')) {
    /** Convert a string into a URL-safe slug. */
    function slugify(string $text): string
    {
        $text = (string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text !== '' ? $text : 'item';
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $datetime, string $format = 'd/m/Y'): string
    {
        if (!$datetime) return '-';
        $ts = strtotime($datetime);
        return $ts ? date($format, $ts) : '-';
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $datetime): string
    {
        return format_date($datetime, 'd/m/Y h:i A');
    }
}

if (!function_exists('status_badge')) {
    /**
     * Map a status string to Tailwind badge classes.
     */
    function status_badge(string $status): string
    {
        $map = [
            // generic
            'active'    => 'bg-emerald-100 text-emerald-700',
            'inactive'  => 'bg-slate-100 text-slate-600',
            'trial'     => 'bg-amber-100 text-amber-700',
            'suspended' => 'bg-red-100 text-red-700',
            'blocked'   => 'bg-red-100 text-red-700',
            'blacklist' => 'bg-red-100 text-red-700',
            'pending'   => 'bg-amber-100 text-amber-700',
            // vehicles
            'available'      => 'bg-emerald-100 text-emerald-700',
            'reserved'       => 'bg-indigo-100 text-indigo-700',
            'rented'         => 'bg-blue-100 text-blue-700',
            'maintenance'    => 'bg-amber-100 text-amber-700',
            'out_of_service' => 'bg-red-100 text-red-700',
            'cleaning'       => 'bg-cyan-100 text-cyan-700',
            // reservations / contracts
            'confirmed'   => 'bg-emerald-100 text-emerald-700',
            'rejected'    => 'bg-red-100 text-red-700',
            'cancelled'   => 'bg-slate-100 text-slate-600',
            'in_progress' => 'bg-blue-100 text-blue-700',
            'converted'   => 'bg-violet-100 text-violet-700',
            'finished'    => 'bg-slate-100 text-slate-600',
            'draft'       => 'bg-slate-100 text-slate-600',
            'overdue'     => 'bg-red-100 text-red-700',
            'claim'       => 'bg-amber-100 text-amber-700',
            // payments
            'paid'      => 'bg-emerald-100 text-emerald-700',
            'partial'   => 'bg-amber-100 text-amber-700',
            'refunded'  => 'bg-cyan-100 text-cyan-700',
            'voided'    => 'bg-slate-100 text-slate-600',
        ];
        return $map[$status] ?? 'bg-slate-100 text-slate-600';
    }
}

if (!function_exists('status_label')) {
    function status_label(string $status): string
    {
        $map = [
            'available'=>'Disponible','reserved'=>'Reservado','rented'=>'Rentado',
            'maintenance'=>'Mantenimiento','out_of_service'=>'Fuera de servicio','cleaning'=>'Limpieza',
            'pending_delivery'=>'Pend. entrega','pending_return'=>'Pend. devolucion',
            'pending'=>'Pendiente','confirmed'=>'Confirmada','rejected'=>'Rechazada','cancelled'=>'Cancelada',
            'in_progress'=>'En proceso','converted'=>'Convertida','finished'=>'Finalizada',
            'draft'=>'Borrador','active'=>'Activo','overdue'=>'En mora','claim'=>'Reclamacion',
            'paid'=>'Pagado','partial'=>'Parcial','refunded'=>'Reembolsado','voided'=>'Anulado',
            'trial'=>'Prueba','suspended'=>'Suspendida','inactive'=>'Inactiva',
            'blocked'=>'Bloqueado','blacklist'=>'Lista negra',
        ];
        return $map[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
}

if (!function_exists('whatsapp_link')) {
    function whatsapp_link(?string $number, string $message = ''): string
    {
        $num = preg_replace('/\D+/', '', (string) $number);
        return 'https://wa.me/' . $num . ($message ? '?text=' . rawurlencode($message) : '');
    }
}

if (!function_exists('strftime_es')) {
    /** Current date/time in Spanish, e.g. "jue 29 may · 01:48 PM". */
    function strftime_es(): string
    {
        $days   = ['Sun'=>'dom','Mon'=>'lun','Tue'=>'mar','Wed'=>'mie','Thu'=>'jue','Fri'=>'vie','Sat'=>'sab'];
        $months = ['Jan'=>'ene','Feb'=>'feb','Mar'=>'mar','Apr'=>'abr','May'=>'may','Jun'=>'jun','Jul'=>'jul','Aug'=>'ago','Sep'=>'sep','Oct'=>'oct','Nov'=>'nov','Dec'=>'dic'];
        $d = $days[date('D')] ?? date('D');
        $mo = $months[date('M')] ?? date('M');
        return sprintf('%s %s %s · %s', $d, date('j'), $mo, date('h:i A'));
    }
}

if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $a = mb_substr($parts[0] ?? '', 0, 1);
        $b = mb_substr($parts[1] ?? '', 0, 1);
        return strtoupper($a . $b);
    }
}

if (!function_exists('media')) {
    /**
     * Build a browser-loadable URL for a stored upload path.
     * - Returns '' if $path is empty/null
     * - Returns the path unchanged if it's already absolute (http(s)://)
     * - Otherwise prepends the app base_path so /assets/... works under XAMPP
     *   sub-folder installs (e.g. /kyros-rentcar-saas/public/assets/...).
     */
    function media(?string $path): string
    {
        if (!$path) return '';
        if (preg_match('#^https?://#i', $path)) return $path;
        return url($path);
    }
}

if (!function_exists('plan_has')) {
    /** Does the current tenant's plan include the given feature key? */
    function plan_has(string $feature): bool
    {
        $tid = Auth::tenantId();
        if (!$tid) return false;
        static $cache = [];
        if (!isset($cache[$tid])) {
            $cache[$tid] = \App\Models\Plan::slugForTenant($tid);
        }
        return \App\Models\Plan::planHas($cache[$tid], $feature);
    }
}

if (!function_exists('plan_upgrade_required')) {
    /** Friendly plan name a tenant needs to upgrade to in order to unlock a feature. */
    function plan_upgrade_required(string $feature): string
    {
        return \App\Models\Plan::labelFor($feature);
    }
}
