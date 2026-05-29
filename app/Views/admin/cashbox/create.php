<?php $inc = $movements['income']; ?>
<div class="max-w-3xl mx-auto" x-data="{ counted: <?= (float)$movements['expected_cash'] ?>, expected: <?= (float)$movements['expected_cash'] ?>,
  get diff(){ return (this.counted - this.expected); },
  fmt(n){ return 'RD$ ' + Number(n).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); } }">
  <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1">Cierre de caja</h1>
  <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Movimientos del día — concilia el efectivo físico</p>

  <!-- date selector -->
  <form method="GET" action="<?= url('/admin/cashbox/create') ?>" class="card p-4 mb-5 flex flex-wrap items-end gap-3">
    <div><label class="block text-xs font-medium text-slate-500 mb-1">Fecha del cierre</label><input type="date" name="date" value="<?= e($date) ?>" class="fld !h-10"></div>
    <button class="k-btn k-btn-dark !h-10">Calcular</button>
    <?php if ($already): ?><span class="text-sm text-amber-600 flex items-center gap-1.5"><i data-lucide="alert-triangle" class="w-4 h-4"></i> Ya existe un cierre para esta fecha (se registrará otro).</span><?php endif; ?>
  </form>

  <div class="grid sm:grid-cols-2 gap-5">
    <!-- income breakdown -->
    <div class="card p-5">
      <h2 class="font-display font-bold text-navy dark:text-white mb-3 flex items-center gap-2"><i data-lucide="trending-up" class="w-4 h-4 text-emerald-500"></i> Ingresos cobrados</h2>
      <div class="space-y-2 text-sm">
        <?php foreach (['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transferencia','other'=>'Otros'] as $k=>$lbl): ?>
        <div class="flex items-center justify-between"><span class="text-slate-500"><?= $lbl ?></span><span class="font-medium text-navy dark:text-white tnum"><?= money($inc[$k]) ?></span></div>
        <?php endforeach; ?>
        <div class="flex items-center justify-between pt-2 border-t hairline"><span class="font-semibold text-navy dark:text-white">Total ingresos</span><span class="font-extrabold text-emerald-600 tnum"><?= money($movements['income_total']) ?></span></div>
      </div>
    </div>

    <!-- expenses -->
    <div class="card p-5">
      <h2 class="font-display font-bold text-navy dark:text-white mb-3 flex items-center gap-2"><i data-lucide="trending-down" class="w-4 h-4 text-brand"></i> Gastos del día</h2>
      <div class="space-y-2 text-sm">
        <div class="flex items-center justify-between"><span class="text-slate-500">Gastos en efectivo</span><span class="font-medium text-navy dark:text-white tnum"><?= money($movements['expense_cash']) ?></span></div>
        <div class="flex items-center justify-between"><span class="text-slate-500">Gastos totales</span><span class="font-medium text-navy dark:text-white tnum"><?= money($movements['expense_total']) ?></span></div>
        <div class="flex items-center justify-between pt-2 border-t hairline"><span class="font-semibold text-navy dark:text-white">Efectivo esperado</span><span class="font-extrabold text-navy dark:text-white tnum"><?= money($movements['expected_cash']) ?></span></div>
        <p class="text-[11px] text-slate-400 pt-1">Efectivo esperado = ingresos en efectivo − gastos en efectivo</p>
      </div>
    </div>
  </div>

  <!-- reconciliation form -->
  <form method="POST" action="<?= url('/admin/cashbox') ?>" class="card p-6 mt-5 space-y-5">
    <?= csrf_field() ?>
    <input type="hidden" name="closing_date" value="<?= e($date) ?>">
    <div class="grid sm:grid-cols-2 gap-4">
      <?php if (!empty($locations)): ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Sucursal</label>
        <select name="location_id" class="fld"><option value="">— General —</option>
          <?php foreach ($locations as $l): ?><option value="<?= $l['id'] ?>"><?= e($l['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Efectivo contado *</label>
        <input type="number" step="0.01" name="counted_cash" x-model.number="counted" required class="fld tnum">
      </div>
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Notas</label><textarea name="notes" rows="2" class="fld" placeholder="Observaciones del cierre…"></textarea></div>
    </div>

    <!-- live difference -->
    <div class="rounded-xl p-4 flex items-center justify-between"
         :class="Math.abs(diff)<0.01 ? 'bg-emerald-50 dark:bg-emerald-500/10' : (diff<0 ? 'bg-red-50 dark:bg-red-500/10' : 'bg-amber-50 dark:bg-amber-500/10')">
      <div>
        <p class="text-[11px] uppercase tracking-wider font-bold"
           :class="Math.abs(diff)<0.01 ? 'text-emerald-600' : (diff<0 ? 'text-brand' : 'text-amber-600')"
           x-text="Math.abs(diff)<0.01 ? 'Caja cuadrada' : (diff<0 ? 'Faltante' : 'Sobrante')"></p>
        <p class="text-xs text-slate-500 mt-0.5">Esperado <span class="tnum" x-text="fmt(expected)"></span> · Contado <span class="tnum" x-text="fmt(counted)"></span></p>
      </div>
      <p class="text-2xl font-extrabold tnum"
         :class="Math.abs(diff)<0.01 ? 'text-emerald-600' : (diff<0 ? 'text-brand' : 'text-amber-600')"
         x-text="(diff>0?'+':'')+fmt(diff)"></p>
    </div>

    <div class="flex items-center gap-3">
      <button type="submit" class="k-btn k-btn-grad !px-6"><i data-lucide="lock" class="w-4 h-4"></i> Registrar cierre</button>
      <a href="<?= url('/admin/cashbox') ?>" class="k-btn k-btn-outline !px-6">Cancelar</a>
    </div>
  </form>
</div>
