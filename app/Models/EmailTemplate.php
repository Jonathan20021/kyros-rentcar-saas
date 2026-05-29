<?php
namespace App\Models;

use App\Core\Database;

/**
 * Transactional email templates, customizable per tenant. Known templates are
 * defined in defaults(); the DB stores per-tenant overrides. Bodies support
 * {{variable}} placeholders replaced at send time.
 */
class EmailTemplate
{
    /** Known templates: code => [label, subject, body_html, variables]. */
    public static function defaults(): array
    {
        return [
            'reservation_received' => [
                'label'   => 'Reserva recibida',
                'desc'    => 'Se envía al cliente cuando solicita una reserva online.',
                'subject' => 'Recibimos tu reserva · {{tenant}}',
                'body'    => "<p>Hola <strong>{{customer}}</strong>,</p>"
                    . "<p>Recibimos tu solicitud de reserva para el <strong>{{vehicle}}</strong>.</p>"
                    . "<p><strong>Código:</strong> {{code}}<br><strong>Desde:</strong> {{start}}<br><strong>Hasta:</strong> {{end}}<br><strong>Total estimado:</strong> {{total}}</p>"
                    . "<p>Te contactaremos pronto para confirmar. ¡Gracias por elegirnos!</p>",
                'vars'    => ['tenant','customer','vehicle','code','start','end','total'],
            ],
            'reservation_confirmed' => [
                'label'   => 'Reserva confirmada',
                'desc'    => 'Se envía cuando confirmas una reserva.',
                'subject' => 'Tu reserva {{code}} está confirmada · {{tenant}}',
                'body'    => "<p>Hola <strong>{{customer}}</strong>,</p>"
                    . "<p>¡Buenas noticias! Tu reserva <strong>{{code}}</strong> para el <strong>{{vehicle}}</strong> fue <strong>confirmada</strong>.</p>"
                    . "<p><strong>Retiro:</strong> {{start}}<br><strong>Devolución:</strong> {{end}}</p>"
                    . "<p>Te esperamos.</p>",
                'vars'    => ['tenant','customer','vehicle','code','start','end'],
            ],
            'contract_generated' => [
                'label'   => 'Contrato generado',
                'desc'    => 'Se envía al cliente cuando se crea el contrato de alquiler.',
                'subject' => 'Tu contrato {{code}} · {{tenant}}',
                'body'    => "<p>Hola <strong>{{customer}}</strong>,</p>"
                    . "<p>Tu contrato de alquiler <strong>{{code}}</strong> para el <strong>{{vehicle}}</strong> ha sido generado.</p>"
                    . "<p><strong>Total:</strong> {{total}}<br><strong>Balance pendiente:</strong> {{balance}}</p>"
                    . "<p>Gracias por tu confianza.</p>",
                'vars'    => ['tenant','customer','vehicle','code','total','balance'],
            ],
            'payment_received' => [
                'label'   => 'Pago recibido',
                'desc'    => 'Comprobante enviado al registrar un pago.',
                'subject' => 'Recibimos tu pago · {{tenant}}',
                'body'    => "<p>Hola <strong>{{customer}}</strong>,</p>"
                    . "<p>Confirmamos la recepción de tu pago de <strong>{{amount}}</strong> ({{method}}).</p>"
                    . "<p><strong>Recibo:</strong> {{code}}<br><strong>Fecha:</strong> {{date}}</p>"
                    . "<p>¡Gracias!</p>",
                'vars'    => ['tenant','customer','amount','method','code','date'],
            ],
            'team_invite' => [
                'label'   => 'Invitación al equipo',
                'desc'    => 'Credenciales enviadas al agregar un usuario al equipo.',
                'subject' => 'Te invitaron a {{tenant}} en Kyros',
                'body'    => "<p>Hola <strong>{{name}}</strong>,</p>"
                    . "<p>Fuiste agregado al equipo de <strong>{{tenant}}</strong>.</p>"
                    . "<p><strong>Correo:</strong> {{email}}<br><strong>Contraseña:</strong> {{password}}</p>"
                    . "<p>Te recomendamos cambiarla al iniciar sesión.</p>",
                'vars'    => ['tenant','name','email','password'],
            ],
        ];
    }

    /** Merge defaults with tenant DB overrides for the management screen. */
    public static function listForTenant(int $tenantId): array
    {
        $overrides = [];
        foreach (Database::select(
            "SELECT code, subject, body_html, status FROM email_templates WHERE tenant_id = :t",
            ['t' => $tenantId]
        ) as $row) { $overrides[$row['code']] = $row; }

        $out = [];
        foreach (self::defaults() as $code => $def) {
            $ov = $overrides[$code] ?? null;
            $out[$code] = [
                'code'         => $code,
                'label'        => $def['label'],
                'desc'         => $def['desc'],
                'subject'      => $ov['subject'] ?? $def['subject'],
                'body'         => $ov['body_html'] ?? $def['body'],
                'vars'         => $def['vars'],
                'status'       => $ov['status'] ?? 'active',
                'customized'   => $ov !== null,
            ];
        }
        return $out;
    }

    public static function get(int $tenantId, string $code): ?array
    {
        $all = self::listForTenant($tenantId);
        return $all[$code] ?? null;
    }

    /** Upsert a tenant override (settings table has unique key on tenant+code? no — manual upsert). */
    public static function save(int $tenantId, string $code, string $subject, string $body, string $status): void
    {
        $exists = Database::scalar(
            "SELECT id FROM email_templates WHERE tenant_id = :t AND code = :c LIMIT 1",
            ['t' => $tenantId, 'c' => $code]
        );
        if ($exists) {
            Database::execute(
                "UPDATE email_templates SET subject = :s, body_html = :b, status = :st, updated_at = NOW() WHERE id = :id",
                ['s' => $subject, 'b' => $body, 'st' => $status, 'id' => (int) $exists]
            );
        } else {
            Database::execute(
                "INSERT INTO email_templates (tenant_id, code, subject, body_html, status) VALUES (:t, :c, :s, :b, :st)",
                ['t' => $tenantId, 'c' => $code, 's' => $subject, 'b' => $body, 'st' => $status]
            );
        }
    }

    /** Remove a tenant override (revert to default). */
    public static function reset(int $tenantId, string $code): void
    {
        Database::execute("DELETE FROM email_templates WHERE tenant_id = :t AND code = :c", ['t' => $tenantId, 'c' => $code]);
    }

    /** Replace {{vars}} in a string. */
    public static function render(string $template, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function ($m) use ($vars) {
            return isset($vars[$m[1]]) ? (string) $vars[$m[1]] : '';
        }, $template);
    }
}
