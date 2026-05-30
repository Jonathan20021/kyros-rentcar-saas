<?php
$statusFlow = [
  'pending'=>['confirmed'=>'Confirmar','rejected'=>'Rechazar','cancelled'=>'Cancelar'],
  'confirmed'=>['in_progress'=>'Iniciar','cancelled'=>'Cancelar'],
  'in_progress'=>['finished'=>'Finalizar'],
];
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
  <div>
    <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Reservas</h1>
    <p class="text-sm text-slate-500"><?= count($reservations) ?> reservas · <?= $statusCounts['pending'] ?> pendientes</p>
  </div>
  <div class="flex gap-2">
    <a href="<?= url('/admin/reservations/calendar') ?>" class="k-btn k-btn-outline flex-1 sm:flex-none"><i data-lucide="calendar" class="w-4 h-4"></i> <span class="hidden sm:inline">Calendario</span></a>
    <?php if (can('reservations.create')): ?><a href="<?= url('/admin/reservations/create') ?>" class="k-btn k-btn-grad flex-1 sm:flex-none"><i data-lucide="plus" class="w-4 h-4"></i> <span>Nueva reserva</span></a><?php endif; ?>
  </div>
</div>

<!-- KPI strip -->
<?php
$kpiCards = [
  ['Confirmadas', $statusCounts['confirmed'], 'calendar-check', 'bg-emerald-50 text-emerald-600'],
  ['Pendientes', $statusCounts['pending'], 'clock', 'bg-amber-50 text-amber-600'],
  ['En proceso', $statusCounts['in_progress'], 'circle-dot', 'bg-indigo-50 text-indigo-600'],
  ['Canceladas', $statusCounts['cancelled'] + $statusCounts['rejected'], 'x-circle', 'bg-red-50 text-brand'],
];
?>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
  <?php foreach ($kpiCards as $c): ?>
  <div class="card p-4 sm:p-5 reveal">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl grid place-items-center <?= $c[3] ?>"><i data-lucide="<?= $c[2] ?>" class="w-[18px] h-[18px]"></i></div>
      <p class="text-[13px] text-slate-400 font-medium truncate"><?= $c[0] ?></p>
    </div>
    <p class="mt-2.5 sm:mt-3 text-[22px] sm:text-[28px] leading-none font-extrabold text-navy dark:text-white tnum" data-count="<?= (int)$c[1] ?>">0</p>
  </div>
  <?php endforeach; ?>
</div>

<form method="GET" class="card p-3 sm:p-4 mb-5 flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-3 sm:items-end">
  <div class="flex-1 min-w-[200px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
    <input name="search" value="<?= e($filters['search']) ?>" placeholder="Código o cliente" class="fld !h-10 !text-[13px]">
  </div>
  <div class="flex gap-2">
    <div class="flex-1 min-w-[140px]">
      <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
      <select name="status" class="fld !h-10 !text-[13px]">
        <option value="">Todos</option>
        <?php foreach (\App\Models\Reservation::STATUSES as $s): ?>
          <option value="<?= $s ?>" <?= ($filters['status']===$s)?'selected':'' ?>><?= status_label($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="k-btn k-btn-dark !h-10 self-end">Filtrar</button>
  </div>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto sm:overflow-x-visible">
    <table class="k-table">
      <thead>
        <tr>
          <th>Código</th><th>Cliente</th><th>Vehículo</th><th>Fechas</th>
          <th>Total</th><th>Estado</th><th class="text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reservations as $r): ?>
        <tr>
          <td data-label="Código" class="k-td-primary">
            <a href="<?= url('/admin/reservations/show/'.$r['id']) ?>" class="font-mono text-xs font-semibold text-brand hover:underline"><?= e($r['reservation_code']) ?></a>
            <?php if ($r['source'] === 'public'): ?><span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-600 font-semibold uppercase tracking-wide">Pública</span><?php endif; ?>
          </td>
          <td data-label="Cliente"><span class="text-navy dark:text-white font-medium truncate"><?= e($r['customer_name'] ?: $r['lead_name'] ?? '—') ?></span></td>
          <td data-label="Vehículo"><span class="text-slate-600 dark:text-slate-300 truncate"><?= e($r['brand'].' '.$r['model']) ?> <span class="text-slate-400 text-xs"><?= e($r['plate_number'] ?? '') ?></span></span></td>
          <td data-label="Fechas"><span class="text-slate-500 text-xs tnum"><?= format_date($r['start_datetime']) ?> → <?= format_date($r['end_datetime']) ?> <span class="text-slate-400">(<?= (int)$r['days_count'] ?>d)</span></span></td>
          <td data-label="Total" class="font-semibold text-navy dark:text-white tnum"><?= money($r['total_amount']) ?></td>
          <td data-label="Estado"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($r['status']) ?>"><?= status_label($r['status']) ?></span></td>
          <td class="k-td-actions text-right">
            <div class="flex items-center justify-end gap-1.5 flex-wrap">
              <a href="<?= url('/admin/reservations/show/'.$r['id']) ?>" class="p-1.5 inline-grid rounded-lg hover:bg-paper dark:hover:bg-slate-800 text-slate-400 hover:text-navy dark:hover:text-white" title="Ver">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </a>
              <?php if (can('reservations.change_status') && !empty($statusFlow[$r['status']])): ?>
                <?php foreach ($statusFlow[$r['status']] as $newStatus => $label): ?>
                  <form method="POST" action="<?= url('/admin/reservations/status/' . $r['id']) ?>" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="<?= $newStatus ?>">
                    <button class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium"><?= e($label) ?></button>
                  </form>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($reservations)): ?>
        <tr><td colspan="7" class="text-center text-slate-400 py-12">
          <i data-lucide="calendar-x" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
          <p>No hay reservas</p>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
