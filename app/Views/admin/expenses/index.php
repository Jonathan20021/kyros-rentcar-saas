<?php
use App\Models\Expense;
$catTint = [
  'fuel'=>'bg-orange-50 text-orange-600','insurance'=>'bg-sky-50 text-sky-600','repairs'=>'bg-red-50 text-brand',
  'maintenance'=>'bg-amber-50 text-amber-600','salaries'=>'bg-indigo-50 text-indigo-600','rent'=>'bg-violet-50 text-violet-600',
  'utilities'=>'bg-cyan-50 text-cyan-600','marketing'=>'bg-pink-50 text-pink-600','taxes'=>'bg-slate-100 text-slate-600',
  'fees'=>'bg-teal-50 text-teal-600','supplies'=>'bg-lime-50 text-lime-600','other'=>'bg-slate-100 text-slate-500',
];
$catIcon = [
  'fuel'=>'fuel','insurance'=>'shield','repairs'=>'wrench','maintenance'=>'settings','salaries'=>'users','rent'=>'building',
  'utilities'=>'plug-zap','marketing'=>'megaphone','taxes'=>'landmark','fees'=>'percent','supplies'=>'package','other'=>'circle-dot',
];
$net = $monthNet; $netUp = $net >= 0;
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Gastos</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Controla los costos operativos de tu rent car</p>
  </div>
  <?php if (can('expenses.create')): ?>
  <a href="<?= url('/admin/expenses/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Registrar gasto</a>
  <?php endif; ?>
</div>

<!-- Financial KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <div class="card p-5 reveal">
    <div class="flex items-center gap-2.5"><div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 grid place-items-center"><i data-lucide="trending-up" class="w-[18px] h-[18px]"></i></div><p class="text-[13px] text-slate-400 font-medium">Ingresos (mes)</p></div>
    <p class="mt-3 text-[22px] leading-none font-extrabold text-navy dark:text-white tnum"><?= money($monthIncome) ?></p>
  </div>
  <div class="card p-5 reveal">
    <div class="flex items-center gap-2.5"><div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-500/10 text-brand grid place-items-center"><i data-lucide="trending-down" class="w-[18px] h-[18px]"></i></div><p class="text-[13px] text-slate-400 font-medium">Gastos (mes)</p></div>
    <p class="mt-3 text-[22px] leading-none font-extrabold text-navy dark:text-white tnum"><?= money($monthExpense) ?></p>
  </div>
  <div class="card p-5 reveal">
    <div class="flex items-center gap-2.5"><div class="w-9 h-9 rounded-xl <?= $netUp?'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600':'bg-red-50 dark:bg-red-500/10 text-brand' ?> grid place-items-center"><i data-lucide="<?= $netUp?'wallet':'alert-triangle' ?>" class="w-[18px] h-[18px]"></i></div><p class="text-[13px] text-slate-400 font-medium">Utilidad neta (mes)</p></div>
    <p class="mt-3 text-[22px] leading-none font-extrabold tnum <?= $netUp?'text-emerald-600':'text-brand' ?>"><?= money($net) ?></p>
  </div>
  <div class="card p-5 reveal">
    <div class="flex items-center gap-2.5"><div class="w-9 h-9 rounded-xl bg-navy/5 dark:bg-white/5 text-navy dark:text-white grid place-items-center"><i data-lucide="calendar" class="w-[18px] h-[18px]"></i></div><p class="text-[13px] text-slate-400 font-medium">Gastos (año)</p></div>
    <p class="mt-3 text-[22px] leading-none font-extrabold text-navy dark:text-white tnum"><?= money($yearExpense) ?></p>
  </div>
</div>

<!-- Filters -->
<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
  <div class="flex-1 min-w-[160px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
    <input name="search" value="<?= e($filters['search']) ?>" placeholder="Descripción o proveedor" class="fld !h-10">
  </div>
  <div class="min-w-[150px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Categoría</label>
    <select name="category" class="fld !h-10"><option value="">Todas</option>
      <?php foreach (Expense::CATEGORIES as $k=>$lbl): ?><option value="<?= $k ?>" <?= ($filters['category']===$k)?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?>
    </select>
  </div>
  <?php if (!empty($locations)): ?>
  <div class="min-w-[150px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Sucursal</label>
    <select name="location_id" class="fld !h-10"><option value="">Todas</option>
      <?php foreach ($locations as $l): ?><option value="<?= $l['id'] ?>" <?= (($filters['location_id']??0)==$l['id'])?'selected':'' ?>><?= e($l['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <div><label class="block text-xs font-medium text-slate-500 mb-1">Desde</label><input type="date" name="from" value="<?= e($filters['from']) ?>" class="fld !h-10"></div>
  <div><label class="block text-xs font-medium text-slate-500 mb-1">Hasta</label><input type="date" name="to" value="<?= e($filters['to']) ?>" class="fld !h-10"></div>
  <button class="k-btn k-btn-dark !h-10">Filtrar</button>
  <a href="<?= url('/admin/expenses') ?>" class="k-btn k-btn-outline !h-10">Limpiar</a>
</form>

<div class="card overflow-hidden reveal">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-paper dark:bg-slate-800/50">
        <tr><th class="px-6 py-3 font-medium">Fecha</th><th class="px-6 py-3 font-medium">Descripción</th><th class="px-6 py-3 font-medium">Categoría</th><th class="px-6 py-3 font-medium">Sucursal / Vehículo</th><th class="px-6 py-3 font-medium">Método</th><th class="px-6 py-3 font-medium text-right">Monto</th><th class="px-6 py-3"></th></tr>
      </thead>
      <tbody class="divide-y hairline">
        <?php $sum = 0; foreach ($expenses as $e): $sum += (float)$e['amount']; ?>
        <tr class="hover:bg-paper dark:hover:bg-slate-800/40">
          <td class="px-6 py-3.5 text-slate-500 whitespace-nowrap tnum"><?= e(date('d/m/Y', strtotime($e['expense_date']))) ?></td>
          <td class="px-6 py-3.5">
            <p class="font-medium text-navy dark:text-white"><?= e($e['description']) ?></p>
            <?php if (!empty($e['vendor'])): ?><p class="text-xs text-slate-400"><?= e($e['vendor']) ?></p><?php endif; ?>
          </td>
          <td class="px-6 py-3.5">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?= $catTint[$e['category']] ?? 'bg-slate-100 text-slate-500' ?>"><i data-lucide="<?= $catIcon[$e['category']] ?? 'circle-dot' ?>" class="w-3.5 h-3.5"></i><?= Expense::CATEGORIES[$e['category']] ?? $e['category'] ?></span>
          </td>
          <td class="px-6 py-3.5 text-slate-500">
            <?= e($e['location_name'] ?? '—') ?><?php if (!empty($e['vehicle_name'])): ?><span class="block text-xs text-slate-400"><?= e($e['vehicle_name']) ?></span><?php endif; ?>
          </td>
          <td class="px-6 py-3.5 text-slate-500"><?= Expense::METHODS[$e['payment_method']] ?? $e['payment_method'] ?></td>
          <td class="px-6 py-3.5 text-right font-bold text-navy dark:text-white tnum"><?= money($e['amount']) ?></td>
          <td class="px-6 py-3.5 text-right whitespace-nowrap">
            <?php if (can('expenses.edit')): ?><a href="<?= url('/admin/expenses/edit/'.$e['id']) ?>" class="p-1.5 inline-grid rounded-lg hover:bg-paper dark:hover:bg-slate-800 text-slate-400 hover:text-navy dark:hover:text-white" title="Editar"><i data-lucide="pencil" class="w-4 h-4"></i></a><?php endif; ?>
            <?php if (can('expenses.delete')): ?><form method="POST" action="<?= url('/admin/expenses/delete/'.$e['id']) ?>" class="inline" data-confirm="Esta acción no se puede deshacer." data-confirm-title="¿Eliminar este gasto?"><?= csrf_field() ?><button class="p-1.5 inline-grid rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-slate-400 hover:text-red-600" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($expenses)): ?>
        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><i data-lucide="receipt-text" class="w-8 h-8 mx-auto mb-2 opacity-40"></i><p class="text-sm">No hay gastos en este período.</p></td></tr>
        <?php endif; ?>
      </tbody>
      <?php if (!empty($expenses)): ?>
      <tfoot><tr class="border-t-2 hairline bg-paper dark:bg-slate-800/50"><td colspan="5" class="px-6 py-3 font-semibold text-navy dark:text-white text-right">Total filtrado</td><td class="px-6 py-3 text-right font-extrabold text-brand tnum"><?= money($sum) ?></td><td></td></tr></tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>
