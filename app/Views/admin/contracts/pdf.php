<?php $v=$c['vehicle']; $cu=$c['customer']; $t=$c['tenant']; $custName=trim($cu['first_name'].' '.$cu['last_name']); ?>
<div class="p-10 text-[13px] text-slate-700">
  <!-- Header -->
  <div class="flex items-start justify-between pb-6 border-b-2 border-slate-900">
    <div>
      <div class="flex items-center gap-2.5">
        <div class="w-10 h-10 rounded-lg grid place-items-center text-white font-black" style="background:<?= e($t['primary_color']??'#F23645') ?>"><?= e(mb_substr($t['name'],0,1)) ?></div>
        <div><p class="font-bold text-lg text-slate-900"><?= e($t['name']) ?></p><p class="text-xs text-slate-500"><?= e($t['legal_name'] ?? '') ?> <?= $t['rnc']?'· RNC '.e($t['rnc']):'' ?></p></div>
      </div>
      <p class="text-xs text-slate-500 mt-2"><?= e($t['address'] ?? '') ?><br><?= e($t['phone'] ?? '') ?> · <?= e($t['email'] ?? '') ?></p>
    </div>
    <div class="text-right">
      <p class="text-xs uppercase tracking-wider text-slate-400">Contrato de alquiler</p>
      <p class="font-bold text-xl text-slate-900 tnum"><?= e($c['contract_number']) ?></p>
      <p class="text-xs text-slate-500 mt-1">Emitido: <?= format_date($c['created_at']) ?></p>
    </div>
  </div>

  <!-- Parties -->
  <div class="grid grid-cols-2 gap-6 mt-6">
    <div><p class="text-[11px] uppercase tracking-wider text-slate-400 mb-1">Arrendatario (Cliente)</p>
      <p class="font-semibold text-slate-900"><?= e($custName) ?></p>
      <p class="text-xs text-slate-500"><?= e(ucfirst($cu['document_type'])) ?>: <?= e($cu['document_number'] ?? '-') ?><br><?= e($cu['phone'] ?? '') ?> · <?= e($cu['email'] ?? '') ?><br>Licencia: <?= e($cu['license_number'] ?? '-') ?></p>
    </div>
    <div><p class="text-[11px] uppercase tracking-wider text-slate-400 mb-1">Vehículo</p>
      <p class="font-semibold text-slate-900"><?= e($v['brand'].' '.$v['model'].' '.($v['version']??'')) ?> (<?= e($v['year']) ?>)</p>
      <p class="text-xs text-slate-500">Placa: <?= e($v['plate_number'] ?? '-') ?> · VIN: <?= e($v['vin'] ?? '-') ?><br>Color: <?= e($v['color'] ?? '-') ?> · <?= e(ucfirst($v['transmission'])) ?></p>
    </div>
  </div>

  <!-- Period -->
  <table class="w-full mt-6 border border-slate-200 rounded overflow-hidden text-xs">
    <thead class="bg-slate-50 text-slate-500"><tr><th class="text-left px-3 py-2 font-semibold">Inicio</th><th class="text-left px-3 py-2 font-semibold">Fin previsto</th><th class="text-left px-3 py-2 font-semibold">Km salida</th><th class="text-left px-3 py-2 font-semibold">Combustible</th></tr></thead>
    <tbody><tr class="border-t border-slate-200">
      <td class="px-3 py-2 tnum"><?= format_datetime($c['start_datetime']) ?></td>
      <td class="px-3 py-2 tnum"><?= format_datetime($c['end_datetime']) ?></td>
      <td class="px-3 py-2 tnum"><?= $c['start_mileage']!==null?number_format((int)$c['start_mileage']).' km':'-' ?></td>
      <td class="px-3 py-2 tnum"><?= $c['start_fuel_level']!==null?$c['start_fuel_level'].'%':'-' ?></td>
    </tr></tbody>
  </table>

  <!-- Costs -->
  <div class="mt-6 flex justify-end">
    <table class="w-72 text-sm">
      <tbody>
        <tr><td class="py-1.5 text-slate-500">Subtotal</td><td class="py-1.5 text-right font-medium tnum"><?= money($c['subtotal']) ?></td></tr>
        <?php if ($c['extras_total']>0): ?><tr><td class="py-1.5 text-slate-500">Extras</td><td class="py-1.5 text-right font-medium tnum"><?= money($c['extras_total']) ?></td></tr><?php endif; ?>
        <?php if ($c['penalties_total']>0): ?><tr><td class="py-1.5 text-slate-500">Penalidades</td><td class="py-1.5 text-right font-medium tnum"><?= money($c['penalties_total']) ?></td></tr><?php endif; ?>
        <tr><td class="py-1.5 text-slate-500">Impuesto</td><td class="py-1.5 text-right font-medium tnum"><?= money($c['tax_amount']) ?></td></tr>
        <tr class="border-t border-slate-300"><td class="py-2 font-bold text-slate-900">Total</td><td class="py-2 text-right font-bold text-slate-900 tnum"><?= money($c['total_amount']) ?></td></tr>
        <tr><td class="py-1.5 text-emerald-600">Pagado</td><td class="py-1.5 text-right text-emerald-600 tnum"><?= money($c['paid_amount']) ?></td></tr>
        <tr><td class="py-1.5 font-semibold">Balance</td><td class="py-1.5 text-right font-bold tnum"><?= money($c['balance_due']) ?></td></tr>
        <tr><td class="py-1.5 text-slate-400 text-xs">Depósito</td><td class="py-1.5 text-right text-slate-400 text-xs tnum"><?= money($c['deposit_amount']) ?></td></tr>
      </tbody>
    </table>
  </div>

  <!-- Terms -->
  <div class="mt-8">
    <p class="text-[11px] uppercase tracking-wider text-slate-400 mb-2">Términos y condiciones</p>
    <p class="text-[11px] text-slate-500 leading-relaxed text-justify">
      <?= e($c['terms'] ?? 'El arrendatario se compromete a devolver el vehículo en las mismas condiciones en que lo recibió, en la fecha y hora acordadas. Cualquier daño, multa de tránsito, exceso de kilometraje o combustible faltante será cargado al arrendatario. El depósito será reembolsado tras la inspección del vehículo. El arrendatario declara poseer licencia de conducir vigente y acepta las políticas de la empresa.') ?>
    </p>
  </div>

  <!-- Signatures -->
  <div class="grid grid-cols-2 gap-12 mt-16">
    <div class="text-center">
      <?php if (!empty($c['customer_signature'])): ?><img src="<?= e($c['customer_signature']) ?>" style="height:58px;margin:0 auto -4px;object-fit:contain"><?php endif; ?>
      <div class="border-t border-slate-400 pt-2 text-xs text-slate-500">Firma del cliente</div>
    </div>
    <div class="text-center"><div style="height:58px"></div><div class="border-t border-slate-400 pt-2 text-xs text-slate-500">Por <?= e($t['name']) ?></div></div>
  </div>
  <p class="text-center text-[10px] text-slate-300 mt-10">Generado por Kyros Rent Car · <?= date('d/m/Y H:i') ?></p>
</div>
