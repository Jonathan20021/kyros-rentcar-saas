<?php
use App\Models\Driver;
$sc = $statusCounts;
$today = new DateTime('today');
$kpis = [
  ['Total', array_sum($sc), 'users', 'bg-navy/5 text-navy'],
  ['Activos', $sc['active'], 'badge-check', 'bg-emerald-50 text-emerald-600'],
  ['Vacaciones', $sc['vacation'], 'palmtree', 'bg-amber-50 text-amber-600'],
  ['Inactivos', $sc['inactive'], 'user-x', 'bg-slate-100 text-slate-500'],
];
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Choferes</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Personal asignable a contratos con servicio de chofer.</p>
  </div>
  <?php if (can('drivers.create')): ?>
  <a href="<?= url('/admin/drivers/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nuevo chofer</a>
  <?php endif; ?>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <?php foreach ($kpis as $k): ?>
  <div class="card p-4 reveal">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl grid place-items-center <?= $k[3] ?>"><i data-lucide="<?= $k[2] ?>" class="w-[18px] h-[18px]"></i></div>
      <p class="text-[13px] text-slate-400 font-medium"><?= $k[0] ?></p>
    </div>
    <p class="mt-2 text-[24px] leading-none font-extrabold text-navy dark:text-white tnum" data-count="<?= (int)$k[1] ?>">0</p>
  </div>
  <?php endforeach; ?>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
  <div class="flex-1 min-w-[180px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
    <input name="search" value="<?= e($filters['search']) ?>" placeholder="Nombre, cédula o licencia" class="fld !h-10">
  </div>
  <div class="min-w-[160px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !h-10">
      <option value="">Todos</option>
      <?php foreach (Driver::STATUSES as $k=>$lbl): ?>
        <option value="<?= $k ?>" <?= $filters['status']===$k?'selected':'' ?>><?= $lbl ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="k-btn k-btn-dark !h-10">Filtrar</button>
  <a href="<?= url('/admin/drivers') ?>" class="k-btn k-btn-outline !h-10">Limpiar</a>
</form>

<?php if (empty($drivers)): ?>
  <div class="card p-16 text-center">
    <div class="w-14 h-14 rounded-2xl bg-paper grid place-items-center mx-auto"><i data-lucide="id-card" class="w-7 h-7 text-slate-300"></i></div>
    <h3 class="font-semibold text-navy mt-4">Aún no hay choferes</h3>
    <p class="text-sm text-slate-400 mt-1">Agrega un chofer para ofrecer servicio con conductor.</p>
    <?php if (can('drivers.create')): ?><a href="<?= url('/admin/drivers/create') ?>" class="k-btn k-btn-grad mt-4"><i data-lucide="plus" class="w-4 h-4"></i> Nuevo chofer</a><?php endif; ?>
  </div>
<?php else: ?>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
  <?php foreach ($drivers as $d):
    $fullName = trim($d['first_name'].' '.($d['last_name'] ?? ''));
    $statusMap = [
      'active'   => ['Activo','bg-emerald-50 text-emerald-600'],
      'vacation' => ['Vacaciones','bg-amber-50 text-amber-600'],
      'inactive' => ['Inactivo','bg-slate-100 text-slate-500'],
    ];
    $sm = $statusMap[$d['status']] ?? $statusMap['inactive'];
    $licWarn = null;
    if (!empty($d['license_expiration'])) {
      $exp = new DateTime($d['license_expiration']);
      $diff = (int) $today->diff($exp)->format('%r%a');
      if ($diff < 0)        $licWarn = ['Vencida', 'bg-red-50 text-red-600'];
      elseif ($diff <= 30)  $licWarn = ["Vence en {$diff}d", 'bg-amber-50 text-amber-600'];
    }
  ?>
  <a href="<?= url('/admin/drivers/show/'.$d['id']) ?>" class="card overflow-hidden group reveal-s block">
    <div class="relative h-32 bg-gradient-to-br from-brand/15 to-brand2/10 dark:from-brand/25 dark:to-navy">
      <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[11px] font-semibold <?= $sm[1] ?>"><?= $sm[0] ?></span>
      <?php if ($licWarn): ?>
        <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[11px] font-semibold flex items-center gap-1 <?= $licWarn[1] ?>"><i data-lucide="alert-triangle" class="w-3 h-3"></i><?= $licWarn[0] ?></span>
      <?php endif; ?>
    </div>
    <div class="px-5 pt-0 pb-5 relative">
      <div class="w-20 h-20 -mt-12 rounded-2xl bg-white dark:bg-slate-900 border-4 border-white dark:border-slate-900 shadow-card overflow-hidden grid place-items-center">
        <?php if (!empty($d['photo'])): ?>
          <img src="<?= e(media($d['photo'])) ?>" class="w-full h-full object-cover">
        <?php else: ?>
          <span class="text-xl font-bold text-slate-400"><?= e(initials($fullName ?: 'D')) ?></span>
        <?php endif; ?>
      </div>
      <h3 class="mt-3 font-display font-bold text-navy dark:text-white truncate group-hover:text-brand transition"><?= e($fullName) ?: '—' ?></h3>
      <p class="text-xs text-slate-400 mt-0.5 truncate">
        <?php if (!empty($d['license_number'])): ?><i data-lucide="id-card" class="w-3 h-3 inline -mt-0.5"></i> <?= e($d['license_number']) ?><?php endif; ?>
        <?php if (!empty($d['phone'])): ?> · <?= e($d['phone']) ?><?php endif; ?>
      </p>
      <?php if (!empty($d['phone']) || !empty($d['email'])): ?>
        <div class="mt-2" @click.stop>
          <?= \App\Core\View::renderPartial('_partials/contact_actions', [
            'phone'    => $d['phone'] ?? null,
            'whatsapp' => $d['phone'] ?? null,
            'email'    => $d['email'] ?? null,
            'country'  => $tenant['country'] ?? 'DO',
            'message'  => 'Hola ' . e($fullName) . ', te contacto desde ' . ($tenant['name'] ?? 'Kyros Rent Car') . '.',
            'size'     => 'sm',
          ]) ?>
        </div>
      <?php endif; ?>
      <div class="flex items-end justify-between mt-3 pt-3 border-t hairline">
        <div>
          <p class="text-[11px] text-slate-400">Tarifa diaria</p>
          <p class="text-lg font-extrabold text-navy dark:text-white tnum"><?= money($d['daily_rate']) ?></p>
        </div>
        <div class="text-right">
          <p class="text-[11px] text-slate-400">Viajes</p>
          <p class="text-sm font-bold text-navy dark:text-white tnum"><?= (int)$d['trips_count'] ?></p>
        </div>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
