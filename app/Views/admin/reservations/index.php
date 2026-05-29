<?php
$statusFlow = [
  'pending'=>['confirmed'=>'Confirmar','rejected'=>'Rechazar','cancelled'=>'Cancelar'],
  'confirmed'=>['in_progress'=>'Iniciar','cancelled'=>'Cancelar'],
  'in_progress'=>['finished'=>'Finalizar'],
];
?>
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Reservas</h1>
    <p class="text-sm text-slate-500"><?= count($reservations) ?> reservas · <?= $statusCounts['pending'] ?> pendientes</p>
  </div>
  <div class="flex gap-2">
    <a href="<?= url('/admin/reservations/calendar') ?>" class="k-btn k-btn-outline"><i data-lucide="calendar" class="w-4 h-4"></i> Calendario</a>
    <?php if (can('reservations.create')): ?><a href="<?= url('/admin/reservations/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nueva reserva</a><?php endif; ?>
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
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <?php foreach ($kpiCards as $c): ?>
  <div class="card p-5 reveal">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl grid place-items-center <?= $c[3] ?>"><i data-lucide="<?= $c[2] ?>" class="w-[18px] h-[18px]"></i></div>
      <p class="text-[13px] text-slate-400 font-medium"><?= $c[0] ?></p>
    </div>
    <p class="mt-3 text-[28px] leading-none font-extrabold text-navy dark:text-white tnum" data-count="<?= (int)$c[1] ?>">0</p>
  </div>
  <?php endforeach; ?>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
  <div class="flex-1 min-w-[200px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
    <input name="search" value="<?= e($filters['search']) ?>" placeholder="Codigo o cliente" class="fld !py-2 !text-[13px]">
  </div>
  <div class="min-w-[150px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !py-2 !text-[13px]">
      <option value="">Todos</option>
      <?php foreach (\App\Models\Reservation::STATUSES as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status']===$s)?'selected':'' ?>><?= status_label($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="k-btn k-btn-dark !py-2">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-slate-50 dark:bg-slate-800/50">
        <tr>
          <th class="px-6 py-3 font-medium">Codigo</th><th class="px-6 py-3 font-medium">Cliente</th>
          <th class="px-6 py-3 font-medium">Vehiculo</th><th class="px-6 py-3 font-medium">Fechas</th>
          <th class="px-6 py-3 font-medium">Total</th><th class="px-6 py-3 font-medium">Estado</th>
          <th class="px-6 py-3 font-medium text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#EAECEF] dark:divide-slate-800">
        <?php foreach ($reservations as $r): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="px-6 py-3 font-mono text-xs font-medium"><a href="<?= url('/admin/reservations/show/'.$r['id']) ?>" class="text-brand hover:underline"><?= e($r['reservation_code']) ?></a></td>
          <td class="px-6 py-3"><?= e($r['customer_name']) ?></td>
          <td class="px-6 py-3 text-slate-500"><?= e($r['brand'].' '.$r['model']) ?><div class="text-xs text-slate-400"><?= e($r['plate_number']) ?></div></td>
          <td class="px-6 py-3 text-slate-500 text-xs"><?= format_date($r['start_datetime']) ?> → <?= format_date($r['end_datetime']) ?><div class="text-slate-400"><?= (int)$r['days_count'] ?> dias</div></td>
          <td class="px-6 py-3 font-semibold"><?= money($r['total_amount']) ?></td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($r['status']) ?>"><?= status_label($r['status']) ?></span></td>
          <td class="px-6 py-3 text-right">
            <?php if (can('reservations.change_status') && !empty($statusFlow[$r['status']])): ?>
              <div class="flex items-center justify-end gap-1.5">
              <?php foreach ($statusFlow[$r['status']] as $newStatus => $label): ?>
                <form method="POST" action="<?= url('/admin/reservations/status/' . $r['id']) ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="status" value="<?= $newStatus ?>">
                  <button class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium"><?= e($label) ?></button>
                </form>
              <?php endforeach; ?>
              </div>
            <?php else: ?>
              <span class="text-xs text-slate-400">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($reservations)): ?>
        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><i data-lucide="calendar-x" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>No hay reservas</p></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
