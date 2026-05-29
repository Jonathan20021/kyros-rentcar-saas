<?php
$t = $tenant; $primary = $t['primary_color'] ?? '#F23645';
$diff = (float) $cc['difference'];
$diffLabel = abs($diff) < 0.01 ? 'Caja cuadrada' : ($diff < 0 ? 'Faltante' : 'Sobrante');
$diffColor = abs($diff) < 0.01 ? '#059669' : ($diff < 0 ? '#E11D48' : '#D97706');
?>
<div class="p-10 text-[13px] text-slate-700 max-w-xl mx-auto">
  <div class="flex items-start justify-between pb-6 border-b-2 border-slate-900">
    <div class="flex items-center gap-2.5">
      <div class="w-10 h-10 rounded-lg grid place-items-center text-white font-black" style="background:<?= e($primary) ?>"><?= e(mb_substr($t['name'],0,1)) ?></div>
      <div><p class="font-bold text-lg text-slate-900"><?= e($t['name']) ?></p><p class="text-xs text-slate-500">Cierre de caja</p></div>
    </div>
    <div class="text-right">
      <p class="text-xs uppercase tracking-wider text-slate-400">Fecha</p>
      <p class="font-bold text-xl text-slate-900"><?= e(date('d/m/Y', strtotime($cc['closing_date']))) ?></p>
      <?php if (!empty($cc['location_name'])): ?><p class="text-xs text-slate-500 mt-1"><?= e($cc['location_name']) ?></p><?php endif; ?>
    </div>
  </div>

  <div class="mt-7">
    <h2 class="text-[11px] uppercase tracking-wider font-bold text-slate-400 mb-2">Ingresos cobrados</h2>
    <table class="w-full">
      <tr class="border-b border-slate-100"><td class="py-1.5">Efectivo</td><td class="py-1.5 text-right tnum"><?= money($cc['income_cash']) ?></td></tr>
      <tr class="border-b border-slate-100"><td class="py-1.5">Tarjeta</td><td class="py-1.5 text-right tnum"><?= money($cc['income_card']) ?></td></tr>
      <tr class="border-b border-slate-100"><td class="py-1.5">Transferencia</td><td class="py-1.5 text-right tnum"><?= money($cc['income_transfer']) ?></td></tr>
      <tr class="border-b border-slate-100"><td class="py-1.5">Otros</td><td class="py-1.5 text-right tnum"><?= money($cc['income_other']) ?></td></tr>
      <tr class="font-bold text-slate-900"><td class="py-2">Total ingresos</td><td class="py-2 text-right tnum"><?= money($cc['income_total']) ?></td></tr>
    </table>
  </div>

  <div class="mt-5">
    <h2 class="text-[11px] uppercase tracking-wider font-bold text-slate-400 mb-2">Conciliación de efectivo</h2>
    <table class="w-full">
      <tr class="border-b border-slate-100"><td class="py-1.5">Ingresos en efectivo</td><td class="py-1.5 text-right tnum"><?= money($cc['income_cash']) ?></td></tr>
      <tr class="border-b border-slate-100"><td class="py-1.5">(−) Gastos en efectivo</td><td class="py-1.5 text-right tnum">(<?= money($cc['expense_cash']) ?>)</td></tr>
      <tr class="border-b border-slate-200 font-semibold"><td class="py-1.5">Efectivo esperado</td><td class="py-1.5 text-right tnum"><?= money($cc['expected_cash']) ?></td></tr>
      <tr class="border-b border-slate-100"><td class="py-1.5">Efectivo contado</td><td class="py-1.5 text-right tnum"><?= money($cc['counted_cash']) ?></td></tr>
    </table>
  </div>

  <div class="mt-5 rounded-xl p-4 flex items-center justify-between" style="background:<?= $diffColor ?>14;">
    <p class="text-[11px] uppercase tracking-wider font-bold" style="color:<?= $diffColor ?>"><?= $diffLabel ?></p>
    <p class="text-2xl font-extrabold tnum" style="color:<?= $diffColor ?>"><?= ($diff>0?'+':'').money($diff) ?></p>
  </div>

  <?php if (!empty($cc['notes'])): ?><p class="mt-4 text-xs text-slate-500"><span class="font-semibold">Notas:</span> <?= e($cc['notes']) ?></p><?php endif; ?>

  <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
    <span>Responsable: <?= e($cc['closed_by_name'] ?? '—') ?></span>
    <span>Generado <?= date('d/m/Y H:i') ?></span>
  </div>

  <div class="mt-6 flex items-center justify-center gap-2 print:hidden">
    <a href="<?= e($backUrl) ?>" class="k-btn k-btn-outline">Volver</a>
    <button onclick="window.print()" class="k-btn k-btn-grad"><i data-lucide="printer" class="w-4 h-4"></i> Imprimir</button>
  </div>
</div>
