<?php
$actionIcons = [
  'created' => ['plus','bg-emerald-50 text-emerald-600'],
  'updated' => ['pencil','bg-indigo-50 text-indigo-600'],
  'deleted' => ['trash-2','bg-red-50 text-red-600'],
  'login'   => ['log-in','bg-slate-100 text-slate-600'],
  'logout'  => ['log-out','bg-slate-100 text-slate-500'],
  'export'  => ['download','bg-amber-50 text-amber-600'],
];
$actionLabels = [
  'created'=>'Creado','updated'=>'Actualizado','deleted'=>'Eliminado',
  'login'=>'Inicio de sesión','logout'=>'Cierre de sesión','export'=>'Exportación',
];
$moduleLabels = [
  'vehicles'=>'Vehículos','customers'=>'Clientes','reservations'=>'Reservas','contracts'=>'Contratos',
  'payments'=>'Pagos','invoices'=>'Facturas','maintenance'=>'Mantenimiento','incidents'=>'Incidencias',
  'expenses'=>'Gastos','locations'=>'Sucursales','users'=>'Equipo','settings'=>'Ajustes',
  'email_templates'=>'Plantillas','promo_codes'=>'Promociones','drivers'=>'Choferes',
  'extras'=>'Servicios','categories'=>'Categorías','cash_closings'=>'Cierre de caja',
  'api_keys'=>'API','reports'=>'Reportes','auth'=>'Autenticación',
];

// Group entries by day for the timeline.
$groups = [];
foreach ($entries as $e) {
  $d = substr($e['created_at'], 0, 10);
  $groups[$d][] = $e;
}
function _day_label(string $d): string {
  $today = date('Y-m-d');
  $yest  = date('Y-m-d', strtotime('-1 day'));
  if ($d === $today) return 'Hoy';
  if ($d === $yest)  return 'Ayer';
  return format_date($d);
}
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Actividad</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Registro auditable de cambios y eventos en tu cuenta.</p>
  </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <?php
  $kpis = [
    ['Total registros', (int)$stats['total'], 'list-tree', 'bg-navy/5 text-navy'],
    ['Hoy', (int)$stats['today'], 'calendar', 'bg-brand/10 text-brand'],
    ['Últimas 24h', (int)$stats['last_24h'], 'activity', 'bg-emerald-50 text-emerald-600'],
    ['Usuarios activos', (int)$stats['unique_users'], 'users', 'bg-indigo-50 text-indigo-600'],
  ];
  foreach ($kpis as $k): ?>
  <div class="card p-4 reveal">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl grid place-items-center <?= $k[3] ?>"><i data-lucide="<?= $k[2] ?>" class="w-[18px] h-[18px]"></i></div>
      <p class="text-[13px] text-slate-400 font-medium"><?= $k[0] ?></p>
    </div>
    <p class="mt-2 text-[24px] leading-none font-extrabold text-navy dark:text-white tnum" data-count="<?= $k[1] ?>">0</p>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<form method="GET" class="card p-4 mb-5 grid sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Módulo</label>
    <select name="module" class="fld !h-10">
      <option value="">Todos</option>
      <?php foreach ($modules as $m): ?>
        <option value="<?= e($m['module']) ?>" <?= $filters['module']===$m['module']?'selected':'' ?>>
          <?= e($moduleLabels[$m['module']] ?? ucfirst($m['module'])) ?> (<?= (int)$m['c'] ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Acción</label>
    <select name="action" class="fld !h-10">
      <option value="">Todas</option>
      <?php foreach ($actions as $a): ?>
        <option value="<?= e($a['action']) ?>" <?= $filters['action']===$a['action']?'selected':'' ?>>
          <?= e($actionLabels[$a['action']] ?? ucfirst($a['action'])) ?> (<?= (int)$a['c'] ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Usuario</label>
    <select name="user" class="fld !h-10">
      <option value="">Todos</option>
      <?php foreach ($users as $u): ?>
        <option value="<?= (int)$u['id'] ?>" <?= (int)$filters['user']===(int)$u['id']?'selected':'' ?>><?= e($u['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Desde</label>
    <input type="date" name="from" value="<?= e($filters['from']) ?>" class="fld !h-10">
  </div>
  <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Hasta</label>
    <input type="date" name="to" value="<?= e($filters['to']) ?>" class="fld !h-10">
  </div>
  <div class="flex gap-2">
    <button class="k-btn k-btn-dark !h-10 flex-1">Filtrar</button>
    <a href="<?= url('/admin/activity') ?>" class="k-btn k-btn-outline !h-10" title="Limpiar"><i data-lucide="x" class="w-4 h-4"></i></a>
  </div>
</form>

<?php if (empty($entries)): ?>
  <div class="card p-16 text-center">
    <div class="w-14 h-14 rounded-2xl bg-paper grid place-items-center mx-auto"><i data-lucide="history" class="w-7 h-7 text-slate-300"></i></div>
    <h3 class="font-semibold text-navy mt-4">Sin actividad</h3>
    <p class="text-sm text-slate-400 mt-1">No hay registros que coincidan con estos filtros.</p>
  </div>
<?php else: ?>
<div class="card overflow-hidden">
  <?php foreach ($groups as $day => $rows): ?>
  <div class="px-6 py-3 bg-paper/60 dark:bg-slate-800/40 border-b hairline sticky top-16 z-10 backdrop-blur">
    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500"><?= e(_day_label($day)) ?></p>
  </div>
  <div class="divide-y hairline">
    <?php foreach ($rows as $e):
      $ai = $actionIcons[$e['action']] ?? ['circle','bg-slate-100 text-slate-500'];
      $time = substr($e['created_at'], 11, 5);
    ?>
    <div class="flex gap-4 px-6 py-3.5 hover:bg-paper dark:hover:bg-slate-800/40">
      <span class="w-9 h-9 rounded-xl grid place-items-center shrink-0 <?= $ai[1] ?>"><i data-lucide="<?= $ai[0] ?>" class="w-4 h-4"></i></span>
      <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-1.5">
          <span class="font-semibold text-navy dark:text-white text-sm">
            <?= e($e['user_name'] ?: 'Sistema') ?>
          </span>
          <span class="text-sm text-slate-500"><?= e(strtolower($actionLabels[$e['action']] ?? $e['action'])) ?></span>
          <?php if ($e['module']): ?>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-paper dark:bg-slate-800 text-slate-600 dark:text-slate-300">
              <?= e($moduleLabels[$e['module']] ?? $e['module']) ?>
              <?php if ($e['entity_id']): ?><span class="text-slate-400">#<?= (int)$e['entity_id'] ?></span><?php endif; ?>
            </span>
          <?php endif; ?>
        </div>
        <?php if (!empty($e['description'])): ?>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= e($e['description']) ?></p>
        <?php endif; ?>
      </div>
      <div class="text-right shrink-0">
        <p class="text-xs text-slate-400 tnum"><?= e($time) ?></p>
        <?php if (!empty($e['ip_address'])): ?><p class="text-[11px] text-slate-300 tnum"><?= e($e['ip_address']) ?></p><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php if (count($entries) >= 200): ?>
<p class="text-center text-xs text-slate-400 mt-4">Mostrando últimos 200 registros. Filtra por fecha para ver eventos más antiguos.</p>
<?php endif; ?>
<?php endif; ?>
