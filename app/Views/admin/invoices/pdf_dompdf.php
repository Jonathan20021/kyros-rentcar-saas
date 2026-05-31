<?php
/**
 * Invoice PDF — A4 portrait, polished, country-aware (RD NCF / CO DIAN).
 * Same design language as the contract PDF: hero band with brand, clean
 * key/value layout, total emphasized, terms in a tight side block.
 */
use App\Services\PdfService;

$t   = $inv['tenant'];
$cu  = $inv['customer'] ?? null;
$items = $inv['items'] ?? [];

$country  = strtoupper($t['country'] ?? 'DO');
$taxLabel = $t['tax_label']    ?? ($country === 'CO' ? 'IVA' : 'ITBIS');
$taxIdLab = $t['tax_id_label'] ?? ($country === 'CO' ? 'NIT' : 'RNC');
$brand    = $t['primary_color'] ?? '#F23645';
$brandSoft= pdf_mix_white($brand, .92);
$ink      = '#0B1120';
$muted    = '#5A6377';
$line     = '#E6EAF1';
$paper    = '#F7F9FC';

$statusLabels = [
  'draft' => 'Borrador', 'issued' => 'Emitida', 'paid' => 'Pagada', 'void' => 'Anulada',
];
$statusColors = [
  'draft'  => ['#9CA3AF', '#F3F4F6'],
  'issued' => ['#6366F1', '#EEF2FF'],
  'paid'   => ['#10B981', '#ECFDF5'],
  'void'   => ['#EF4444', '#FEF2F2'],
];
$statusText = $statusLabels[$inv['status']] ?? '—';
[$stColor, $stBg] = $statusColors[$inv['status']] ?? ['#9CA3AF', '#F3F4F6'];

$canImg   = PdfService::canRenderImages();
$logoData = !empty($t['logo']) ? PdfService::embedImage($t['logo']) : '';
$hasLogo  = $logoData !== '';
if (!$logoData) {
    $logoData = PdfService::brandSvgDataUri($t['name'] ?? 'K', $brand, '#FFFFFF', 96);
}

$tName = $t['name'] ?? 'Rent car';
$custName = $cu ? trim($cu['first_name'] . ' ' . ($cu['last_name'] ?? '')) : 'Consumidor final';

// Tax effective rate (avoid /0)
$subtotal = (float) $inv['subtotal'];
$taxAmt   = (float) $inv['tax_amount'];
$disc     = (float) ($inv['discount_amount'] ?? 0);
$total    = (float) $inv['total'];
$taxRate  = $subtotal > 0 ? round(($taxAmt / $subtotal) * 100, 1) : (float)($t['tax_rate'] ?? 0);

// Legal compliance label
$legalLabel = $country === 'CO' ? 'Resolución DIAN' : 'NCF';
$legalValue = $country === 'CO'
    ? trim(($inv['dian_prefix'] ?? '') . ($inv['dian_resolution'] ? ' · ' . $inv['dian_resolution'] : ''))
    : trim(($inv['ncf_type'] ?? '') . ($inv['ncf'] ? ' · ' . $inv['ncf'] : ''));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura <?= e($inv['invoice_number']) ?></title>
<style>
  @page { margin: 0; size: A4; }
  body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; color: <?= $ink ?>; font-size: 10px; line-height: 1.45; }
  table { border-collapse: collapse; }
  .eyebrow { font-size: 8px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: #9aa3b2; }
  .muted   { color: <?= $muted ?>; }
  .strong  { color: <?= $ink ?>; font-weight: 700; }
  .right   { text-align: right; }

  .hero { background: <?= $brand ?>; color:#fff; padding: 22px 38px; }
  .hero .name   { font-size: 17px; font-weight: 700; letter-spacing: -.2px; }
  .hero .legal  { font-size: 9px; opacity: .85; margin-top: 3px; }
  .hero .number { font-size: 26px; font-weight: 700; letter-spacing: -.7px; }
  .hero .label-r{ font-size: 8.5px; font-weight: 700; letter-spacing: 1.8px; text-transform: uppercase; opacity: .82; }
  .pill { display: inline-block; padding: 4px 12px; border-radius: 99px; font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
  .doc  { padding: 22px 38px 26px; }
  .sec-h { font-size: 8.5px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: <?= $muted ?>; margin-bottom: 6px; }
  .card  { border: 1px solid <?= $line ?>; border-radius: 12px; background: #fff; padding: 14px 18px; }
  .it-table { width: 100%; }
  .it-table thead td { background: <?= $paper ?>; color: <?= $muted ?>; font-weight: 700; font-size: 8px; text-transform: uppercase; letter-spacing: 1.3px; padding: 9px 14px; border-bottom: 1px solid <?= $line ?>; }
  .it-table tbody td { padding: 10px 14px; border-bottom: 1px solid #F1F3F7; font-size: 10px; }
  .it-table tbody tr:last-child td { border-bottom: 0; }
  .totals-row td { padding: 7px 0; font-size: 10.5px; }
  .totals-row.total td { padding: 11px 14px; background: #0E1422; color: #fff; font-size: 12px; font-weight: 700; border-radius: 8px; }
  .totals-row.total td.amount { font-size: 16px; letter-spacing: -.3px; }
</style>
</head>
<body>

<!-- HERO -->
<div class="hero">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td valign="middle" width="60%">
        <table cellpadding="0" cellspacing="0"><tr>
          <td valign="middle" width="62">
            <img src="<?= e($logoData) ?>" style="width:52px; height:52px; border-radius:12px; <?= $hasLogo ? 'background:#fff; padding:3px;' : '' ?>" alt="">
          </td>
          <td valign="middle" style="padding-left:14px;">
            <div class="name"><?= e($tName) ?></div>
            <?php
              $bits = [];
              if (!empty($t['rnc']))   $bits[] = $taxIdLab . ' ' . $t['rnc'];
              if (!empty($t['phone'])) $bits[] = $t['phone'];
              if (!empty($t['email'])) $bits[] = $t['email'];
              if ($bits) echo '<div class="legal">' . e(implode(' · ', $bits)) . '</div>';
              if (!empty($t['address'])) echo '<div class="legal">' . e($t['address']) . '</div>';
            ?>
          </td>
        </tr></table>
      </td>
      <td valign="middle" align="right" width="40%">
        <div class="label-r">Factura</div>
        <div class="number" style="margin-top:4px;"><?= e($inv['invoice_number']) ?></div>
        <div style="margin-top:8px;">
          <span class="pill" style="background:<?= $stBg ?>; color:<?= $stColor ?>; border:1px solid <?= $stColor ?>33;">
            <?= e(strtoupper($statusText)) ?>
          </span>
        </div>
      </td>
    </tr>
  </table>
</div>

<div class="doc">

  <!-- METADATA STRIP -->
  <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 18px;">
    <tr>
      <td valign="top" width="33%">
        <div class="sec-h">Fecha emisión</div>
        <div class="strong" style="font-size:12px;"><?= e(date('d/m/Y', strtotime($inv['issue_date']))) ?></div>
      </td>
      <td valign="top" width="33%">
        <div class="sec-h">Fecha vencimiento</div>
        <div class="strong" style="font-size:12px;"><?= $inv['due_date'] ? e(date('d/m/Y', strtotime($inv['due_date']))) : '—' ?></div>
      </td>
      <td valign="top" width="34%">
        <div class="sec-h"><?= e($legalLabel) ?></div>
        <div class="strong" style="font-size:12px; font-family:'DejaVu Sans Mono', monospace;"><?= e($legalValue ?: '—') ?></div>
      </td>
    </tr>
  </table>

  <!-- CUSTOMER + COMPANY (Para / De) -->
  <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 18px;">
    <tr>
      <td valign="top" width="49%">
        <div class="sec-h">Facturar a</div>
        <div class="card">
          <div class="strong" style="font-size:13px; letter-spacing:-.2px;"><?= e($custName) ?></div>
          <?php if ($cu): ?>
            <table style="margin-top:6px; font-size:9.5px; line-height:1.6;">
              <?php if (!empty($cu['document_number'])): ?>
                <tr><td class="muted" style="padding-right:12px;">Documento</td><td class="strong"><?= e($cu['document_number']) ?></td></tr>
              <?php endif; ?>
              <?php if (!empty($cu['phone'])): ?>
                <tr><td class="muted" style="padding-right:12px;">Teléfono</td><td class="strong"><?= e($cu['phone']) ?></td></tr>
              <?php endif; ?>
              <?php if (!empty($cu['email'])): ?>
                <tr><td class="muted" style="padding-right:12px;">Email</td><td class="strong"><?= e($cu['email']) ?></td></tr>
              <?php endif; ?>
              <?php if (!empty($cu['address'])): ?>
                <tr><td class="muted" valign="top" style="padding-right:12px;">Dirección</td><td class="strong"><?= e($cu['address']) ?></td></tr>
              <?php endif; ?>
            </table>
          <?php endif; ?>
        </div>
      </td>
      <td width="2%"></td>
      <td valign="top" width="49%">
        <div class="sec-h">Emitida por</div>
        <div class="card" style="background:<?= $paper ?>;">
          <div class="strong" style="font-size:13px; letter-spacing:-.2px;"><?= e($t['legal_name'] ?? $tName) ?></div>
          <table style="margin-top:6px; font-size:9.5px; line-height:1.6;">
            <?php if (!empty($t['rnc'])): ?>
              <tr><td class="muted" style="padding-right:12px;"><?= e($taxIdLab) ?></td><td class="strong"><?= e($t['rnc']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($t['address'])): ?>
              <tr><td class="muted" valign="top" style="padding-right:12px;">Dirección</td><td class="strong"><?= e($t['address']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($t['phone'])): ?>
              <tr><td class="muted" style="padding-right:12px;">Teléfono</td><td class="strong"><?= e($t['phone']) ?></td></tr>
            <?php endif; ?>
          </table>
        </div>
      </td>
    </tr>
  </table>

  <!-- LINE ITEMS -->
  <div class="sec-h">Detalle</div>
  <table class="it-table" style="border:1px solid <?= $line ?>; border-radius:10px; overflow:hidden; margin-bottom: 14px;" width="100%">
    <thead>
      <tr>
        <td width="48%">Concepto</td>
        <td width="12%" class="right">Cant.</td>
        <td width="20%" class="right">Precio unitario</td>
        <td width="20%" class="right">Total</td>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($items)): ?>
        <tr><td colspan="4" class="muted" style="text-align:center; padding: 18px;">Sin líneas en esta factura</td></tr>
      <?php else: foreach ($items as $it): ?>
        <tr>
          <td class="strong"><?= e($it['description']) ?></td>
          <td class="right"><?= rtrim(rtrim(number_format((float)$it['quantity'], 2), '0'), '.') ?></td>
          <td class="right muted"><?= money($it['unit_price']) ?></td>
          <td class="right strong"><?= money($it['line_total']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <!-- TOTALS -->
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td width="55%" valign="top">
        <?php if (!empty($inv['notes'])): ?>
          <div class="sec-h">Notas</div>
          <div class="card" style="border-left:3px solid <?= $brand ?>;">
            <p class="muted" style="margin:0; line-height:1.55; font-size:9.5px;"><?= e($inv['notes']) ?></p>
          </div>
        <?php endif; ?>
      </td>
      <td width="3%"></td>
      <td width="42%" valign="top">
        <table width="100%">
          <tr class="totals-row"><td class="muted">Subtotal</td><td class="right strong tnum"><?= money($subtotal) ?></td></tr>
          <?php if ($disc > 0): ?>
            <tr class="totals-row"><td style="color:#047857;">Descuento</td><td class="right tnum" style="color:#047857; font-weight:700;">− <?= money($disc) ?></td></tr>
          <?php endif; ?>
          <tr class="totals-row"><td class="muted"><?= e($taxLabel) ?> (<?= $taxRate ?>%)</td><td class="right strong tnum"><?= money($taxAmt) ?></td></tr>
          <tr><td colspan="2" style="padding:6px 0;"></td></tr>
          <tr class="totals-row total">
            <td>TOTAL A PAGAR</td>
            <td class="right amount tnum"><?= money($total) ?></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- FOOTER -->
  <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 24px; padding-top: 12px; border-top: 1px solid <?= $line ?>;">
    <tr>
      <td style="font-size: 8.5px; color: #9aa3b2;">
        Documento generado por <span class="strong" style="color:<?= $brand ?>;"><?= e($tName) ?></span>
        <?php if ($inv['status'] === 'paid'): ?>· Pagada el <?= e(date('d/m/Y', strtotime($inv['updated_at']))) ?><?php endif; ?>
      </td>
      <td align="right" style="font-size: 8.5px; color: #9aa3b2;">
        Emitido el <?= e(date('d/m/Y H:i')) ?> · Kyros Rent Car
      </td>
    </tr>
  </table>
</div>
</body>
</html>
