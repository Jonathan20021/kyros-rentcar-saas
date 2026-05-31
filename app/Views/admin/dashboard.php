<?php
use App\Core\View;
$first = explode(' ', $_auth['name'] ?? 'Usuario')[0];
function trend_pill(float $v): string {
  $up = $v >= 0;
  $cls = $up ? 'trend-up' : 'trend-down';
  $icon = $up ? '&#8593;' : '&#8595;';
  return '<span class="trend '.$cls.'">'.$icon.' '.($up?'+':'').number_format($v,2).'%</span>';
}
$revSpark = $revenueSparkline ?? [];
$resSpark = $reservationSparkline ?? [];
$occ = $occupation ?? ['busy'=>0,'total'=>0,'pct'=>0];
$pulse = $pulse ?? ['new_reservations'=>0,'pickups_today'=>0,'returns_today'=>0,'payments_today'=>0];

$kpis = [
  [
    // Compact format guarantees readability at every breakpoint. Full value
    // shown via `title` tooltip on hover.
    'label'=>'Ingresos del mes',
    'value'=>money_compact($stats['income_month']),
    'fullValue'=>money($stats['income_month']),
    'icon'=>'dollar-sign','tint'=>'bg-emerald-50 text-emerald-600',
    'pill'=>trend_pill($trends['revenue']),'sub'=>'vs. mes anterior',
    'spark' => array_column($revSpark, 'value'), 'sparkColor' => '#10B981',
  ],
  [
    'label'=>'Reservas (semana)','value'=>$stats['reservations_today'],
    'icon'=>'calendar-plus','tint'=>'bg-indigo-50 text-indigo-600',
    'pill'=>trend_pill($trends['bookings']),'sub'=>'creadas esta semana','count'=>true,
    'spark' => array_column($resSpark, 'value'), 'sparkColor' => '#6366F1',
  ],
  [
    'label'=>'Ocupación de flotilla','value'=>$occ['pct'].'%',
    'icon'=>'gauge','tint'=>'bg-brand/10 text-brand',
    'pill'=>'<span class="trend '.($occ['pct']>=70?'trend-up':'trend-down').'">'.$occ['busy'].'/'.$occ['total'].'</span>',
    'sub'=>'vehículos rentados',
  ],
  [
    'label'=>'Vehiculos rentados','value'=>$stats['rented'],
    'icon'=>'car-front','tint'=>'bg-amber-50 text-amber-600',
    'pill'=>'<span class="trend trend-up">'.($stats['rented']).' uds</span>',
    'sub'=>'en circulación','count'=>true,
  ],
  [
    'label'=>'Disponibles','value'=>$stats['available'],
    'icon'=>'circle-check-big','tint'=>'bg-cyan-50 text-cyan-600',
    'pill'=>'<span class="trend trend-up">listos</span>',
    'sub'=>'para reservar','count'=>true,
  ],
];
$rs = $reservationStatus;
$hired = $rs['converted'] + $rs['in_progress'];
$pend  = $rs['pending'] + $rs['confirmed'];
$canc  = $rs['cancelled'] + $rs['rejected'];
?>

<!-- HEADER -->
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5 sm:mb-6">
  <div class="min-w-0">
    <div class="flex items-center gap-2 mb-1.5">
      <span class="inline-block h-1 w-8 rounded-full grad-bg"></span>
      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400"><?= ucfirst(date('l')) ?> · <?= e(date('d M Y')) ?></span>
    </div>
    <h1 class="font-display text-[22px] sm:text-[28px] font-extrabold text-navy dark:text-white tracking-tight leading-tight">Buen día, <?= e($first) ?></h1>
    <p class="text-[13px] text-slate-500 mt-1 truncate"><?= e($tenant['name'] ?? '') ?> · <?= strftime_es() ?></p>
  </div>
  <div class="flex flex-wrap gap-2">
    <?php if (!empty($branchOptions)): ?>
    <select onchange="location.href='<?= url('/admin/dashboard') ?>'+(this.value?('?location_id='+this.value):'')" class="fld !h-10 !w-auto flex-1 sm:flex-none !text-[13px]">
      <option value="">Todas las sucursales</option>
      <?php foreach ($branchOptions as $bo): ?><option value="<?= $bo['id'] ?>" <?= (($selectedLoc ?? null)==$bo['id'])?'selected':'' ?>><?= e($bo['name']) ?></option><?php endforeach; ?>
    </select>
    <?php endif; ?>
    <a href="<?= url('/admin/reservations/calendar') ?>" class="k-btn k-btn-outline !h-10"><i data-lucide="calendar" class="w-4 h-4"></i><span class="hidden sm:inline">Calendario</span></a>
    <a href="<?= url('/admin/reservations/create') ?>" class="k-btn k-btn-grad !h-10"><i data-lucide="plus" class="w-4 h-4"></i><span>Nueva reserva</span></a>
  </div>
</div>

<!-- QUICK ACTIONS STRIP -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 mb-5">
  <?php foreach ([
    ['Cobrar pago','credit-card','/admin/payments/create','bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500/15'],
    ['Nuevo contrato','file-text','/admin/contracts/create','bg-brand/10 text-brand hover:bg-brand/15'],
    ['Nuevo cliente','user-plus','/admin/customers/create','bg-indigo-500/10 text-indigo-600 hover:bg-indigo-500/15'],
    ['Nuevo vehículo','car','/admin/vehicles/create','bg-amber-500/10 text-amber-600 hover:bg-amber-500/15'],
  ] as $qa): ?>
    <a href="<?= url($qa[2]) ?>" class="group rounded-2xl p-3.5 sm:p-4 transition border hairline hover:border-transparent hover:shadow-lift <?= $qa[3] ?>">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl grid place-items-center bg-white/60 dark:bg-slate-900/40 shrink-0">
          <i data-lucide="<?= $qa[1] ?>" class="w-4 h-4"></i>
        </div>
        <p class="font-display font-bold text-[13.5px] truncate flex-1"><?= e($qa[0]) ?></p>
        <i data-lucide="arrow-right" class="w-4 h-4 opacity-50 group-hover:opacity-100 group-hover:translate-x-0.5 transition"></i>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-4 sm:gap-5">
  <!-- ====== LEFT COLUMN ====== -->
  <div class="space-y-4 sm:space-y-5 min-w-0">

    <!-- KPI ROW WITH MINI SPARKLINES -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
      <?php foreach ($kpis as $i => $k): ?>
      <div class="card p-4 sm:p-5 reveal hover:-translate-y-0.5 hover:shadow-lift transition-all duration-200 overflow-hidden"
           <?php if (!empty($k['fullValue'])): ?>title="<?= e($k['fullValue']) ?>"<?php endif; ?>>
        <div class="flex items-center justify-between gap-1 min-w-0">
          <div class="w-9 h-9 rounded-xl grid place-items-center shrink-0 <?= $k['tint'] ?>"><i data-lucide="<?= $k['icon'] ?>" class="w-4 h-4"></i></div>
          <span class="shrink-0"><?= $k['pill'] ?></span>
        </div>
        <!-- Value: no `truncate` so the full number always reads. Uses
             compact format (77.5K) when source >= 10k so it stays one line. -->
        <p class="mt-3 text-[19px] sm:text-[22px] leading-tight font-extrabold text-navy dark:text-white tracking-tight tnum break-words">
          <?php if (!empty($k['count'])): ?><span data-count="<?= (int)$k['value'] ?>">0</span><?php else: ?><?= e($k['value']) ?><?php endif; ?>
        </p>
        <p class="text-[12px] text-slate-400 mt-1 truncate"><?= e($k['label']) ?></p>
        <?php if (!empty($k['spark']) && count($k['spark']) > 1): ?>
          <!-- Wrapper locks height so the canvas can never stretch -->
          <div class="relative mt-2 h-[28px] w-full">
            <canvas data-spark='<?= json_encode($k['spark']) ?>' data-spark-color="<?= e($k['sparkColor'] ?? '#F23645') ?>"></canvas>
          </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- PULSE OF THE DAY -->
    <div class="card p-5 sm:p-6 reveal">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2.5">
          <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand opacity-60"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-brand"></span></span>
          <h2 class="font-display font-bold text-navy dark:text-white">Pulso del día</h2>
        </div>
        <span class="text-xs text-slate-400"><?= e(date('d/m/Y')) ?></span>
      </div>
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <?php foreach ([
          ['Reservas nuevas',  $pulse['new_reservations'],          'calendar-plus', 'text-indigo-600 bg-indigo-50',  null],
          ['Entregas',         $pulse['pickups_today'],             'key-round',     'text-amber-600 bg-amber-50',    null],
          ['Devoluciones',     $pulse['returns_today'],             'rotate-ccw',    'text-emerald-600 bg-emerald-50',null],
          ['Cobros del día',   money_compact($pulse['payments_today']),'credit-card','text-brand bg-brand/10',         money($pulse['payments_today'])],
        ] as $cell): ?>
        <div class="rounded-xl border hairline p-3" <?php if ($cell[4]): ?>title="<?= e($cell[4]) ?>"<?php endif; ?>>
          <div class="flex items-center gap-2 min-w-0">
            <div class="w-7 h-7 rounded-lg grid place-items-center shrink-0 <?= $cell[3] ?>"><i data-lucide="<?= $cell[2] ?>" class="w-3.5 h-3.5"></i></div>
            <p class="text-[11.5px] text-slate-500 truncate flex-1"><?= e($cell[0]) ?></p>
          </div>
          <p class="font-display text-[18px] sm:text-[20px] leading-tight font-extrabold text-navy dark:text-white mt-2 tnum break-words"><?= $cell[4] ? e($cell[1]) : (int)$cell[1] ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Ingresos vs Gastos + Reservas por estado -->
    <div class="grid lg:grid-cols-[1fr_320px] gap-4 sm:gap-5">
      <div class="card p-5 sm:p-6 reveal min-w-0">
        <div class="flex items-center justify-between mb-1 flex-wrap gap-2">
          <h2 class="font-display font-bold text-navy dark:text-white">Ingresos vs Gastos</h2>
          <span class="text-xs font-medium text-slate-400 px-2.5 py-1 rounded-lg bg-paper">Últimos 12 meses</span>
        </div>
        <?php $netMonth = $stats['income_month'] - $stats['expense_month']; $netUp = $netMonth >= 0; ?>
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1 mb-4 text-[13px]">
          <span class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-brand"></span>Ingresos <b class="text-navy dark:text-white tnum"><?= money($stats['income_month']) ?></b></span>
          <span class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>Gastos <b class="text-navy dark:text-white tnum"><?= money($stats['expense_month']) ?></b></span>
          <span class="flex items-center gap-1.5 text-slate-500">Neto <b class="tnum <?= $netUp?'text-emerald-600':'text-brand' ?>"><?= money($netMonth) ?></b></span>
        </div>
        <div class="relative h-[220px] sm:h-[260px]">
          <canvas id="incomeChart"></canvas>
        </div>
      </div>
      <div class="card p-5 sm:p-6 reveal min-w-0">
        <h2 class="font-display font-bold text-navy mb-4">Estado de reservas</h2>
        <div class="relative h-[180px]">
          <canvas id="statusChart"></canvas>
        </div>
        <div class="mt-5 space-y-2.5 text-sm">
          <?php
          $tot = max(1, $hired+$pend+$canc);
          $rows = [['Activas',$hired,'bg-navy'],['Pendientes',$pend,'bg-brand'],['Canceladas',$canc,'bg-slate-200']];
          foreach ($rows as $r): ?>
          <div class="flex items-center justify-between">
            <span class="flex items-center gap-2 text-slate-500"><span class="w-2.5 h-2.5 rounded-full <?= $r[2] ?>"></span><?= $r[0] ?></span>
            <span class="font-semibold text-navy tnum"><?= round($r[1]/$tot*100) ?>%</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Reservas por mes -->
    <div class="card p-5 sm:p-6 reveal">
      <div class="flex items-center justify-between mb-4">
        <div><h2 class="font-display font-bold text-navy">Reservas por mes</h2><p class="text-[13px] text-slate-400">Volumen del año</p></div>
        <span class="text-xs font-medium text-slate-400 px-2.5 py-1 rounded-lg bg-paper">Este año</span>
      </div>
      <div class="relative h-[200px] sm:h-[240px]">
        <canvas id="bookingsChart"></canvas>
      </div>
    </div>

    <!-- Upcoming returns + Recent activity (2col on lg) -->
    <div class="grid lg:grid-cols-2 gap-4 sm:gap-5">
      <!-- Próximas devoluciones -->
      <div class="card p-5 sm:p-6 reveal">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-display font-bold text-navy dark:text-white">Próximas devoluciones</h2>
          <a href="<?= url('/admin/contracts') ?>" class="text-xs font-semibold text-brand hover:underline">Ver todos</a>
        </div>
        <?php if (empty($upcomingReturns ?? [])): ?>
          <div class="text-center py-6">
            <div class="inline-flex w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 items-center justify-center"><i data-lucide="rotate-ccw" class="w-5 h-5"></i></div>
            <p class="text-sm text-slate-400 mt-2">Sin devoluciones próximas</p>
          </div>
        <?php else: ?>
          <ol class="relative space-y-3 border-l-2 hairline pl-4 ml-1.5">
            <?php foreach ($upcomingReturns as $u):
              $ts = strtotime($u['end_datetime']);
              $hrs = max(0, (int) round(($ts - time()) / 3600));
              $tone = $hrs <= 24 ? 'bg-red-500' : ($hrs <= 72 ? 'bg-amber-500' : 'bg-slate-300');
            ?>
            <li class="relative">
              <span class="absolute -left-[22px] top-1 w-3 h-3 rounded-full ring-2 ring-white dark:ring-slate-900 <?= $tone ?>"></span>
              <a href="<?= url('/admin/contracts/show/'.$u['id']) ?>" class="block hover:bg-paper/60 dark:hover:bg-slate-800/30 -mx-2 px-2 py-1.5 rounded-lg transition">
                <p class="font-semibold text-navy dark:text-white text-[13.5px] truncate"><?= e($u['brand'].' '.$u['model']) ?> <span class="text-slate-400 font-normal">· <?= e($u['plate_number'] ?? '') ?></span></p>
                <p class="text-[12px] text-slate-500 truncate"><?= e($u['customer_name']) ?></p>
                <p class="text-[11px] text-slate-400 tnum mt-0.5">
                  <?= e(date('d/m H:i', $ts)) ?>
                  <span class="ml-1"><?= $hrs <= 0 ? '· Atrasado' : ($hrs < 24 ? '· en ' . $hrs . ' h' : '· en ' . round($hrs/24) . ' días') ?></span>
                </p>
              </a>
            </li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      </div>

      <!-- Top vehículos -->
      <div class="card p-5 sm:p-6 reveal">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-display font-bold text-navy dark:text-white">Top vehículos (90d)</h2>
          <a href="<?= url('/admin/reports') ?>" class="text-xs font-semibold text-brand hover:underline">Reporte</a>
        </div>
        <?php $topV = $topVehiclesMini ?? []; if (empty($topV) || (float)($topV[0]['revenue'] ?? 0) == 0): ?>
          <div class="text-center py-6">
            <div class="inline-flex w-10 h-10 rounded-xl bg-slate-100 text-slate-400 items-center justify-center"><i data-lucide="car-front" class="w-5 h-5"></i></div>
            <p class="text-sm text-slate-400 mt-2">Sin contratos en este período</p>
          </div>
        <?php else: ?>
          <div class="space-y-2.5">
            <?php
            $maxRev = max(1, (float) $topV[0]['revenue']);
            foreach ($topV as $i => $tv):
              $pct = (float) $tv['revenue'] > 0 ? round(((float)$tv['revenue'] / $maxRev) * 100) : 0;
            ?>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-800 grid place-items-center shrink-0">
                <?php if (!empty($tv['main_image'])): ?>
                  <img src="<?= e(media($tv['main_image'])) ?>" class="w-full h-full object-cover" alt="">
                <?php else: ?>
                  <i data-lucide="car-front" class="w-4 h-4 text-slate-400"></i>
                <?php endif; ?>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-[13px] font-semibold text-navy dark:text-white truncate"><?= e($tv['brand'].' '.$tv['model']) ?></p>
                  <p class="text-[12px] font-bold text-navy dark:text-white tnum shrink-0" title="<?= e(money($tv['revenue'])) ?>"><?= money_compact($tv['revenue']) ?></p>
                </div>
                <div class="flex items-center gap-2 mt-1">
                  <div class="flex-1 h-1 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full grad-bg" style="width: <?= max(2,$pct) ?>%"></div>
                  </div>
                  <span class="text-[10.5px] text-slate-400 tnum"><?= (int)$tv['contracts'] ?> ren.</span>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent activity feed -->
    <?php if (!empty($recentActivity ?? [])): ?>
    <div class="card p-5 sm:p-6 reveal">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-display font-bold text-navy dark:text-white">Actividad reciente</h2>
        <a href="<?= url('/admin/activity') ?>" class="text-xs font-semibold text-brand hover:underline">Ver todo</a>
      </div>
      <ul class="space-y-1">
        <?php
        $actIcons = [
          'created'=>['plus','text-emerald-500 bg-emerald-50'],
          'updated'=>['pencil','text-indigo-500 bg-indigo-50'],
          'deleted'=>['trash-2','text-red-500 bg-red-50'],
          'login'  =>['log-in','text-slate-500 bg-slate-100'],
          'logout' =>['log-out','text-slate-500 bg-slate-100'],
          'signed' =>['pen-line','text-amber-500 bg-amber-50'],
          'export' =>['download','text-cyan-500 bg-cyan-50'],
        ];
        foreach ($recentActivity as $act):
          [$ic, $cls] = $actIcons[$act['action']] ?? ['circle','text-slate-400 bg-slate-100'];
        ?>
        <li class="flex items-start gap-3 p-2.5 -mx-1 rounded-xl hover:bg-paper dark:hover:bg-slate-800/30 transition">
          <div class="w-8 h-8 rounded-lg grid place-items-center shrink-0 <?= $cls ?>"><i data-lucide="<?= $ic ?>" class="w-3.5 h-3.5"></i></div>
          <div class="flex-1 min-w-0">
            <p class="text-[13px] text-navy dark:text-white truncate"><?= e($act['description']) ?></p>
            <p class="text-[11px] text-slate-400 mt-0.5">
              <?= e($act['user_name'] ?? 'Sistema') ?>
              · <?= e(date('d/m H:i', strtotime($act['created_at']))) ?>
              <?php if (!empty($act['module'])): ?>· <span class="font-mono"><?= e($act['module']) ?></span><?php endif; ?>
            </p>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
  </div>

  <!-- ====== RIGHT RAIL ====== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-4 sm:gap-5 min-w-0">
    <!-- Disponibilidad rápida -->
    <div class="card p-5 reveal">
      <h2 class="font-display font-bold text-navy mb-3">Disponibilidad rápida</h2>
      <form method="GET" action="<?= url('/admin/vehicles') ?>" class="space-y-2.5">
        <select name="status" class="fld !text-[13px]"><option value="available">Disponibles</option><option value="reserved">Reservados</option><option value="rented">Rentados</option><option value="maintenance">Mantenimiento</option></select>
        <div class="grid grid-cols-2 gap-2.5">
          <div class="card !shadow-none px-3 flex items-center justify-between"><span class="text-[13px] text-slate-400">Total</span><span class="font-bold text-navy tnum"><?= $totalVeh ?></span></div>
          <div class="card !shadow-none px-3 flex items-center justify-between"><span class="text-[13px] text-slate-400">Libres</span><span class="font-bold text-emerald-600 tnum"><?= $stats['available'] ?></span></div>
        </div>
        <button class="k-btn k-btn-grad w-full">Ver flotilla</button>
      </form>
    </div>

    <!-- Tipos de vehículo -->
    <div class="card p-5 reveal">
      <div class="flex items-center justify-between mb-3"><h2 class="font-display font-bold text-navy">Tipos de vehículo</h2><a href="<?= url('/admin/vehicles') ?>" class="text-xs font-semibold text-brand hover:underline">Ver</a></div>
      <div class="space-y-3.5">
        <?php foreach ($carTypes as $ct): ?>
        <div>
          <div class="flex items-center justify-between text-sm mb-1.5"><span class="font-medium text-navy"><?= e($ct['name']) ?></span><span class="text-slate-400 tnum"><?= $ct['pct'] ?>%</span></div>
          <div class="progress reveal-s"><i style="width:<?= $ct['pct'] ?>%"></i></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($carTypes)): ?><p class="text-sm text-slate-400 py-2">Sin vehículos aún.</p><?php endif; ?>
      </div>
    </div>

    <!-- Sucursales -->
    <?php if (!empty($branches)): ?>
    <div class="card p-5 reveal">
      <div class="flex items-center justify-between mb-3"><h2 class="font-display font-bold text-navy dark:text-white">Sucursales</h2><a href="<?= url('/admin/locations') ?>" class="text-xs font-semibold text-brand hover:underline">Gestionar</a></div>
      <div class="space-y-3">
        <?php foreach ($branches as $b): $tot=(int)$b['total']; $av=(int)$b['available']; $pct = $tot>0 ? (int)round($av/$tot*100) : 0; ?>
        <a href="<?= url('/admin/vehicles?location_id='.$b['id']) ?>" class="block group">
          <div class="flex items-center justify-between text-sm mb-1.5">
            <span class="font-medium text-navy dark:text-white flex items-center gap-1.5 min-w-0"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand/70 shrink-0"></i><span class="truncate group-hover:text-brand transition"><?= e($b['name']) ?></span></span>
            <span class="text-slate-400 tnum shrink-0 ml-2"><span class="text-emerald-600 font-semibold"><?= $av ?></span>/<?= $tot ?></span>
          </div>
          <div class="progress reveal-s"><i style="width:<?= $pct ?>%"></i></div>
        </a>
        <?php endforeach; ?>
      </div>
      <p class="text-[11px] text-slate-400 mt-3 pt-3 border-t hairline">Vehículos disponibles por sucursal</p>
    </div>
    <?php endif; ?>

    <!-- Almacenamiento -->
    <?php $st = $storage; $stColor = $st['level']==='block'?'bg-red-500':($st['level']==='warn'?'bg-amber-500':'bg-emerald-500'); ?>
    <div class="card p-5 reveal">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-display font-bold text-navy dark:text-white">Almacenamiento</h2>
        <a href="<?= url('/admin/storage') ?>" class="text-xs font-semibold text-brand hover:underline">Gestionar</a>
      </div>
      <p class="font-display text-xl font-extrabold text-navy dark:text-white tnum"><?= e($st['used_human']) ?> <span class="text-slate-300 font-normal text-sm">/ <?= e($st['quota_human']) ?></span></p>
      <p class="text-[11.5px] text-slate-400 mt-0.5"><?= e($st['free_human']) ?> disponibles · <?= $st['percent'] ?>%</p>
      <div class="mt-3 h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
        <div class="h-full <?= $stColor ?> transition-all duration-500" style="width: <?= max(2, $st['percent']) ?>%"></div>
      </div>
      <?php if ($st['level'] === 'warn'): ?>
        <a href="<?= url('/admin/storage') ?>" class="mt-3 flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-[12px] font-medium hover:bg-amber-100 transition">
          <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Te acercas al límite · Solicita más
        </a>
      <?php elseif ($st['level'] === 'block'): ?>
        <a href="<?= url('/admin/storage') ?>" class="mt-3 flex items-center gap-2 px-3 py-2 rounded-lg bg-red-50 text-red-600 text-[12px] font-semibold hover:bg-red-100 transition">
          <i data-lucide="alert-octagon" class="w-3.5 h-3.5"></i> Cuota completa · Cargas bloqueadas
        </a>
      <?php endif; ?>
    </div>

    <!-- Recordatorios -->
    <div class="card p-5 reveal">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-display font-bold text-navy dark:text-white">Recordatorios</h2>
        <i data-lucide="bell" class="w-4 h-4 text-slate-300"></i>
      </div>
      <div class="space-y-2.5">
        <?php
        $today = date('Y-m-d');
        $reminders = [];
        foreach ($overdue as $o) {
          $reminders[] = ['t'=>'Devolución vencida: '.$o['brand'].' '.$o['model'],'d'=>$o['end_datetime'],'icon'=>'alert-triangle','c'=>'text-red-500','u'=>url('/admin/contracts')];
        }
        foreach ($docAlerts as $d) {
          $reminders[] = ['t'=>'Documento por vencer: '.$d['brand'].' '.$d['model'],'d'=>$d['nearest'],'icon'=>'calendar-clock','c'=>'text-amber-500','u'=>url('/admin/documents')];
        }
        foreach ($customerLicAlerts as $c) {
          $isExpired = $c['license_expiration'] < $today;
          $reminders[] = [
            't' => ($isExpired ? 'Licencia vencida: ' : 'Licencia por vencer: ') . trim($c['first_name'].' '.$c['last_name']),
            'd' => $c['license_expiration'],
            'icon' => 'id-card',
            'c' => $isExpired ? 'text-red-500' : 'text-amber-500',
            'u' => url('/admin/customers/show/'.$c['id']),
          ];
        }
        foreach ($driverLicAlerts as $dr) {
          $isExpired = $dr['license_expiration'] < $today;
          $reminders[] = [
            't' => ($isExpired ? 'Lic. de chofer vencida: ' : 'Lic. de chofer por vencer: ') . trim($dr['first_name'].' '.$dr['last_name']),
            'd' => $dr['license_expiration'],
            'icon' => 'id-card',
            'c' => $isExpired ? 'text-red-500' : 'text-amber-500',
            'u' => url('/admin/drivers/show/'.$dr['id']),
          ];
        }
        usort($reminders, fn($a,$b) => strcmp($a['d'] ?? '', $b['d'] ?? ''));
        $reminders = array_slice($reminders, 0, 5);
        foreach ($reminders as $r): ?>
        <a href="<?= e($r['u']) ?>" class="flex gap-3 p-3 rounded-xl bg-paper dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
          <i data-lucide="<?= e($r['icon']) ?>" class="w-4 h-4 mt-0.5 shrink-0 <?= $r['c'] ?>"></i>
          <div class="min-w-0">
            <p class="text-[13px] font-medium text-navy dark:text-white leading-snug truncate"><?= e($r['t']) ?></p>
            <p class="text-[11px] text-slate-400 mt-0.5 tnum"><?= format_date($r['d']) ?></p>
          </div>
        </a>
        <?php endforeach; ?>
        <?php if (empty($reminders)): ?>
          <div class="text-center py-6">
            <div class="inline-flex w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 items-center justify-center"><i data-lucide="check" class="w-5 h-5"></i></div>
            <p class="text-sm text-slate-400 mt-2">Todo al día</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$incomeLabels = array_map(fn($r) => $r['ym'], $monthlyIncome);
$incomeValues = array_map(fn($r) => (float) $r['total'], $monthlyIncome);
$expenseValues = array_map(fn($r) => (float) ($monthlyExpenses[$r['ym']] ?? 0), $monthlyIncome);
$monNames=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
$bookLabels = $monNames;
$bookValues = array_fill(0,12,0);
foreach (\App\Core\Database::select("SELECT MONTH(start_datetime) m, COUNT(*) c FROM reservations WHERE tenant_id=".((int)$tenant['id'])." AND deleted_at IS NULL AND YEAR(start_datetime)=YEAR(CURDATE()) GROUP BY m") as $b) { $bookValues[(int)$b['m']-1]=(int)$b['c']; }
$peak = array_keys($bookValues, max($bookValues))[0] ?? 0;
View::push('scripts', '<script>
(function(){
  // IMPORTANT: do NOT set Chart.defaults.maintainAspectRatio = false globally.
  // It makes any canvas without a fixed-height parent stretch to fill space.
  // Each chart below has its own maintainAspectRatio:false PLUS a fixed-height
  // wrapper div so the size is always locked.
  Chart.defaults.font.family="Inter"; Chart.defaults.font.size=11; Chart.defaults.color="#9aa3b2";
  var brand="#F23645", navy="#1C2433";
  var lockSize = function(opts){ opts = opts || {}; opts.responsive = true; opts.maintainAspectRatio = false; return opts; };

  // KPI sparklines — tiny line charts on each KPI card
  document.querySelectorAll("[data-spark]").forEach(function(cv){
    var data = JSON.parse(cv.getAttribute("data-spark") || "[]");
    var color = cv.getAttribute("data-spark-color") || brand;
    if (!data.length) return;
    var ctx = cv.getContext("2d");
    var g = ctx.createLinearGradient(0,0,0,30);
    g.addColorStop(0, color + "44"); g.addColorStop(1, color + "00");
    new Chart(cv, {
      type:"line",
      data:{labels:data.map(function(_,i){return i;}),
            datasets:[{data:data,borderColor:color,backgroundColor:g,fill:true,tension:.4,borderWidth:1.5,pointRadius:0}]},
      options:lockSize({plugins:{legend:{display:false},tooltip:{enabled:false}},
               scales:{x:{display:false},y:{display:false,beginAtZero:true}},
               elements:{line:{cubicInterpolationMode:"monotone"}}})
    });
  });

  var inc=document.getElementById("incomeChart");
  if(inc){ var ctx=inc.getContext("2d"); var g=ctx.createLinearGradient(0,0,0,200); g.addColorStop(0,"rgba(242,54,69,.20)"); g.addColorStop(1,"rgba(242,54,69,0)");
    new Chart(inc,{type:"line",data:{labels:'.json_encode($incomeLabels).',datasets:[
      {label:"Ingresos",data:'.json_encode($incomeValues).',borderColor:brand,backgroundColor:g,fill:true,tension:.45,pointRadius:0,pointHoverRadius:5,pointHoverBackgroundColor:brand,borderWidth:3},
      {label:"Gastos",data:'.json_encode($expenseValues).',borderColor:"#94A3B8",backgroundColor:"transparent",fill:false,tension:.45,pointRadius:0,pointHoverRadius:5,borderWidth:2,borderDash:[5,4]}
    ]},options:lockSize({plugins:{legend:{display:false},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10,mode:"index",intersect:false}},scales:{y:{beginAtZero:true,grid:{color:"#EEF1F6"},border:{display:false},ticks:{padding:8}},x:{grid:{display:false},border:{display:false}}}})});
  }
  var st=document.getElementById("statusChart");
  if(st) new Chart(st,{type:"doughnut",data:{labels:["Activas","Pendientes","Canceladas"],datasets:[{data:['.($hired).','.($pend).','.($canc).'],backgroundColor:[navy,brand,"#E2E8F0"],borderWidth:0,hoverOffset:6}]},options:lockSize({cutout:"72%",plugins:{legend:{display:false},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10}}})});
  var bk=document.getElementById("bookingsChart");
  if(bk){ var peak='.$peak.'; var vals='.json_encode($bookValues).'; var cols=vals.map(function(_,i){return i===peak?brand:navy;});
    new Chart(bk,{type:"bar",data:{labels:'.json_encode($bookLabels).',datasets:[{data:vals,backgroundColor:cols,borderRadius:7,maxBarThickness:28}]},
      plugins:[{id:"countOnBar",afterDatasetsDraw:function(c){var ctx=c.ctx;ctx.save();ctx.font="600 11px Inter";ctx.fillStyle="#475569";ctx.textAlign="center";c.data.datasets[0].data.forEach(function(v,i){if(v<=0)return;var b=c.getDatasetMeta(0).data[i];ctx.fillText(v, b.x, b.y - 6);});ctx.restore();}}],
      options:lockSize({plugins:{legend:{display:false},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10,displayColors:false}},scales:{y:{beginAtZero:true,grid:{color:"#EEF1F6"},border:{display:false},ticks:{padding:8}},x:{grid:{display:false},border:{display:false}}}})});
  }
})();
</script>');
?>
