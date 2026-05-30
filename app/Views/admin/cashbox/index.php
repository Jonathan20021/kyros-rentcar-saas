<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
  <div>
    <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Cierre de caja</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Concilia el efectivo del día: ingresos cobrados menos gastos pagados</p>
  </div>
  <?php if (can('cashbox.manage')): ?>
  <a href="<?= url('/admin/cashbox/create') ?>" class="k-btn k-btn-grad"><i data-lucide="calculator" class="w-4 h-4"></i> Nuevo cierre</a>
  <?php endif; ?>
</div>

<?php if (!$todayDone && can('cashbox.manage')): ?>
<div class="card p-4 mb-5 flex items-center gap-3 border-l-4 !border-l-amber-400">
  <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 grid place-items-center shrink-0"><i data-lucide="alert-circle" class="w-5 h-5"></i></div>
  <p class="text-sm text-slate-600 dark:text-slate-300 flex-1">Aún no has hecho el cierre de hoy.</p>
  <a href="<?= url('/admin/cashbox/create') ?>" class="k-btn k-btn-outline !h-9">Cerrar hoy</a>
</div>
<?php endif; ?>

<div class="card overflow-hidden">
  <div class="overflow-x-auto sm:overflow-x-visible">
    <table class="k-table">
      <thead>
        <tr><th>Fecha</th><th>Ingresos</th><th>Efectivo esperado</th><th>Contado</th><th>Diferencia</th><th>Responsable</th><th class="text-right">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($closings as $c): $diff=(float)$c['difference']; ?>
        <tr>
          <td data-label="Fecha" class="k-td-primary"><span class="font-medium text-navy dark:text-white"><?= e(date('d/m/Y', strtotime($c['closing_date']))) ?></span><?php if(!empty($c['location_name'])): ?> <span class="text-xs text-slate-400"><?= e($c['location_name']) ?></span><?php endif; ?></td>
          <td data-label="Ingresos" class="text-slate-500 tnum"><?= money($c['income_total']) ?></td>
          <td data-label="Esperado" class="text-slate-500 tnum"><?= money($c['expected_cash']) ?></td>
          <td data-label="Contado" class="font-medium text-navy dark:text-white tnum"><?= money($c['counted_cash']) ?></td>
          <td data-label="Diferencia">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold <?= abs($diff)<0.01?'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600':($diff<0?'bg-red-50 dark:bg-red-500/10 text-brand':'bg-amber-50 dark:bg-amber-500/10 text-amber-600') ?>">
              <?= abs($diff)<0.01 ? 'Cuadrado' : (($diff>0?'+':'').money($diff)) ?>
            </span>
          </td>
          <td data-label="Responsable" class="text-slate-500 truncate"><?= e($c['closed_by_name'] ?? '—') ?></td>
          <td class="k-td-actions text-right"><a href="<?= url('/admin/cashbox/show/'.$c['id']) ?>" class="text-xs font-semibold text-brand hover:underline">Ver</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($closings)): ?>
        <tr><td colspan="7" class="text-center text-slate-400 py-12"><i data-lucide="calculator" class="w-8 h-8 mx-auto mb-2 opacity-40"></i><p class="text-sm">Aún no hay cierres registrados.</p></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
