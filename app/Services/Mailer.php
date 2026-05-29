<?php
namespace App\Services;

use App\Core\Config;
use App\Core\Logger;
use App\Models\Setting;

/**
 * Transactional email via Resend (https://resend.com).
 * Configured from the Super Admin panel (settings table, tenant_id NULL):
 *   mail_enabled, resend_api_key, mail_from_email, mail_from_name.
 * When disabled or unconfigured, messages are written to storage/logs/mail.log
 * so flows never break in local/dev.
 */
class Mailer
{
    public static function enabled(): bool
    {
        return Setting::getPlatform('mail_enabled', '0') === '1' && Setting::getPlatform('resend_api_key', '') !== '';
    }

    /**
     * @param string|array $to
     * @return bool true if Resend accepted the message
     */
    public static function send($to, string $subject, string $html, ?string $replyTo = null): bool
    {
        $recipients = array_values(array_filter(is_array($to) ? $to : [$to]));
        if (empty($recipients)) {
            return false;
        }

        $apiKey   = (string) Setting::getPlatform('resend_api_key', '');
        $fromEmail= (string) Setting::getPlatform('mail_from_email', 'onboarding@resend.dev');
        $fromName = (string) Setting::getPlatform('mail_from_name', 'Kyros Rent Car');
        $from     = $fromName . ' <' . $fromEmail . '>';

        if (!self::enabled()) {
            self::logFallback($recipients, $subject, 'disabled-or-unconfigured');
            return false;
        }

        $payload = ['from' => $from, 'to' => $recipients, 'subject' => $subject, 'html' => $html];
        if ($replyTo) { $payload['reply_to'] = $replyTo; }

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            self::logFallback($recipients, $subject, 'sent');
            return true;
        }
        Logger::error('Resend failed (' . $code . '): ' . substr((string) $resp, 0, 300) . ' ' . $err);
        self::logFallback($recipients, $subject, 'error-' . $code);
        return false;
    }

    /**
     * Render and send a transactional email from a (possibly customized) template.
     * Looks up the tenant's template by code, replaces {{vars}}, wraps in the
     * branded layout, and sends. Returns true if accepted by Resend.
     *
     * @param array $vars  Replacement values (must include 'tenant' for the heading fallback).
     */
    public static function fromTemplate(string $code, string $to, array $tenant, array $vars, ?array $cta = null): bool
    {
        $tpl = \App\Models\EmailTemplate::get((int) ($tenant['id'] ?? 0), $code);
        if (!$tpl || $tpl['status'] !== 'active') {
            return false; // inactive or unknown template => caller may fall back
        }
        $vars = array_merge(['tenant' => $tenant['name'] ?? 'Kyros Rent Car'], $vars);
        $subject = \App\Models\EmailTemplate::render($tpl['subject'], $vars);
        $body    = \App\Models\EmailTemplate::render($tpl['body'], $vars);
        $html    = self::layout($tpl['label'] ?? ($tenant['name'] ?? 'Kyros'), $body, $tenant, $cta);
        return self::send($to, $subject, $html);
    }

    /** Send a test email; returns [ok, message]. */
    public static function test(string $to): array
    {
        if (!self::enabled()) {
            return [false, 'El correo está deshabilitado o sin API key. Guarda la configuración primero.'];
        }
        $html = self::layout('Correo de prueba', '<p>Si ves este mensaje, la integración con <strong>Resend</strong> funciona correctamente. 🎉</p><p style="color:#6b7280">Enviado desde el panel Super Admin de Kyros.</p>');
        $ok = self::send($to, 'Prueba de correo · Kyros Rent Car', $html);
        return [$ok, $ok ? 'Correo de prueba enviado a ' . $to : 'Resend rechazó el envío. Revisa la API key y el remitente verificado.'];
    }

    protected static function logFallback(array $to, string $subject, string $status): void
    {
        $dir = Config::get('app.storage_path') . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $line = sprintf("[%s] %s | to=%s | %s%s", date('Y-m-d H:i:s'), $status, implode(',', $to), $subject, PHP_EOL);
        @file_put_contents($dir . DIRECTORY_SEPARATOR . 'mail.log', $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Branded responsive HTML wrapper (inline styles for email clients).
     * $cta = ['label'=>..., 'url'=>...]
     */
    public static function layout(string $heading, string $bodyHtml, ?array $tenant = null, ?array $cta = null): string
    {
        $brand   = $tenant['primary_color'] ?? '#F23645';
        $name    = $tenant['name'] ?? 'Kyros Rent Car';
        $initial = mb_substr($name, 0, 1);
        $year    = date('Y');

        $ctaHtml = '';
        if ($cta && !empty($cta['url'])) {
            $ctaHtml = '<tr><td style="padding:8px 32px 28px;">'
                . '<a href="' . e($cta['url']) . '" style="display:inline-block;background:' . e($brand) . ';color:#fff;text-decoration:none;font-weight:600;font-size:15px;padding:12px 22px;border-radius:10px;">' . e($cta['label']) . '</a>'
                . '</td></tr>';
        }

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        . '<body style="margin:0;background:#f1f3f7;font-family:Inter,Arial,Helvetica,sans-serif;color:#1c2433;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f3f7;padding:28px 12px;"><tr><td align="center">'
        . '<table role="presentation" width="520" cellpadding="0" cellspacing="0" style="max-width:520px;width:100%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px -12px rgba(16,24,40,.2);">'
        . '<tr><td style="padding:24px 32px;border-bottom:1px solid #eef1f6;">'
        . '<table role="presentation" cellpadding="0" cellspacing="0"><tr>'
        . '<td style="width:38px;height:38px;background:' . e($brand) . ';border-radius:10px;color:#fff;font-weight:800;font-size:18px;text-align:center;vertical-align:middle;line-height:38px;">' . e($initial) . '</td>'
        . '<td style="padding-left:12px;font-weight:800;font-size:18px;color:#1c2433;">' . e($name) . '</td>'
        . '</tr></table></td></tr>'
        . '<tr><td style="padding:28px 32px 8px;"><h1 style="margin:0 0 14px;font-size:21px;color:#1c2433;">' . e($heading) . '</h1>'
        . '<div style="font-size:15px;line-height:1.6;color:#3b4456;">' . $bodyHtml . '</div></td></tr>'
        . $ctaHtml
        . '<tr><td style="padding:20px 32px;background:#fafbfc;border-top:1px solid #eef1f6;color:#9aa3b2;font-size:12px;">'
        . '&copy; ' . $year . ' ' . e($name) . ' · Powered by Kyros Rent Car</td></tr>'
        . '</table></td></tr></table></body></html>';
    }
}
