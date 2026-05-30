<?php
$v   = $c['vehicle'];
$cu  = $c['customer'];
$t   = $c['tenant'];
$custName = trim($cu['first_name'] . ' ' . $cu['last_name']);
$brand    = $t['primary_color'] ?? '#F23645';
$shareUrl = !empty($c['share_token']) ? abs_url('/contrato/' . $c['share_token']) : null;
$signed   = !empty($c['signed_at']);

$days = max(1, (int) ceil((strtotime($c['end_datetime']) - strtotime($c['start_datetime'])) / 86400));
$statusLabels = [
  'draft' => ['Borrador',  '#6b7385'],
  'active'=> ['Activo',    '#0E9F6E'],
  'finished'=>['Finalizado','#6366F1'],
  'cancelled'=>['Cancelado','#94a3b8'],
  'overdue'=> ['Atrasado', '#F59E0B'],
  'claim'  => ['Reclamo',  '#F23645'],
];
$st = $statusLabels[$c['status']] ?? ['—', '#94a3b8'];
?>
<style>
  /* Document-only refinements (the print layout already loads Tailwind + Inter) */
  .doc{ color:#1C2433; font-size:12.5px; line-height:1.55; }
  .doc h1, .doc h2, .doc h3, .doc h4{ letter-spacing:-.015em; }
  .doc .tnum{ font-variant-numeric:tabular-nums; }
  .doc .hairline{ border-color:#E6EAF1; }
  .doc .eyebrow{ font-size:10px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:#94a3b8; }
  .doc .brand-band{ background: linear-gradient(135deg, <?= e($brand) ?> 0%, color-mix(in srgb, <?= e($brand) ?> 60%, #1C2433) 100%); color:#fff; }
  .doc .key-table{ width:100%; border-collapse:collapse; }
  .doc .key-table th, .doc .key-table td{ padding:9px 14px; text-align:left; }
  .doc .key-table thead th{ background:#F7F9FC; color:#6b7385; font-weight:600; font-size:10.5px; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid #E6EAF1; }
  .doc .key-table tbody td{ border-bottom:1px solid #EFF1F5; }
  .doc .key-table tbody tr:last-child td{ border-bottom:0; }
  .doc .pill{ display:inline-flex; align-items:center; gap:.4rem; padding:4px 10px; border-radius:999px; font-size:11px; font-weight:700; }
  .doc .sig-line{ height:1px; background:#1C2433; opacity:.65; margin:60px 0 6px; }
  @media print{
    .doc{ padding:0 !important; }
    .doc .page-break{ page-break-after:always; }
  }
</style>

<div class="doc">

  <!-- =================== BRAND BAND =================== -->
  <div class="brand-band relative overflow-hidden" style="padding:34px 40px 30px;">
    <!-- subtle grid texture -->
    <div style="position:absolute; inset:0; opacity:.15; background-image:linear-gradient(rgba(255,255,255,.18) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.18) 1px,transparent 1px); background-size:28px 28px;"></div>
    <div class="relative" style="display:flex; align-items:flex-start; justify-content:space-between; gap:24px;">
      <div style="display:flex; gap:14px; align-items:flex-start;">
        <?php if (!empty($t['logo'])): ?>
          <img src="<?= e(media($t['logo'])) ?>" alt="" style="width:54px; height:54px; border-radius:12px; background:#fff; padding:6px; object-fit:contain;">
        <?php else: ?>
          <div style="width:54px; height:54px; border-radius:12px; background:rgba(255,255,255,.95); color:<?= e($brand) ?>; display:grid; place-items:center; font-weight:900; font-size:24px; letter-spacing:-.02em;">
            <?= e(mb_substr($t['name'] ?? 'K', 0, 1)) ?>
          </div>
        <?php endif; ?>
        <div>
          <p style="font-weight:800; font-size:18px; line-height:1.1;"><?= e($t['name']) ?></p>
          <?php if (!empty($t['legal_name']) || !empty($t['rnc'])): ?>
            <p style="opacity:.75; font-size:11px; margin-top:3px;">
              <?php if (!empty($t['legal_name'])): ?><?= e($t['legal_name']) ?><?php endif; ?>
              <?php if (!empty($t['rnc'])): ?> · RNC <?= e($t['rnc']) ?><?php endif; ?>
            </p>
          <?php endif; ?>
          <?php if (!empty($t['address'])): ?>
            <p style="opacity:.7; font-size:11px; margin-top:2px;"><?= e($t['address']) ?></p>
          <?php endif; ?>
          <?php if (!empty($t['phone']) || !empty($t['email'])): ?>
            <p style="opacity:.7; font-size:11px; margin-top:2px;">
              <?= e($t['phone'] ?? '') ?><?= !empty($t['email']) ? ' · ' . e($t['email']) : '' ?>
            </p>
          <?php endif; ?>
        </div>
      </div>
      <div style="text-align:right;">
        <p class="eyebrow" style="color:rgba(255,255,255,.7);">Contrato de alquiler</p>
        <p style="font-weight:800; font-size:30px; letter-spacing:-.02em; margin-top:4px;" class="tnum"><?= e($c['contract_number']) ?></p>
        <div style="margin-top:10px; display:inline-flex; gap:6px; flex-direction:column; align-items:flex-end;">
          <span class="pill" style="background:rgba(255,255,255,.18); backdrop-filter:blur(8px); color:#fff; border:1px solid rgba(255,255,255,.25);">
            <?= e($st[0]) ?>
          </span>
          <?php if ($signed): ?>
            <span class="pill" style="background:rgba(16,159,110,.22); border:1px solid rgba(16,159,110,.55); color:#D1FAE5;">
              ✓ FIRMADO <?= e(date('d/m/Y', strtotime($c['signed_at']))) ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- =================== BODY =================== -->
  <div style="padding:30px 40px 36px;">

    <!-- Period strip -->
    <div style="display:grid; grid-template-columns:1fr auto 1fr auto auto; gap:18px; align-items:center; padding:14px 18px; border:1px solid #E6EAF1; border-radius:10px; background:#F7F9FC; margin-bottom:24px;">
      <div>
        <p class="eyebrow">Inicio</p>
        <p style="font-weight:700; font-size:13px; margin-top:3px;" class="tnum"><?= format_date($c['start_datetime']) ?></p>
        <p style="font-size:11px; color:#6b7385;" class="tnum"><?= e(date('H:i', strtotime($c['start_datetime']))) ?></p>
      </div>
      <div style="color:<?= e($brand) ?>; font-size:18px;">→</div>
      <div>
        <p class="eyebrow">Devolución</p>
        <p style="font-weight:700; font-size:13px; margin-top:3px;" class="tnum"><?= format_date($c['end_datetime']) ?></p>
        <p style="font-size:11px; color:#6b7385;" class="tnum"><?= e(date('H:i', strtotime($c['end_datetime']))) ?></p>
      </div>
      <div style="width:1px; height:36px; background:#E6EAF1;"></div>
      <div>
        <p class="eyebrow">Duración</p>
        <p style="font-weight:800; font-size:18px; color:<?= e($brand) ?>;" class="tnum"><?= $days ?> <span style="font-size:11px; font-weight:600;">días</span></p>
      </div>
    </div>

    <!-- Parties -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:22px;">
      <!-- Arrendatario -->
      <div style="border:1px solid #E6EAF1; border-radius:10px; padding:16px 18px;">
        <p class="eyebrow" style="margin-bottom:8px;">Arrendatario (cliente)</p>
        <p style="font-weight:700; font-size:15px; color:#0E1422;"><?= e($custName) ?: '—' ?></p>
        <table style="margin-top:8px; font-size:11.5px; color:#4b5363; line-height:1.6;">
          <?php if (!empty($cu['document_number'])): ?>
            <tr><td style="color:#94a3b8; padding-right:10px;">Cédula/Doc</td><td class="tnum"><?= e($cu['document_number']) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($cu['phone'])): ?>
            <tr><td style="color:#94a3b8; padding-right:10px;">Teléfono</td><td class="tnum"><?= e($cu['phone']) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($cu['email'])): ?>
            <tr><td style="color:#94a3b8; padding-right:10px;">Email</td><td><?= e($cu['email']) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($cu['license_number'])): ?>
            <tr><td style="color:#94a3b8; padding-right:10px;">Licencia</td><td class="tnum"><?= e($cu['license_number']) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($cu['address'])): ?>
            <tr><td style="color:#94a3b8; padding-right:10px; vertical-align:top;">Dirección</td><td><?= e($cu['address']) ?></td></tr>
          <?php endif; ?>
        </table>
      </div>
      <!-- Vehículo -->
      <div style="border:1px solid #E6EAF1; border-radius:10px; padding:16px 18px;">
        <p class="eyebrow" style="margin-bottom:8px;">Vehículo</p>
        <p style="font-weight:700; font-size:15px; color:#0E1422;">
          <?= e($v['brand'] . ' ' . $v['model']) ?>
          <?php if (!empty($v['version'])): ?><span style="color:#6b7385;"> · <?= e($v['version']) ?></span><?php endif; ?>
          <span style="color:#6b7385; font-weight:600;">(<?= e($v['year']) ?>)</span>
        </p>
        <table style="margin-top:8px; font-size:11.5px; color:#4b5363; line-height:1.6;">
          <?php if (!empty($v['plate_number'])): ?>
            <tr><td style="color:#94a3b8; padding-right:10px;">Placa</td><td class="tnum"><?= e($v['plate_number']) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($v['vin'])): ?>
            <tr><td style="color:#94a3b8; padding-right:10px;">VIN</td><td class="tnum"><?= e($v['vin']) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($v['color'])): ?>
            <tr><td style="color:#94a3b8; padding-right:10px;">Color</td><td><?= e($v['color']) ?></td></tr>
          <?php endif; ?>
          <tr><td style="color:#94a3b8; padding-right:10px;">Transmisión</td><td><?= $v['transmission'] === 'automatic' ? 'Automática' : 'Manual' ?> · <?= ucfirst($v['fuel_type']) ?></td></tr>
          <?php if (!empty($v['passengers'])): ?>
            <tr><td style="color:#94a3b8; padding-right:10px;">Capacidad</td><td><?= (int) $v['passengers'] ?> pasajeros</td></tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <!-- Handover state -->
    <?php if ($c['start_mileage'] !== null || $c['start_fuel_level'] !== null || $c['end_mileage'] !== null): ?>
    <div style="border:1px solid #E6EAF1; border-radius:10px; overflow:hidden; margin-bottom:22px;">
      <table class="key-table">
        <thead>
          <tr>
            <th>Concepto</th>
            <th style="text-align:right;">Salida</th>
            <th style="text-align:right;">Llegada</th>
            <th style="text-align:right;">Diferencia</th>
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
            <td style="font-weight:600;">Kilometraje</td>
            <td style="text-align:right;" class="tnum"><?= $sm !== null ? number_format($sm) . ' km' : '—' ?></td>
            <td style="text-align:right;" class="tnum"><?= $em !== null ? number_format($em) . ' km' : '—' ?></td>
            <td style="text-align:right; color:<?= e($brand) ?>; font-weight:700;" class="tnum">
              <?= ($sm !== null && $em !== null) ? '+ ' . number_format(max(0, $em - $sm)) . ' km' : '—' ?>
            </td>
          </tr>
          <tr>
            <td style="font-weight:600;">Combustible</td>
            <td style="text-align:right;" class="tnum"><?= $sf !== null ? $sf . '%' : '—' ?></td>
            <td style="text-align:right;" class="tnum"><?= $ef !== null ? $ef . '%' : '—' ?></td>
            <td style="text-align:right; font-weight:700; <?= ($sf !== null && $ef !== null && $ef < $sf) ? 'color:#F59E0B;' : 'color:#0E9F6E;' ?>" class="tnum">
              <?= ($sf !== null && $ef !== null) ? ($ef >= $sf ? '+ ' : '- ') . abs($ef - $sf) . '%' : '—' ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- Pricing -->
    <div style="border:1px solid #E6EAF1; border-radius:10px; overflow:hidden; margin-bottom:22px;">
      <table class="key-table">
        <thead>
          <tr>
            <th>Concepto</th>
            <th style="text-align:right;">Detalle</th>
            <th style="text-align:right;">Monto</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Alquiler</td>
            <td style="text-align:right; color:#6b7385;" class="tnum"><?= money($c['daily_rate']) ?> × <?= $days ?> días</td>
            <td style="text-align:right; font-weight:600;" class="tnum"><?= money($c['subtotal']) ?></td>
          </tr>
          <?php if ((float) $c['extras_total'] > 0): ?>
          <tr>
            <td>Servicios adicionales</td>
            <td></td>
            <td style="text-align:right; font-weight:600;" class="tnum"><?= money($c['extras_total']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($c['discount_amount']) && (float) $c['discount_amount'] > 0): ?>
          <tr>
            <td style="color:#0E9F6E;">Descuento aplicado</td>
            <td></td>
            <td style="text-align:right; color:#0E9F6E; font-weight:600;" class="tnum">- <?= money($c['discount_amount']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if ((float) $c['penalties_total'] > 0): ?>
          <tr>
            <td style="color:#F59E0B;">Penalidades / cargos</td>
            <td></td>
            <td style="text-align:right; color:#F59E0B; font-weight:600;" class="tnum">+ <?= money($c['penalties_total']) ?></td>
          </tr>
          <?php endif; ?>
          <tr>
            <td>Impuesto (ITBIS)</td>
            <td></td>
            <td style="text-align:right; font-weight:600;" class="tnum"><?= money($c['tax_amount']) ?></td>
          </tr>
          <tr style="background:#F7F9FC;">
            <td style="font-weight:800; font-size:13.5px;">TOTAL</td>
            <td></td>
            <td style="text-align:right; font-weight:800; font-size:14.5px;" class="tnum"><?= money($c['total_amount']) ?></td>
          </tr>
          <tr>
            <td style="color:#0E9F6E;">Pagado</td>
            <td></td>
            <td style="text-align:right; color:#0E9F6E; font-weight:700;" class="tnum">- <?= money($c['paid_amount']) ?></td>
          </tr>
          <tr style="background:color-mix(in srgb, <?= e($brand) ?> 8%, white);">
            <td style="font-weight:800; color:<?= e($brand) ?>;">Balance pendiente</td>
            <td></td>
            <td style="text-align:right; font-weight:800; font-size:14px; color:<?= e($brand) ?>;" class="tnum"><?= money($c['balance_due']) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Deposit note -->
    <?php if ((float) $c['deposit_amount'] > 0): ?>
    <div style="display:flex; align-items:center; gap:10px; padding:10px 14px; background:#F7F9FC; border:1px dashed #CFD6E2; border-radius:8px; margin-bottom:22px; font-size:11.5px; color:#6b7385;">
      <span style="font-weight:700; color:#1C2433;">Depósito en garantía:</span>
      <span class="tnum"><?= money($c['deposit_amount']) ?></span>
      <span style="margin-left:auto; font-size:10.5px;">(reembolsable tras inspección del vehículo)</span>
    </div>
    <?php endif; ?>

    <!-- Terms -->
    <div style="margin-bottom:24px;">
      <p class="eyebrow" style="margin-bottom:8px;">Términos y condiciones</p>
      <p style="font-size:11px; color:#4b5363; line-height:1.7; text-align:justify; white-space:pre-line;">
        <?= e($c['terms'] ?? 'El arrendatario se compromete a devolver el vehículo en las mismas condiciones en que lo recibió, en la fecha y hora acordadas. Cualquier daño, multa de tránsito, exceso de kilometraje o combustible faltante será cargado al arrendatario. El depósito será reembolsado tras la inspección del vehículo. El arrendatario declara poseer licencia de conducir vigente, conocer y aceptar las políticas de la empresa, y autoriza el cargo de cualquier daño imputable al uso del vehículo durante el período de alquiler.') ?>
      </p>
    </div>

    <!-- Signatures -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:48px; margin-top:48px;">
      <!-- Cliente -->
      <div>
        <?php if (!empty($c['customer_signature'])): ?>
          <div style="height:64px; display:flex; align-items:flex-end; justify-content:center;">
            <img src="<?= e(media($c['customer_signature'])) ?>" alt="" style="max-height:64px; object-fit:contain; mix-blend-mode:multiply;">
          </div>
        <?php else: ?>
          <div style="height:64px;"></div>
        <?php endif; ?>
        <div style="height:1px; background:#1C2433; opacity:.65;"></div>
        <p style="margin-top:6px; font-size:11px; font-weight:700; color:#1C2433;"><?= e($custName ?: 'Firma del arrendatario') ?></p>
        <p style="font-size:10px; color:#6b7385;">Arrendatario (cliente)</p>
        <?php if ($signed): ?>
          <p style="font-size:10px; color:#0E9F6E; font-weight:600; margin-top:4px;">
            ✓ Firmado el <?= e(date('d/m/Y H:i', strtotime($c['signed_at']))) ?>
            <?php if (!empty($c['signed_ip'])): ?>· IP <?= e($c['signed_ip']) ?><?php endif; ?>
          </p>
        <?php endif; ?>
      </div>
      <!-- Rent car -->
      <div>
        <div style="height:64px;"></div>
        <div style="height:1px; background:#1C2433; opacity:.65;"></div>
        <p style="margin-top:6px; font-size:11px; font-weight:700; color:#1C2433;">Por <?= e($t['name']) ?></p>
        <p style="font-size:10px; color:#6b7385;">Arrendador</p>
      </div>
    </div>

    <!-- Share QR (if available) -->
    <?php if ($shareUrl): ?>
    <div style="margin-top:38px; padding-top:18px; border-top:1px dashed #CFD6E2; display:flex; align-items:center; gap:16px;">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&margin=0&data=<?= rawurlencode($shareUrl) ?>"
           alt="QR" style="width:72px; height:72px; border:1px solid #E6EAF1; border-radius:6px; padding:4px; background:#fff;">
      <div>
        <p class="eyebrow">Versión digital</p>
        <p style="font-size:11px; color:#1C2433; margin-top:3px;">Escanea el código para ver y firmar este contrato online.</p>
        <p style="font-size:10px; color:#6b7385; margin-top:3px; word-break:break-all;"><?= e($shareUrl) ?></p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div style="margin-top:36px; padding-top:14px; border-top:1px solid #E6EAF1; display:flex; align-items:center; justify-content:space-between; font-size:10px; color:#94a3b8;">
      <p>Generado por <b style="color:#1C2433;">Kyros Rent Car</b> · rentcar.kyrosrd.com</p>
      <p class="tnum"><?= date('d/m/Y H:i') ?></p>
    </div>

  </div>
</div>
