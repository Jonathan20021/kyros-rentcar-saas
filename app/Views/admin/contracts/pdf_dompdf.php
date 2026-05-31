<?php
/**
 * Contract PDF — single-page A4, premium edition.
 * Pure table-based layout. Logo + signature embedded as raster (needs GD).
 * When GD is missing we fall back to an SVG glyph with the tenant initial,
 * so the PDF still shows the brand. QR is always SVG (no GD needed).
 */
use App\Services\PdfService;

$v    = $c['vehicle'];
$cu   = $c['customer'];
$t    = $c['tenant'];
$custName  = trim($cu['first_name'] . ' ' . ($cu['last_name'] ?? ''));
$tName     = $t['name'] ?? 'Rent car';
$brand     = $t['primary_color'] ?? '#F23645';
$brandSoft = pdf_mix_white($brand, .92);
$brandDeep = pdf_mix_navy($brand, .35);
$ink       = '#0B1120';
$muted     = '#5A6377';
$line      = '#E6EAF1';
$paper     = '#F7F9FC';
$signed    = !empty($c['signed_at']);
$days = max(1, (int) ceil((strtotime($c['end_datetime']) - strtotime($c['start_datetime'])) / 86400));

$statusLabels = [
  'draft' => 'Borrador', 'active' => 'Activo', 'finished' => 'Finalizado',
  'cancelled' => 'Cancelado', 'overdue' => 'Atrasado', 'claim' => 'Reclamo',
];
$statusText = $statusLabels[$c['status']] ?? '—';

/* ----------- Logo: tenant raster if available, else SVG fallback ----------- */
$logoData = !empty($t['logo']) ? PdfService::embedImage($t['logo']) : '';
$hasLogo  = $logoData !== '';
if (!$logoData) {
    $logoData = PdfService::brandSvgDataUri($tName, $brand, '#FFFFFF', 96);
}
$sigData  = !empty($c['customer_signature']) ? PdfService::embedImage($c['customer_signature']) : '';

$shareUrl = !empty($c['share_token']) ? abs_url('/contrato/' . $c['share_token']) : null;
$qrSrc    = $shareUrl ? PdfService::qrSvgDataUri($shareUrl, $ink) : '';

/* color helpers (pdf_mix_white / pdf_mix_navy live in app/Helpers/functions.php
   so every PDF template can call them without redeclaring). */

/* Inclusions — what's covered with the rental (fills space + adds clarity) */
$inclusions = [
  ['label' => 'Kilometraje', 'value' => 'Libre durante el período'],
  ['label' => 'Asistencia 24/7', 'value' => 'Cobertura nacional'],
  ['label' => 'Mantenimiento',   'value' => 'A cargo del arrendador'],
  ['label' => 'Seguro básico',   'value' => 'Daños a terceros'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Contrato <?= e($c['contract_number']) ?></title>
<style>
  @page { margin: 0; size: A4; }
  body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; color: <?= $ink ?>; font-size: 9px; line-height: 1.4; }
  table { border-collapse: collapse; }
  .eyebrow { font-size: 8px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: #9aa3b2; }
  .muted   { color: <?= $muted ?>; }
  .strong  { color: <?= $ink ?>; font-weight: 700; }
  .right   { text-align: right; }

  /* ---- Hero ---- */
  .hero { background: <?= $brand ?>; color:#fff; padding: 14px 32px 16px; }
  .hero .name   { font-size: 15.5px; font-weight: 700; line-height: 1.15; letter-spacing: -.2px; }
  .hero .legal  { font-size: 8px; opacity: .88; margin-top: 1px; }
  .hero .number { font-size: 22px; font-weight: 700; letter-spacing: -.8px; }
  .hero .label-r{ font-size: 8.5px; font-weight: 700; letter-spacing: 1.8px; text-transform: uppercase; opacity: .82; }
  .pill {
    display: inline-block; padding: 3px 10px; border-radius: 99px;
    font-size: 8.5px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
  }
  .pill-status { background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.32); color: #fff; }
  .pill-signed { background: #10B981; color: #fff; }

  /* ---- KPI strip (just under hero) ---- */
  .kpi-band { background: <?= $brandDeep ?>; color: #fff; padding: 11px 32px; }
  .kpi-cell { padding: 0 14px; border-left: 1px solid rgba(255,255,255,.16); }
  .kpi-cell:first-child { border-left: 0; padding-left: 0; }
  .kpi-label { font-size: 7px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; opacity: .68; }
  .kpi-value { font-size: 15.5px; font-weight: 700; letter-spacing: -.4px; margin-top: 3px; line-height: 1.15; }
  .kpi-sub   { font-size: 7.5px; opacity: .62; margin-top: 1px; }

  /* ---- Body ---- */
  .doc { padding: 11px 32px 8px; }

  /* ---- Section heading + card ---- */
  .sec-h  { font-size: 8px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: <?= $muted ?>; margin-bottom: 4px; }
  .card   { border:1px solid <?= $line ?>; border-radius: 10px; background: #fff; }
  .card-paper { border:1px solid <?= $line ?>; border-radius: 10px; background: <?= $paper ?>; }

  /* ---- Tables ---- */
  .k-table { width: 100%; }
  .k-table td { padding: 5px 13px; border-bottom: 1px solid #EFF1F5; }
  .k-table tr:last-child td { border-bottom: 0; }
  .k-table thead td {
    background: <?= $paper ?>; color: <?= $muted ?>; font-weight: 700;
    font-size: 7px; text-transform: uppercase; letter-spacing: 1.3px;
    border-bottom: 1px solid <?= $line ?>; padding: 5px 13px;
  }

  /* ---- Pricing rows ---- */
  .total-row td { background: #0E1422; color: #fff; font-size: 10px; font-weight: 700; padding: 7px 13px; border: 0; }
  .total-row td.amount { font-size: 12.5px; letter-spacing: -.2px; }
  .pay-row td { color: #047857; font-weight: 700; }
  .balance-row td {
    background: <?= $brandSoft ?>; color: <?= $brand ?>; font-weight: 700;
    font-size: 10px; padding: 7px 13px; border-top: 1px solid <?= pdf_mix_white($brand, .75) ?>;
  }
  .balance-row td.amount { font-size: 13px; letter-spacing: -.2px; }

  /* ---- Signature ---- */
  .sig-line { border-top: 1.5px solid <?= $ink ?>; padding-top: 5px; }
  .sig-name { font-size: 10px; font-weight: 700; color: <?= $ink ?>; letter-spacing: -.1px; }
  .sig-role { font-size: 8px; color: <?= $muted ?>; margin-top: 1px; }
  .audit { display:inline-block; margin-top: 4px; padding: 2px 6px; border-radius: 4px; background:#ECFDF5; color:#047857; font-size: 7.5px; font-weight: 700; }

  /* ---- Spacing ---- */
  .section-gap { margin-bottom: 6px; }
</style>
</head>
<body>

<!-- ============================== HERO ============================== -->
<div class="hero">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td valign="middle" width="60%">
        <table cellpadding="0" cellspacing="0">
          <tr>
            <td valign="middle" width="62">
              <img src="<?= e($logoData) ?>" style="width:52px; height:52px; border-radius:12px; <?= $hasLogo ? 'background:#fff; padding:3px;' : '' ?>" alt="">
            </td>
            <td valign="middle" style="padding-left: 14px;">
              <div class="name"><?= e($tName) ?></div>
              <?php if (!empty($t['rnc']) || !empty($t['phone']) || !empty($t['email'])): ?>
                <div class="legal">
                  <?php
                    $bits = [];
                    if (!empty($t['rnc']))   $bits[] = 'RNC ' . $t['rnc'];
                    if (!empty($t['phone'])) $bits[] = $t['phone'];
                    if (!empty($t['email'])) $bits[] = $t['email'];
                    echo e(implode(' · ', $bits));
                  ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($t['address'])): ?>
                <div class="legal"><?= e($t['address']) ?></div>
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </td>
      <td valign="middle" align="right" width="40%">
        <div class="label-r">Contrato de alquiler</div>
        <div class="number" style="margin-top: 4px;"><?= e($c['contract_number']) ?></div>
        <div style="margin-top: 7px;">
          <span class="pill pill-status"><?= e(strtoupper($statusText)) ?></span>
          <?php if ($signed): ?>
            <span class="pill pill-signed" style="margin-left:4px;">✓ FIRMADO</span>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>
</div>

<!-- ============================== KPI STRIP ============================== -->
<div class="kpi-band">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td class="kpi-cell" width="25%">
        <div class="kpi-label">Duración</div>
        <div class="kpi-value"><?= $days ?> días</div>
        <div class="kpi-sub"><?= e(date('d/m', strtotime($c['start_datetime']))) ?> → <?= e(date('d/m/Y', strtotime($c['end_datetime']))) ?></div>
      </td>
      <td class="kpi-cell" width="25%">
        <div class="kpi-label">Tarifa diaria</div>
        <div class="kpi-value"><?= money($c['daily_rate']) ?></div>
        <div class="kpi-sub">Vehículo · <?= e($v['brand']) ?></div>
      </td>
      <td class="kpi-cell" width="25%">
        <div class="kpi-label">Total contrato</div>
        <div class="kpi-value"><?= money($c['total_amount']) ?></div>
        <div class="kpi-sub">Impuestos incluidos</div>
      </td>
      <td class="kpi-cell" width="25%">
        <div class="kpi-label">Balance pendiente</div>
        <div class="kpi-value" style="color: <?= ((float)$c['balance_due'] > 0) ? '#FDE68A' : '#A7F3D0' ?>;"><?= money($c['balance_due']) ?></div>
        <div class="kpi-sub">Pagado: <?= money($c['paid_amount'] ?? 0) ?></div>
      </td>
    </tr>
  </table>
</div>

<div class="doc">

  <!-- ============================== PERIOD + PARTIES ============================== -->
  <table width="100%" cellpadding="0" cellspacing="0" class="section-gap">
    <tr>
      <td valign="top" width="36%">
        <div class="sec-h">Período del alquiler</div>
        <div class="card-paper" style="padding:9px 13px;">
          <table width="100%">
            <tr>
              <td style="font-size:9px;">
                <div class="eyebrow">Entrega</div>
                <div class="strong" style="font-size:11px; margin-top:2px;"><?= format_date($c['start_datetime']) ?></div>
                <div class="muted" style="font-size:8.5px;"><?= e(date('H:i', strtotime($c['start_datetime']))) ?> h</div>
              </td>
              <td align="center" width="14%" style="color:<?= $brand ?>; font-size:15px;">→</td>
              <td style="font-size:9px;">
                <div class="eyebrow">Devolución</div>
                <div class="strong" style="font-size:11px; margin-top:2px;"><?= format_date($c['end_datetime']) ?></div>
                <div class="muted" style="font-size:8.5px;"><?= e(date('H:i', strtotime($c['end_datetime']))) ?> h</div>
              </td>
            </tr>
          </table>
          <table width="100%" style="margin-top:7px; padding-top:7px; border-top:1px solid <?= $line ?>;">
            <tr>
              <td class="muted" style="font-size:8.5px;">Duración total</td>
              <td align="right" style="color:<?= $brand ?>; font-size:15px; font-weight:700; letter-spacing:-.5px;"><?= $days ?> <span style="font-size:8.5px; font-weight:600;">días</span></td>
            </tr>
          </table>
        </div>
      </td>
      <td width="2%"></td>
      <td valign="top" width="31%">
        <div class="sec-h">Arrendatario · Cliente</div>
        <div class="card" style="padding:9px 13px;">
          <div class="strong" style="font-size:10.5px; letter-spacing:-.2px;"><?= e($custName ?: '—') ?></div>
          <div class="muted" style="font-size:9px; margin-top:2px;">Conductor principal autorizado</div>
          <table style="margin-top:5px; font-size:8.5px; line-height:1.45;">
            <?php if (!empty($cu['document_number'])): ?>
              <tr><td class="muted" style="padding-right:12px; padding-bottom:3px;">Documento</td><td class="strong" style="padding-bottom:3px;"><?= e($cu['document_number']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($cu['phone'])): ?>
              <tr><td class="muted" style="padding-right:12px; padding-bottom:3px;">Teléfono</td><td class="strong" style="padding-bottom:3px;"><?= e($cu['phone']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($cu['email'])): ?>
              <tr><td class="muted" style="padding-right:12px; padding-bottom:3px;">Email</td><td class="strong" style="padding-bottom:3px;"><?= e($cu['email']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($cu['license_number'])): ?>
              <tr><td class="muted" style="padding-right:12px; padding-bottom:3px;">Licencia</td><td class="strong" style="padding-bottom:3px;"><?= e($cu['license_number']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($cu['address'])): ?>
              <tr><td class="muted" valign="top" style="padding-right:12px;">Dirección</td><td class="strong"><?= e($cu['address']) ?></td></tr>
            <?php endif; ?>
          </table>
        </div>
      </td>
      <td width="2%"></td>
      <td valign="top" width="29%">
        <div class="sec-h">Vehículo arrendado</div>
        <div class="card" style="padding:9px 13px;">
          <div class="strong" style="font-size:10.5px; letter-spacing:-.2px;">
            <?= e($v['brand'] . ' ' . $v['model']) ?>
          </div>
          <div class="muted" style="font-size:9px; margin-top:2px;">
            <?php if (!empty($v['version'])): ?><?= e($v['version']) ?> · <?php endif; ?><?= e($v['year']) ?>
          </div>
          <table style="margin-top:5px; font-size:8.5px; line-height:1.45;">
            <?php if (!empty($v['plate_number'])): ?>
              <tr><td class="muted" style="padding-right:10px; padding-bottom:3px;">Placa</td><td class="strong" style="padding-bottom:3px;"><?= e($v['plate_number']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($v['vin'])): ?>
              <tr><td class="muted" style="padding-right:10px; padding-bottom:3px;">VIN</td><td class="strong" style="padding-bottom:3px;"><?= e($v['vin']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($v['color'])): ?>
              <tr><td class="muted" style="padding-right:10px; padding-bottom:3px;">Color</td><td class="strong" style="padding-bottom:3px;"><?= e($v['color']) ?></td></tr>
            <?php endif; ?>
            <tr><td class="muted" style="padding-right:10px; padding-bottom:3px;">Trans.</td><td class="strong" style="padding-bottom:3px;"><?= $v['transmission'] === 'automatic' ? 'Automática' : 'Manual' ?></td></tr>
            <tr><td class="muted" style="padding-right:10px; padding-bottom:3px;">Combust.</td><td class="strong" style="padding-bottom:3px;"><?= ucfirst($v['fuel_type']) ?></td></tr>
            <?php if (!empty($v['passengers'])): ?>
              <tr><td class="muted" style="padding-right:10px;">Capacidad</td><td class="strong"><?= (int) $v['passengers'] ?> pasajeros</td></tr>
            <?php endif; ?>
          </table>
        </div>
      </td>
    </tr>
  </table>

  <!-- ============================== HANDOVER ============================== -->
  <div class="sec-h">Estado del vehículo · Inspección</div>
  <table class="k-table section-gap card" width="100%">
    <thead>
      <tr>
        <td>Concepto</td><td class="right">Entrega</td><td class="right">Devolución</td><td class="right">Diferencia</td>
      </tr>
    </thead>
    <tbody>
      <?php
      $sm = $c['start_mileage'] !== null ? (int) $c['start_mileage'] : null;
      $em = $c['end_mileage']   !== null ? (int) $c['end_mileage']   : null;
      $sf = $c['start_fuel_level'] !== null ? (int) $c['start_fuel_level'] : null;
      $ef = $c['end_fuel_level']   !== null ? (int) $c['end_fuel_level']   : null;
      ?>
      <tr>
        <td><span class="strong">Kilometraje</span><div class="muted" style="font-size:8px;">Lectura del odómetro</div></td>
        <td class="right"><?= $sm !== null ? number_format($sm) . ' km' : '—' ?></td>
        <td class="right"><?= $em !== null ? number_format($em) . ' km' : '—' ?></td>
        <td class="right" style="color:<?= $brand ?>; font-weight:700;">
          <?= ($sm !== null && $em !== null) ? '+ ' . number_format(max(0, $em - $sm)) . ' km' : '—' ?>
        </td>
      </tr>
      <tr>
        <td><span class="strong">Nivel de combustible</span><div class="muted" style="font-size:8px;">% del tanque</div></td>
        <td class="right"><?= $sf !== null ? $sf . ' %' : '—' ?></td>
        <td class="right"><?= $ef !== null ? $ef . ' %' : '—' ?></td>
        <td class="right" style="font-weight:700; color:<?= ($sf !== null && $ef !== null && $ef < $sf) ? '#F59E0B' : '#10B981' ?>;">
          <?= ($sf !== null && $ef !== null) ? ($ef >= $sf ? '+ ' : '− ') . abs($ef - $sf) . ' %' : '—' ?>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- ============================== PRICING (full width) ============================== -->
  <div class="sec-h">Desglose económico</div>
  <table class="k-table section-gap card" width="100%">
    <thead>
      <tr>
        <td width="48%">Concepto</td><td width="32%" class="right">Detalle</td><td width="20%" class="right">Monto</td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><span class="strong">Alquiler del vehículo</span><div class="muted" style="font-size:8px;"><?= e($v['brand'] . ' ' . $v['model']) ?></div></td>
        <td class="right muted"><?= money($c['daily_rate']) ?> × <?= $days ?> días</td>
        <td class="right strong"><?= money($c['subtotal']) ?></td>
      </tr>
      <?php if ((float) $c['extras_total'] > 0): ?>
      <tr><td><span class="strong">Servicios adicionales</span><div class="muted" style="font-size:8px;">Extras contratados</div></td><td></td><td class="right strong"><?= money($c['extras_total']) ?></td></tr>
      <?php endif; ?>
      <?php if (!empty($c['discount_amount']) && (float) $c['discount_amount'] > 0): ?>
      <tr><td style="color:#047857;"><span class="strong" style="color:#047857;">Descuento aplicado</span></td><td></td><td class="right" style="color:#047857; font-weight:700;">− <?= money($c['discount_amount']) ?></td></tr>
      <?php endif; ?>
      <?php if ((float) $c['penalties_total'] > 0): ?>
      <tr><td style="color:#D97706;"><span class="strong" style="color:#D97706;">Penalidades / cargos</span></td><td></td><td class="right" style="color:#D97706; font-weight:700;">+ <?= money($c['penalties_total']) ?></td></tr>
      <?php endif; ?>
      <tr><td><span class="strong">Impuesto (ITBIS)</span><div class="muted" style="font-size:8px;">18 % sobre el subtotal</div></td><td></td><td class="right strong"><?= money($c['tax_amount']) ?></td></tr>
      <tr class="total-row">
        <td>TOTAL DEL CONTRATO</td><td class="right" style="opacity:.7; font-size:8.5px; letter-spacing:1px;">IMPUESTOS INCLUIDOS</td><td class="amount right"><?= money($c['total_amount']) ?></td>
      </tr>
      <?php if ((float) $c['paid_amount'] > 0): ?>
      <tr class="pay-row"><td>Pagado a la fecha</td><td></td><td class="right">− <?= money($c['paid_amount']) ?></td></tr>
      <?php endif; ?>
      <tr class="balance-row">
        <td>Balance pendiente al cierre</td><td class="right" style="font-size:8.5px; opacity:.85;">PAGAR ANTES DE LA DEVOLUCIÓN</td><td class="amount right"><?= money($c['balance_due']) ?></td>
      </tr>
    </tbody>
  </table>

  <!-- ============================== INCLUSIONS + TERMS (50/50) ============================== -->
  <table width="100%" cellpadding="0" cellspacing="0" class="section-gap">
    <tr>
      <!-- Inclusions / Deposit -->
      <td valign="top" width="46%">
        <div class="sec-h">Incluye este alquiler</div>
        <div class="card" style="padding:8px 12px;">
          <table width="100%" style="font-size:8.5px; line-height:1.4;">
            <?php foreach ($inclusions as $i => $inc): ?>
              <tr>
                <td width="48%" style="padding: 2px 0; <?= $i < count($inclusions)-1 ? 'border-bottom:1px solid #F1F3F7;' : '' ?>">
                  <span style="color:#10B981; font-weight:700;">✓</span>
                  <span class="strong" style="margin-left:4px;"><?= e($inc['label']) ?></span>
                </td>
                <td class="muted right" style="padding: 2px 0; <?= $i < count($inclusions)-1 ? 'border-bottom:1px solid #F1F3F7;' : '' ?>">
                  <?= e($inc['value']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </table>
        </div>
        <?php if ((float) $c['deposit_amount'] > 0): ?>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 6px;">
          <tr>
            <td style="padding: 7px 12px; background:<?= $brandSoft ?>; border:1px dashed <?= pdf_mix_white($brand, .6) ?>; border-radius:8px;">
              <table width="100%"><tr>
                <td style="font-size:8.5px;">
                  <div class="eyebrow" style="color:<?= $brand ?>; opacity:.85;">Depósito en garantía</div>
                  <div class="muted" style="font-size:8px; margin-top:1px;">Reembolsable tras la inspección a la devolución.</div>
                </td>
                <td align="right" valign="middle" style="color:<?= $brand ?>; font-size:13.5px; font-weight:700; letter-spacing:-.3px;">
                  <?= money($c['deposit_amount']) ?>
                </td>
              </tr></table>
            </td>
          </tr>
        </table>
        <?php endif; ?>
      </td>
      <td width="3%"></td>
      <!-- Terms -->
      <td valign="top" width="51%">
        <div class="sec-h">Términos y condiciones</div>
        <div class="card" style="padding:9px 13px 9px 12px; border-left: 3px solid <?= $brand ?>;">
          <p style="font-size:8px; color:<?= $muted ?>; line-height:1.55; text-align:justify; margin:0; white-space:pre-line;">
<?= e($c['terms'] ?? 'El arrendatario se compromete a devolver el vehículo en las mismas condiciones en que lo recibió, en la fecha y hora acordadas. Cualquier daño, multa de tránsito, exceso de kilometraje o combustible faltante será cargado al arrendatario. El depósito será reembolsado tras la inspección del vehículo. El arrendatario declara poseer licencia de conducir vigente, conocer y aceptar las políticas de la empresa, y autoriza el cargo de cualquier daño imputable al uso del vehículo durante el período de alquiler. La empresa se reserva el derecho de cancelar este contrato en caso de incumplimiento de cualquiera de las cláusulas anteriores.') ?>
          </p>
        </div>
      </td>
    </tr>
  </table>

  <!-- ============================== SIGNATURES + QR ============================== -->
  <div class="sec-h" style="margin-top: 2px;">Firmas y verificación</div>
  <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 6px;">
    <tr>
      <td valign="bottom" width="40%">
        <div style="height: 46px; text-align:center; padding-bottom:1px;">
          <?php if ($sigData): ?>
            <img src="<?= e($sigData) ?>" style="max-height:60px; max-width:220px;" alt="">
          <?php elseif ($signed): ?>
            <table cellpadding="0" cellspacing="0" align="center" style="height:46px;"><tr><td valign="middle" align="center">
              <span style="display:inline-block; padding: 6px 14px; border: 1.5px solid <?= $brand ?>; border-radius: 6px; color: <?= $brand ?>; font-size: 12px; font-weight: 700; letter-spacing: .5px;">
                ✓ Firmado digitalmente
              </span>
            </td></tr></table>
          <?php endif; ?>
        </div>
        <div class="sig-line">
          <div class="sig-name"><?= e($custName ?: 'Firma del arrendatario') ?></div>
          <div class="sig-role">Arrendatario · Cliente</div>
          <?php if ($signed): ?>
            <div class="audit">✓ FIRMADO <?= e(date('d/m/Y H:i', strtotime($c['signed_at']))) ?>
              <?php if (!empty($c['signed_ip'])): ?> · IP <?= e($c['signed_ip']) ?><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </td>
      <td width="4%"></td>
      <td valign="bottom" width="40%">
        <div style="height: 46px;"></div>
        <div class="sig-line">
          <div class="sig-name">Por <?= e($tName) ?></div>
          <div class="sig-role">Arrendador · Representante autorizado</div>
        </div>
      </td>
      <td width="2%"></td>
      <!-- QR -->
      <?php if ($qrSrc): ?>
      <td valign="bottom" align="center" width="14%">
        <img src="<?= e($qrSrc) ?>" width="72" height="72" style="border:1px solid <?= $line ?>; padding:2px; background:#fff;" alt="QR">
        <div class="eyebrow" style="margin-top: 3px; font-size:7px;">Versión digital</div>
      </td>
      <?php endif; ?>
    </tr>
  </table>

  <!-- ============================== FOOTER ============================== -->
  <table width="100%" cellpadding="0" cellspacing="0" style="padding-top: 7px; border-top: 1px solid <?= $line ?>;">
    <tr>
      <td style="font-size: 8px; color: #9aa3b2; line-height: 1.45;">
        Documento generado por <span class="strong" style="color:<?= $brand ?>;">Kyros Rent Car</span> · <span style="color:#9aa3b2;">rentcar.kyrosrd.com</span>
      </td>
      <td align="right" style="font-size: 8px; color: #9aa3b2; line-height: 1.45;">
        Emitido el <?= date('d/m/Y') ?> · <?= date('H:i') ?> hrs
      </td>
    </tr>
  </table>

</div>
</body>
</html>
