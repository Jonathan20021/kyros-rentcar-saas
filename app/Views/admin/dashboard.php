<?php
use App\Core\View;
$first = explode(' ', $_auth['name'] ?? 'Usuario')[0];
function trend_pill(float $v): string {
  $up = $v >= 0;
  $cls = $up ? 'trend-up' : 'trend-down';
  $icon = $up ? '&#8593;' : '&#8595;';
  return '<span class="trend '.$cls.'">'.$icon.' '.($up?'+':'').number_format($v,2).'%</span>';
}
$kpis = [
  ['label'=>'Ingresos del mes','value'=>money($stats['income_month']),'icon'=>'dollar-sign','tint'=>'bg-brand/10 text-brand','pill'=>trend_pill($trends['revenue']),'sub'=>'vs. mes anterior'],
  ['label'=>'Reservas (semana)','value'=>$stats['reservations_today'],'icon'=>'calendar-plus','tint'=>'bg-indigo-50 text-indigo-600','pill'=>trend_pill($trends['bookings']),'sub'=>'creadas esta semana','count'=>true],
  ['label'=>'Vehiculos rentados','value'=>$stats['rented'],'icon'=>'car-front','tint'=>'bg-amber-50 text-amber-600','pill'=>'<span class="trend trend-up">'.($stats['rented']).' uds</span>','sub'=>'en circulacion','count'=>true],
  ['label'=>'Disponibles','value'=>$stats['available'],'icon'=>'circle-check-big','tint'=>'bg-emerald-50 text-emerald-600','pill'=>'<span class="trend trend-up">listos</span>','sub'=>'para reservar','count'=>true],
];
$rs = $reservationStatus;
$hired = $rs['converted'] + $rs['in_progress'];
$pend  = $rs['pending'] + $rs['confirmed'];
$canc  = $rs['cancelled'] + $rs['rejected'];
?>
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5 sm:mb-6">
  <div class="min-w-0">
    <div class="flex items-center gap-2 mb-1.5">
      <span class="inline-block h-1 w-8 rounded-full grad-bg"></span>
      <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400"><?= ucfirst(date('l')) ?></span>
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
    <a href="<?= url('/admin/vehicles/create') ?>" class="k-btn k-btn-grad !h-10"><i data-lucide="plus" class="w-4 h-4"></i><span>Vehículo</span></a>
  </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-4 sm:gap-5">
  <!-- LEFT column -->
  <div class="space-y-4 sm:space-y-5">
    <!-- KPI row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <?php foreach ($kpis as $k): ?>
      <div class="card p-4 sm:p-5 reveal hover:-translate-y-0.5 hover:shadow-lift transition-all duration-200">
        <div class="flex items-center justify-between gap-1">
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl grid place-items-center shrink-0 <?= $k['tint'] ?>"><i data-lucide="<?= $k['icon'] ?>" class="w-4 h-4 sm:w-5 sm:h-5"></i></div>
          <?= $k['pill'] ?>
        </div>
        <p class="mt-3 sm:mt-3.5 text-[22px] sm:text-[26px] leading-none font-extrabold text-navy dark:text-white tracking-tight tnum truncate">
          <?php if (!empty($k['count'])): ?><span data-count="<?= (int)$k['value'] ?>">0</span><?php else: ?><?= e($k['value']) ?><?php endif; ?>
        </p>
        <p class="text-[12px] sm:text-[13px] text-slate-400 mt-1.5 truncate"><?= e($k['label']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Earnings + Rent status -->
    <div class="grid lg:grid-cols-[1fr_300px] gap-5">
      <div class="card p-6 reveal">
        <div class="flex items-center justify-between mb-1">
          <h2 class="font-display font-bold text-navy dark:text-white">Ingresos vs Gastos</h2>
          <span class="text-xs font-medium text-slate-400 px-2.5 py-1 rounded-lg bg-paper">Últimos 12 meses</span>
        </div>
        <?php $netMonth = $stats['income_month'] - $stats['expense_month']; $netUp = $netMonth >= 0; ?>
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1 mb-4 text-[13px]">
          <span class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-brand"></span>Ingresos <b class="text-navy dark:text-white tnum"><?= money($stats['income_month']) ?></b></span>
          <span class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>Gastos <b class="text-navy dark:text-white tnum"><?= money($stats['expense_month']) ?></b></span>
          <span class="flex items-center gap-1.5 text-slate-500">Neto <b class="tnum <?= $netUp?'text-emerald-600':'text-brand' ?>"><?= money($netMonth) ?></b></span>
        </div>
        <canvas id="incomeChart" height="118"></canvas>
      </div>
      <div class="card p-6 reveal">
        <h2 class="font-display font-bold text-navy mb-4">Estado de reservas</h2>
        <div class="relative">
          <canvas id="statusChart" height="180"></canvas>
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

    <!-- Bookings overview bar -->
    <div class="card p-6 reveal">
      <div class="flex items-center justify-between mb-4">
        <div><h2 class="font-display font-bold text-navy">Reservas por mes</h2><p class="text-[13px] text-slate-400">Volumen del año</p></div>
        <span class="text-xs font-medium text-slate-400 px-2.5 py-1 rounded-lg bg-paper">Este año</span>
      </div>
      <canvas id="bookingsChart" height="90"></canvas>
    </div>
  </div>

  <!-- RIGHT rail — at md/lg displays as 2-column grid below the main column,
       at xl+ becomes the actual rail. -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-4 sm:gap-5">
    <!-- Quick availability -->
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

    <!-- Car types -->
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

    <!-- Branches -->
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

    <!-- Reminders -->
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
// bookings per month (current year) from reservations
$bookValues = array_fill(0,12,0);
foreach (\App\Core\Database::select("SELECT MONTH(start_datetime) m, COUNT(*) c FROM reservations WHERE tenant_id=".((int)$tenant['id'])." AND deleted_at IS NULL AND YEAR(start_datetime)=YEAR(CURDATE()) GROUP BY m") as $b) { $bookValues[(int)$b['m']-1]=(int)$b['c']; }
$peak = array_keys($bookValues, max($bookValues))[0] ?? 0;
View::push('scripts', '<script>
(function(){
  Chart.defaults.font.family="Inter"; Chart.defaults.font.size=11; Chart.defaults.color="#9aa3b2";
  var brand="#F23645", navy="#1C2433";
  var inc=document.getElementById("incomeChart");
  if(inc){ var ctx=inc.getContext("2d"); var g=ctx.createLinearGradient(0,0,0,200); g.addColorStop(0,"rgba(242,54,69,.20)"); g.addColorStop(1,"rgba(242,54,69,0)");
    new Chart(inc,{type:"line",data:{labels:'.json_encode($incomeLabels).',datasets:[
      {label:"Ingresos",data:'.json_encode($incomeValues).',borderColor:brand,backgroundColor:g,fill:true,tension:.45,pointRadius:0,pointHoverRadius:5,pointHoverBackgroundColor:brand,borderWidth:3},
      {label:"Gastos",data:'.json_encode($expenseValues).',borderColor:"#94A3B8",backgroundColor:"transparent",fill:false,tension:.45,pointRadius:0,pointHoverRadius:5,borderWidth:2,borderDash:[5,4]}
    ]},options:{plugins:{legend:{display:false},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10,mode:"index",intersect:false}},scales:{y:{beginAtZero:true,grid:{color:"#EEF1F6"},border:{display:false},ticks:{padding:8}},x:{grid:{display:false},border:{display:false}}}}});
  }
  var st=document.getElementById("statusChart");
  if(st) new Chart(st,{type:"doughnut",data:{labels:["Activas","Pendientes","Canceladas"],datasets:[{data:['.($hired).','.($pend).','.($canc).'],backgroundColor:[navy,brand,"#E2E8F0"],borderWidth:0,hoverOffset:6}]},options:{cutout:"72%",plugins:{legend:{display:false},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10}}}});
  var bk=document.getElementById("bookingsChart");
  if(bk){ var peak='.$peak.'; var vals='.json_encode($bookValues).'; var cols=vals.map(function(_,i){return i===peak?brand:navy;});
    new Chart(bk,{type:"bar",data:{labels:'.json_encode($bookLabels).',datasets:[{data:vals,backgroundColor:cols,borderRadius:7,maxBarThickness:28}]},
      plugins:[{id:"countOnBar",afterDatasetsDraw:function(c){var ctx=c.ctx;ctx.save();ctx.font="600 11px Inter";ctx.fillStyle="#475569";ctx.textAlign="center";c.data.datasets[0].data.forEach(function(v,i){if(v<=0)return;var b=c.getDatasetMeta(0).data[i];ctx.fillText(v, b.x, b.y - 6);});ctx.restore();}}],
      options:{plugins:{legend:{display:false},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10,displayColors:false}},scales:{y:{beginAtZero:true,grid:{color:"#EEF1F6"},border:{display:false},ticks:{padding:8}},x:{grid:{display:false},border:{display:false}}}}});
  }
})();
</script>');
?>
