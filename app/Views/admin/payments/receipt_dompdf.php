<?php
/**
 * Payment receipt PDF — compact A4 format for a single payment record.
 * Matches the visual language of the contract + invoice PDFs.
 */
use App\Services\PdfService;

$t      = $p['tenant'];
$cu     = $p['customer'] ?? null;
$contract = $p['contract'] ?? null;

$brand    = $t['primary_color'] ?? '#F23645';
$brandSoft= pdf_mix_white($brand, .92);
$ink      = '#0B1120';
$muted    = '#5A6377';
$line     = '#E6EAF1';
$paper    = '#F7F9FC';

$canImg   = PdfService::canRenderImages();
$logoData = !empty($t['logo']) ? PdfService::embedImage($t['logo']) : '';
$hasLogo  = $logoData !== '';
if (!$logoData) {
    $logoData = PdfService::brandSvgDataUri($t['name'] ?? 'K', $brand, '#FFFFFF', 96);
}
$tName = $t['name'] ?? 'Rent car';

$methodLabels = [
  'cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta',
  'paypal'=>'PayPal','stripe'=>'Stripe','azul'=>'Azul','cardnet'=>'Cardnet','other'=>'Otro',
];
$statusLabels = [
  'paid'=>'Pagado','pending'=>'Pendiente','partial'=>'Parcial','refunded'=>'Reembolsado','voided'=>'Anulado',
];

$statusColors = [
  'paid'     => ['#10B981', '#ECFDF5'],
  'pending'  => ['#F59E0B', '#FFFBEB'],
  'partial'  => ['#6366F1', '#EEF2FF'],
  'refunded' => ['#94A3B8', '#F1F5F9'],
  'voided'   => ['#EF4444', '#FEF2F2'],
];
[$stColor, $stBg] = $statusColors[$p['status']] ?? ['#9CA3AF', '#F3F4F6'];

$custName = $cu ? trim($cu['first_name'] . ' ' . ($cu['last_name'] ?? '')) : '—';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recibo <?= e($p['payment_code']) ?></title>
<style>
  @page { margin: 0; size: A4; }
  body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; color: <?= $ink ?>; font-size: 10.5px; line-height: 1.45; }
  table { border-collapse: collapse; }
  .eyebrow { font-size: 8px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: #9aa3b2; }
  .muted   { color: <?= $muted ?>; }
  .strong  { color: <?= $ink ?>; font-weight: 700; }
  .right   { text-align: right; }

  .hero { background: <?= $brand ?>; color:#fff; padding: 24px 38px; }
  .hero .name   { font-size: 17px; font-weight: 700; letter-spacing: -.2px; }
  .hero .legal  { font-size: 9px; opacity: .85; margin-top: 3px; }
  .hero .number { font-size: 26px; font-weight: 700; letter-spacing: -.7px; font-family:'DejaVu Sans Mono', monospace; }
  .hero .label-r{ font-size: 9px; font-weight: 700; letter-spacing: 1.8px; text-transform: uppercase; opacity: .82; }

  .doc  { padding: 28px 38px; }
  .sec-h { font-size: 8.5px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: <?= $muted ?>; margin-bottom: 6px; }

  .big-amount {
    background: linear-gradient(135deg, <?= $brand ?>, <?= pdf_mix_navy($brand, .30) ?>);
    color: #fff; padding: 30px 36px; border-radius: 18px; text-align: center;
  }
  .big-amount .small { font-size: 10px; opacity: .85; letter-spacing: 2px; text-transform: uppercase; font-weight: 700; }
  .big-amount .num   { font-size: 42px; font-weight: 700; letter-spacing: -1.5px; margin-top: 6px; }
  .big-amount .desc  { font-size: 10.5px; opacity: .88; margin-top: 4px; }

  .kv { border:1px solid <?= $line ?>; border-radius: 12px; padding: 6px 18px; background:#fff; }
  .kv tr td { padding: 9px 0; border-bottom: 1px solid #F1F3F7; font-size: 10.5px; }
  .kv tr:last-child td { border-bottom: 0; }
  .kv .label { color: <?= $muted ?>; width: 38%; }
  .kv .val   { color: <?= $ink ?>; font-weight: 700; text-align: right; }

  .stamp {
    display:inline-block; padding: 6px 14px; border-radius: 99px;
    font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase;
  }
</style>
</head>
<body>

<!-- HERO -->
<div class="hero">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td valign="middle" width="62%">
        <table cellpadding="0" cellspacing="0"><tr>
          <td valign="middle" width="62">
            <img src="<?= e($logoData) ?>" style="width:52px; height:52px; border-radius:12px; <?= $hasLogo ? 'background:#fff; padding:3px;' : '' ?>" alt="">
          </td>
          <td valign="middle" style="padding-left:14px;">
            <div class="name"><?= e($tName) ?></div>
            <?php
              $bits = [];
              if (!empty($t['rnc']))   $bits[] = ($t['tax_id_label'] ?? 'RNC') . ' ' . $t['rnc'];
              if (!empty($t['phone'])) $bits[] = $t['phone'];
              if (!empty($t['email'])) $bits[] = $t['email'];
              if ($bits) echo '<div class="legal">' . e(implode(' · ', $bits)) . '</div>';
            ?>
          </td>
        </tr></table>
      </td>
      <td valign="middle" align="right" width="38%">
        <div class="label-r">Recibo de pago</div>
        <div class="number" style="margin-top:4px;"><?= e($p['payment_code']) ?></div>
        <div style="margin-top:8px;">
          <span class="stamp" style="background:<?= $stBg ?>; color:<?= $stColor ?>; border:1px solid <?= $stColor ?>33;">
            <?= e(strtoupper($statusLabels[$p['status']] ?? '—')) ?>
          </span>
        </div>
      </td>
    </tr>
  </table>
</div>

<div class="doc">

  <!-- BIG AMOUNT -->
  <div class="big-amount">
    <div class="small">Monto recibido</div>
    <div class="num"><?= money($p['amount']) ?></div>
    <div class="desc">Pagado el <?= e(date('d \d\e F \d\e Y', strtotime($p['payment_date']))) ?> · <?= e($methodLabels[$p['method']] ?? $p['method']) ?></div>
  </div>

  <!-- DETAILS -->
  <div style="margin-top:24px;">
    <div class="sec-h">Detalle del pago</div>
    <table width="100%" class="kv">
      <tr>
        <td class="label">Código</td>
        <td class="val tnum"><?= e($p['payment_code']) ?></td>
      </tr>
      <tr>
        <td class="label">Cliente</td>
        <td class="val"><?= e($custName) ?>
          <?php if ($cu && !empty($cu['document_number'])): ?>
            <span class="muted" style="font-weight:400; font-size:9.5px;"> · <?= e($cu['document_number']) ?></span>
          <?php endif; ?>
        </td>
      </tr>
      <?php if ($contract): ?>
      <tr>
        <td class="label">Contrato</td>
        <td class="val tnum"><?= e($contract['contract_number']) ?></td>
      </tr>
      <?php endif; ?>
      <tr>
        <td class="label">Método</td>
        <td class="val"><?= e($methodLabels[$p['method']] ?? $p['method']) ?></td>
      </tr>
      <?php if (!empty($p['reference'])): ?>
      <tr>
        <td class="label">Referencia</td>
        <td class="val" style="font-family:'DejaVu Sans Mono', monospace;"><?= e($p['reference']) ?></td>
      </tr>
      <?php endif; ?>
      <tr>
        <td class="label">Fecha</td>
        <td class="val tnum"><?= e(date('d/m/Y', strtotime($p['payment_date']))) ?></td>
      </tr>
      <?php if (!empty($p['notes'])): ?>
      <tr>
        <td class="label" valign="top">Notas</td>
        <td class="val" style="font-weight:400;"><?= e($p['notes']) ?></td>
      </tr>
      <?php endif; ?>
    </table>
  </div>

  <!-- ACKNOWLEDGEMENT -->
  <div style="margin-top:28px; padding: 18px 22px; background:<?= $paper ?>; border-radius: 12px; border-left: 3px solid <?= $brand ?>;">
    <p class="muted" style="margin:0; font-size:9.5px; line-height:1.6;">
      Este documento es un acuse de recibo del pago indicado. Si el pago corresponde a un contrato de alquiler, el saldo
      pendiente se actualiza automáticamente. Para cualquier consulta, contacta con
      <span class="strong"><?= e($tName) ?></span>
      <?php if (!empty($t['phone'])): ?>al <?= e($t['phone']) ?><?php endif; ?>
      <?php if (!empty($t['email'])): ?>o por correo a <?= e($t['email']) ?><?php endif; ?>.
    </p>
  </div>

  <!-- FOOTER -->
  <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 30px; padding-top: 14px; border-top: 1px solid <?= $line ?>;">
    <tr>
      <td style="font-size: 8.5px; color: #9aa3b2;">
        Generado por <span class="strong" style="color:<?= $brand ?>;"><?= e($tName) ?></span>
      </td>
      <td align="right" style="font-size: 8.5px; color: #9aa3b2;">
        Emitido el <?= e(date('d/m/Y H:i')) ?> · Kyros Rent Car
      </td>
    </tr>
  </table>
</div>
</body>
</html>
