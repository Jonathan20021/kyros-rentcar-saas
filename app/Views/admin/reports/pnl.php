<?php
use App\Models\Expense;
$t = $tenant;
$primary = $t['primary_color'] ?? '#F23645';
$mNames = [1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
[$yy,$mm] = explode('-', $month);
$periodLabel = ($mNames[(int)$mm] ?? $mm) . ' ' . $yy;
$netUp = $net >= 0;
$methods = ['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transferencia','check'=>'Cheque','other'=>'Otro'];
?>
<div class="p-10 text-[13px] text-slate-700 max-w-3xl mx-auto">
  <!-- header -->
  <div class="flex items-start justify-between pb-6 border-b-2 border-slate-900">
    <div class="flex items-center gap-2.5">
      <div class="w-10 h-10 rounded-lg grid place-items-center text-white font-black" style="background:<?= e($primary) ?>"><?= e(mb_substr($t['name'],0,1)) ?></div>
      <div><p class="font-bold text-lg text-slate-900"><?= e($t['name']) ?></p><p class="text-xs text-slate-500"><?= e($t['legal_name'] ?? '') ?> <?= $t['rnc']?'· RNC '.e($t['rnc']):'' ?></p></div>
    </div>
    <div class="text-right">
      <p class="text-xs uppercase tracking-wider text-slate-400">Estado de Resultados</p>
      <p class="font-bold text-xl text-slate-900"><?= e($periodLabel) ?></p>
      <p class="text-xs text-slate-500 mt-1"><?= format_date($from) ?> — <?= format_date($to) ?></p>
    </div>
  </div>

  <!-- income -->
  <div class="mt-8">
    <h2 class="text-[11px] uppercase tracking-wider font-bold text-slate-400 mb-3">Ingresos</h2>
    <table class="w-full">
      <tbody>
        <?php if (empty($incomeByMethod)): ?>
        <tr><td class="py-1.5 text-slate-400">Sin ingresos en el período</td><td class="py-1.5 text-right tnum">RD$ 0.00</td></tr>
        <?php else: foreach ($incomeByMethod as $im): ?>
        <tr class="border-b border-slate-100">
          <td class="py-1.5"><?= e($methods[$im['method']] ?? $im['method']) ?> <span class="text-slate-400 text-xs">· <?= (int)$im['cnt'] ?> pago<?= (int)$im['cnt']===1?'':'s' ?></span></td>
          <td class="py-1.5 text-right tnum"><?= money($im['total']) ?></td>
        </tr>
        <?php endforeach; endif; ?>
        <tr class="border-t-2 border-slate-200 font-bold text-slate-900">
          <td class="py-2">Total ingresos</td>
          <td class="py-2 text-right tnum"><?= money($income) ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- expenses -->
  <div class="mt-7">
    <h2 class="text-[11px] uppercase tracking-wider font-bold text-slate-400 mb-3">Gastos operativos</h2>
    <table class="w-full">
      <tbody>
        <?php if (empty($expByCategory)): ?>
        <tr><td class="py-1.5 text-slate-400">Sin gastos en el período</td><td class="py-1.5 text-right tnum">RD$ 0.00</td></tr>
        <?php else: foreach ($expByCategory as $ec): ?>
        <tr class="border-b border-slate-100">
          <td class="py-1.5"><?= e(Expense::CATEGORIES[$ec['category']] ?? $ec['category']) ?> <span class="text-slate-400 text-xs">· <?= (int)$ec['cnt'] ?></span></td>
          <td class="py-1.5 text-right tnum">(<?= money($ec['total']) ?>)</td>
        </tr>
        <?php endforeach; endif; ?>
        <tr class="border-t-2 border-slate-200 font-bold text-slate-900">
          <td class="py-2">Total gastos</td>
          <td class="py-2 text-right tnum">(<?= money($expTotal) ?>)</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- net -->
  <div class="mt-7 rounded-xl p-5 flex items-center justify-between" style="background:<?= $netUp?'#ECFDF5':'#FEF2F2' ?>">
    <div>
      <p class="text-[11px] uppercase tracking-wider font-bold <?= $netUp?'text-emerald-600':'text-red-600' ?>">Utilidad neta</p>
      <p class="text-xs text-slate-500 mt-0.5">Margen: <?= number_format($margin,1) ?>%</p>
    </div>
    <p class="text-2xl font-extrabold tnum <?= $netUp?'text-emerald-600':'text-red-600' ?>"><?= money($net) ?></p>
  </div>

  <p class="text-[11px] text-slate-400 mt-8 pt-4 border-t border-slate-100 text-center">
    Generado el <?= date('d/m/Y H:i') ?> · <?= e($t['name']) ?> · Documento informativo, no constituye un estado financiero auditado.
  </p>

  <!-- print toolbar (hidden when printing) -->
  <div class="mt-6 flex items-center justify-center gap-2 print:hidden" style="">
    <a href="<?= e($backUrl) ?>" class="k-btn k-btn-outline">Volver</a>
    <button onclick="window.print()" class="k-btn k-btn-grad"><i data-lucide="printer" class="w-4 h-4"></i> Imprimir / PDF</button>
  </div>
</div>
