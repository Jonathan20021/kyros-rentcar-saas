<?php
namespace App\Services;

use App\Core\Logger;
use App\Models\Setting;

/**
 * NotificationService — fans out platform-event emails to a configured pool
 * of recipients (typically the founding team / ops inbox).
 *
 * Platform settings consumed:
 *   notify_recipients_registration  — comma-separated emails
 *   notify_recipients_demo          — comma-separated emails
 *   notify_recipients_logins        — comma-separated emails
 *   notify_logins_enabled           — '1'/'0' master switch for login alerts
 *   notify_logins_filter            — 'all' | 'super_only' | 'failed_only'
 *
 * Each public method is idempotent w.r.t. send failures: if there are no
 * recipients OR Mailer is disabled, the call is a no-op (and logged).
 */
class NotificationService
{
    /** Send a "new tenant registered" alert to configured recipients. */
    public static function notifyRegistration(array $tenant, array $owner): void
    {
        $to = self::recipients('notify_recipients_registration');
        if (!$to) return;
        $rows = [
            ['Empresa',        $tenant['name'] ?? '—'],
            ['Slug',           $tenant['slug'] ?? '—'],
            ['Plan',           $tenant['plan_name'] ?? 'Starter'],
            ['Estado',         $tenant['status'] ?? 'pending_approval'],
            ['Email contacto', $tenant['email'] ?? '—'],
            ['Teléfono',       $tenant['phone'] ?? '—'],
            ['Propietario',    ($owner['name'] ?? '') . ' · ' . ($owner['email'] ?? '')],
            ['Registrado el',  date('d/m/Y H:i:s')],
            ['Aprobar en',     abs_url('/super-admin/approvals')],
        ];
        $html = self::renderEventEmail(
            'Nueva empresa registrada en Kyros',
            'Una rent car acaba de crear su cuenta y está esperando activación.',
            '#F23645',
            'building-2',
            $rows,
            ['label' => 'Revisar en panel', 'url' => abs_url('/super-admin/approvals')]
        );
        self::dispatch($to, '[Kyros] Nueva empresa: ' . ($tenant['name'] ?? 'sin nombre'), $html);
    }

    /** Send a "demo redeemed" alert. */
    public static function notifyDemoCreated(array $tenant, array $owner, array $license): void
    {
        $to = self::recipients('notify_recipients_demo');
        if (!$to) return;
        $expires = $tenant['demo_expires_at'] ?? null;
        $rows = [
            ['Empresa demo', $tenant['name'] ?? '—'],
            ['Slug',         $tenant['slug'] ?? '—'],
            ['Usuario',      ($owner['name'] ?? '') . ' · ' . ($owner['email'] ?? '')],
            ['Licencia',     $license['code'] ?? '—'],
            ['Plan demo',    $license['plan_name'] ?? '—'],
            ['Horas válidas',(string)($license['hours_valid'] ?? '—')],
            ['Expira',       $expires ? date('d/m/Y H:i', strtotime($expires)) : '—'],
            ['Creado el',    date('d/m/Y H:i:s')],
        ];
        $html = self::renderEventEmail(
            'Demo redimida',
            'Un usuario acaba de activar una licencia demo.',
            '#6366F1',
            'sparkles',
            $rows
        );
        self::dispatch($to, '[Kyros] Demo activada: ' . ($tenant['name'] ?? 'sin nombre'), $html);
    }

    /**
     * Send a login alert (success or failure). Filter rules:
     *   - 'failed_only' → only when $success === false
     *   - 'super_only'  → only when $user has no tenant_id (super admin)
     *   - 'all'         → every login
     */
    public static function notifyLogin(array $user, string $ip, ?string $userAgent, bool $success): void
    {
        if (Setting::getPlatform('notify_logins_enabled', '0') !== '1') return;
        $to = self::recipients('notify_recipients_logins');
        if (!$to) return;

        $filter = Setting::getPlatform('notify_logins_filter', 'failed_only');
        if ($filter === 'failed_only' && $success) return;
        if ($filter === 'super_only'  && !empty($user['tenant_id'])) return;

        $rows = [
            ['Resultado',  $success ? '✅ Login exitoso' : '⚠️ Intento fallido'],
            ['Usuario',    ($user['name'] ?? '') . ' · ' . ($user['email'] ?? '')],
            ['Tipo',       empty($user['tenant_id']) ? 'Super Admin' : 'Tenant'],
            ['Tenant',     $user['tenant_name'] ?? ($user['tenant_id'] ?? '—')],
            ['IP',         $ip],
            ['User-agent', $userAgent ? mb_strimwidth($userAgent, 0, 140, '…') : '—'],
            ['Cuándo',     date('d/m/Y H:i:s')],
        ];
        $accent = $success ? '#10B981' : '#F59E0B';
        $icon   = $success ? 'log-in' : 'shield-alert';
        $html = self::renderEventEmail(
            $success ? 'Inicio de sesión' : 'Intento de login fallido',
            $success ? 'Un usuario acaba de iniciar sesión en la plataforma.' : 'Se rechazó un intento de login.',
            $accent,
            $icon,
            $rows
        );
        $subject = '[Kyros] ' . ($success ? 'Login' : 'Login fallido') . ' · ' . ($user['email'] ?? 'desconocido');
        self::dispatch($to, $subject, $html);
    }

    // ---------------------------------------------------------------------

    /** Read a comma/semicolon/newline-separated list and return clean emails. */
    private static function recipients(string $settingKey): array
    {
        $raw = (string) Setting::getPlatform($settingKey, '');
        if (trim($raw) === '') return [];
        $parts = preg_split('/[\s,;]+/', $raw) ?: [];
        $out = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') continue;
            if (filter_var($p, FILTER_VALIDATE_EMAIL)) {
                $out[$p] = true; // dedupe via keys
            }
        }
        return array_keys($out);
    }

    /** Fire-and-forget per-recipient send. Each failure is logged but never thrown. */
    private static function dispatch(array $to, string $subject, string $html): void
    {
        if (!Mailer::enabled()) {
            Logger::info('NotificationService: mail disabled, would send to ' . implode(',', $to) . ': ' . $subject);
            return;
        }
        foreach ($to as $addr) {
            try {
                Mailer::send($addr, $subject, $html);
            } catch (\Throwable $e) {
                Logger::error('NotificationService send failed for ' . $addr . ': ' . $e->getMessage());
            }
        }
    }

    /** Build a polished event email body — branded layout + key/value rows. */
    private static function renderEventEmail(string $title, string $intro, string $accent, string $iconKey, array $rows, ?array $cta = null): string
    {
        $kv = '';
        foreach ($rows as $r) {
            [$k, $v] = $r;
            $kv .= '<tr>'
                 . '<td style="padding:8px 0; font-size:12px; color:#6B7280; width:35%; vertical-align:top;">' . htmlspecialchars((string)$k) . '</td>'
                 . '<td style="padding:8px 0; font-size:13px; color:#0B1120; font-weight:600; vertical-align:top; word-break:break-word;">' . htmlspecialchars((string)$v) . '</td>'
                 . '</tr>';
        }
        $body = '<p style="margin:0 0 12px; color:#4B5563; font-size:14px; line-height:1.55;">' . htmlspecialchars($intro) . '</p>'
              . '<table style="width:100%; border-collapse:collapse; margin-top:12px; background:#F9FAFB; border-radius:12px; padding:8px;">'
              . '<tr><td style="padding:14px 18px;"><table style="width:100%; border-collapse:collapse;">' . $kv . '</table></td></tr>'
              . '</table>';
        return Mailer::layout($title, $body, null, $cta);
    }
}
