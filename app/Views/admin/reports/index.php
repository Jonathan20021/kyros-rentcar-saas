<?php
$income12 = array_sum(array_map(fn($r)=>(float)$r['total'], $monthlyIncome));
$expense12 = array_sum($monthlyExpenses ?? []);
$net12 = $income12 - $expense12;
$resTotal = array_sum($reservationStatus);
$topRevenue = !empty($topVehicles) ? (float)$topVehicles[0]['revenue'] : 0;
$kpis = [
  ['Ingresos (12m)', money($income12), 'dollar-sign', 'bg-emerald-50 text-emerald-600'],
  ['Gastos (12m)', money($expense12), 'trending-down', 'bg-red-50 text-brand'],
  ['Utilidad neta', money($net12), 'wallet', $net12>=0?'bg-emerald-50 text-emerald-600':'bg-red-50 text-brand'],
  ['Reservas totales', (string)$resTotal, 'calendar-check', 'bg-indigo-50 text-indigo-600', true],
];
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Reportes</h1>
    <p class="text-sm text-slate-500">Análisis de tu operación</p>
  </div>
  <div class="flex items-center gap-2" x-data="{m:'<?= date('Y-m') ?>'}">
    <input type="month" x-model="m" class="fld !h-10 !w-auto">
    <a :href="'<?= url('/admin/reports/pnl') ?>?month='+m" target="_blank" class="k-btn k-btn-outline !h-10"><i data-lucide="file-text" class="w-4 h-4"></i> Estado de resultados</a>
  <?php if (can('reports.export')): ?>
  <div class="relative" x-data="{open:false}">
    <button @click="open=!open" class="k-btn k-btn-dark !h-10"><i data-lucide="download" class="w-4 h-4"></i> CSV <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
    <div x-show="open" x-cloak @click.outside="open=false" x-transition.origin.top.right class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-soft border hairline py-1.5 z-20">
      <a href="<?= url('/admin/reports/export/income') ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-paper"><i data-lucide="line-chart" class="w-4 h-4 text-slate-400"></i> Ingresos mensuales</a>
      <a href="<?= url('/admin/reports/export/vehicles') ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-paper"><i data-lucide="car" class="w-4 h-4 text-slate-400"></i> Vehículos más rentados</a>
      <a href="<?= url('/admin/reports/export/payments') ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-paper"><i data-lucide="credit-card" class="w-4 h-4 text-slate-400"></i> Pagos</a>
      <a href="<?= url('/admin/reports/export/expenses') ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-paper"><i data-lucide="trending-down" class="w-4 h-4 text-slate-400"></i> Gastos</a>
    </div>
  </div>
  <?php endif; ?>
  </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <?php foreach ($kpis as $k): ?>
  <div class="card p-5 reveal">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl grid place-items-center <?= $k[3] ?>"><i data-lucide="<?= $k[2] ?>" class="w-[18px] h-[18px]"></i></div>
      <p class="text-[13px] text-slate-400 font-medium"><?= $k[0] ?></p>
    </div>
    <p class="mt-3 text-[24px] leading-none font-extrabold text-navy dark:text-white tnum"<?= !empty($k[4])?' data-count="'.(int)$k[1].'"':'' ?>><?= !empty($k[4]) ? '0' : e($k[1]) ?></p>
  </div>
  <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  <div class="lg:col-span-2 card p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-display font-bold text-navy dark:text-white">Ingresos vs Gastos</h2>
      <div class="flex items-center gap-3 text-xs text-slate-500">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand"></span>Ingresos</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>Gastos</span>
      </div>
    </div>
    <canvas id="repIncome" height="120"></canvas>
  </div>
  <div class="card p-6">
    <h2 class="font-display font-bold text-navy dark:text-white mb-4">Reservas por estado</h2>
    <canvas id="repRes" height="160"></canvas>
  </div>
</div>

<?php if (!empty($expensesByCategory)): $catTotal = array_sum(array_map(fn($r)=>(float)$r['total'],$expensesByCategory)); ?>
<div class="card p-6 mt-5 reveal">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-display font-bold text-navy dark:text-white">Gastos por categoría <span class="text-sm font-normal text-slate-400">(este año)</span></h2>
    <span class="font-extrabold text-brand tnum"><?= money($catTotal) ?></span>
  </div>
  <div class="space-y-3">
    <?php
    $catLabels = \App\Models\Expense::CATEGORIES;
    foreach ($expensesByCategory as $row): $pct = $catTotal>0 ? round((float)$row['total']/$catTotal*100) : 0; ?>
    <div>
      <div class="flex items-center justify-between text-sm mb-1.5">
        <span class="font-medium text-navy dark:text-white"><?= e($catLabels[$row['category']] ?? $row['category']) ?> <span class="text-slate-400 font-normal">· <?= (int)$row['cnt'] ?></span></span>
        <span class="text-slate-500 tnum"><?= money($row['total']) ?> <span class="text-slate-400">(<?= $pct ?>%)</span></span>
      </div>
      <div class="progress reveal-s"><i style="width:<?= $pct ?>%"></i></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="card overflow-hidden mt-5">
  <div class="px-6 py-4 border-b hairline font-display font-bold text-navy">Vehículos más rentados</div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-paper"><tr><th class="px-6 py-3 font-medium">Vehículo</th><th class="px-6 py-3 font-medium">Placa</th><th class="px-6 py-3 font-medium">Rentas</th><th class="px-6 py-3 font-medium">Ingresos</th></tr></thead>
      <tbody class="divide-y hairline">
        <?php foreach ($topVehicles as $i=>$v): ?>
        <tr class="hover:bg-paper">
          <td class="px-6 py-3"><span class="font-medium text-navy"><?= e($v['brand'].' '.$v['model']) ?></span></td>
          <td class="px-6 py-3 text-slate-500"><?= e($v['plate_number'] ?? '-') ?></td>
          <td class="px-6 py-3"><span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full bg-navy/5 text-navy text-xs font-semibold tnum"><?= (int)$v['rentals'] ?></span></td>
          <td class="px-6 py-3 font-semibold text-navy tnum"><?= money($v['revenue']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($topVehicles)): ?><tr><td colspan="4" class="px-6 py-10 text-center text-slate-400">Sin datos</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (!empty($branchPerformance)): ?>
<div class="card overflow-hidden mt-5 reveal">
  <div class="px-6 py-4 border-b hairline font-display font-bold text-navy dark:text-white">Rendimiento por sucursal</div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-paper dark:bg-slate-800/50"><tr><th class="px-6 py-3 font-medium">Sucursal</th><th class="px-6 py-3 font-medium">Flotilla</th><th class="px-6 py-3 font-medium">Ingresos</th><th class="px-6 py-3 font-medium">Gastos</th><th class="px-6 py-3 font-medium text-right">Neto</th></tr></thead>
      <tbody class="divide-y hairline">
        <?php foreach ($branchPerformance as $b): $bnet = (float)$b['revenue'] - (float)$b['expenses']; ?>
        <tr class="hover:bg-paper dark:hover:bg-slate-800/40">
          <td class="px-6 py-3"><span class="font-medium text-navy dark:text-white flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand/70"></i><?= e($b['name']) ?></span></td>
          <td class="px-6 py-3 text-slate-500 tnum"><?= (int)$b['vehicles'] ?> uds</td>
          <td class="px-6 py-3 text-emerald-600 font-medium tnum"><?= money($b['revenue']) ?></td>
          <td class="px-6 py-3 text-slate-500 tnum"><?= money($b['expenses']) ?></td>
          <td class="px-6 py-3 text-right font-bold tnum <?= $bnet>=0?'text-navy dark:text-white':'text-brand' ?>"><?= money($bnet) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php
$incomeLabels = array_map(fn($r)=>$r['ym'], $monthlyIncome);
$incomeValues = array_map(fn($r)=>(float)$r['total'], $monthlyIncome);
$expenseValues = array_map(fn($r)=>(float)($monthlyExpenses[$r['ym']] ?? 0), $monthlyIncome);
$resLabels = array_map(fn($k)=>status_label($k), array_keys($reservationStatus));
$resValues = array_values($reservationStatus);
\App\Core\View::push('scripts', '<script>
(function(){
  Chart.defaults.font.family="Inter"; Chart.defaults.color="#9aa3b2";
  var brand="#F23645", navy="#1C2433";
  var i=document.getElementById("repIncome");
  if(i){ var ctx=i.getContext("2d"); var g=ctx.createLinearGradient(0,0,0,200); g.addColorStop(0,"rgba(242,54,69,.85)"); g.addColorStop(1,"rgba(242,54,69,.55)");
    new Chart(i,{type:"bar",data:{labels:'.json_encode($incomeLabels).',datasets:[
      {label:"Ingresos",data:'.json_encode($incomeValues).',backgroundColor:g,borderRadius:7,maxBarThickness:26},
      {label:"Gastos",data:'.json_encode($expenseValues).',backgroundColor:"#CBD5E1",borderRadius:7,maxBarThickness:26}
    ]},options:{plugins:{legend:{display:false},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10,mode:"index",intersect:false}},scales:{y:{beginAtZero:true,grid:{color:"#EEF1F6"},border:{display:false}},x:{grid:{display:false},border:{display:false}}}}});
  }
  var r=document.getElementById("repRes");
  if(r) new Chart(r,{type:"doughnut",data:{labels:'.json_encode($resLabels).',datasets:[{data:'.json_encode($resValues).',backgroundColor:[navy,brand,"#FF8FA0","#94A3B8","#3B82F6","#8B5CF6","#06B6D4"],borderWidth:0,hoverOffset:6}]},options:{cutout:"66%",plugins:{legend:{position:"bottom",labels:{boxWidth:9,font:{size:10},padding:10,usePointStyle:true}}}}});
})();
</script>'); ?>
