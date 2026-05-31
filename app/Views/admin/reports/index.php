<?php
use App\Core\View;
$toneClass = [
  'emerald' => 'bg-emerald-50 text-emerald-600',
  'red'     => 'bg-red-50 text-red-600',
  'indigo'  => 'bg-indigo-50 text-indigo-600',
  'amber'   => 'bg-amber-50 text-amber-600',
  'cyan'    => 'bg-cyan-50 text-cyan-600',
  'brand'   => 'bg-brand/10 text-brand',
];
$paymentLabels = [
  'cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta',
  'paypal'=>'PayPal','stripe'=>'Stripe','azul'=>'Azul','cardnet'=>'Cardnet','other'=>'Otro',
];
$catLabels = \App\Models\Expense::CATEGORIES;
?>

<div x-data="{tab:'overview'}">

  <!-- HEADER + DATE RANGE -->
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-6">
    <div>
      <div class="flex items-center gap-2 mb-1.5">
        <span class="inline-block h-1 w-8 rounded-full grad-bg"></span>
        <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Análisis de tu operación</span>
      </div>
      <h1 class="font-display text-[22px] sm:text-[28px] font-extrabold text-navy dark:text-white tracking-tight">Reportes</h1>
      <p class="text-[13px] text-slate-500 mt-1">
        Del <span class="font-semibold text-navy"><?= e(date('d/m/Y', strtotime($from))) ?></span>
        al <span class="font-semibold text-navy"><?= e(date('d/m/Y', strtotime($to))) ?></span>
        · <?= $days ?> día<?= $days==1?'':'s' ?>
        <span class="text-slate-300">·</span>
        comparado con <?= e(date('d/m', strtotime($prevFrom))) ?>–<?= e(date('d/m', strtotime($prevTo))) ?>
      </p>
    </div>

    <!-- Date range picker + presets + actions -->
    <form method="GET" class="flex flex-wrap items-end gap-2">
      <div class="flex flex-wrap items-end gap-2 p-1 rounded-2xl bg-paper border hairline">
        <?php
          $presets = [
            'hoy'      => ['Hoy',         date('Y-m-d'), date('Y-m-d')],
            '7d'       => ['7 días',      date('Y-m-d', strtotime('-6 days')), date('Y-m-d')],
            '30d'      => ['30 días',     date('Y-m-d', strtotime('-29 days')), date('Y-m-d')],
            'mtd'      => ['Mes actual',  date('Y-m-01'), date('Y-m-d')],
            'last_m'   => ['Mes anterior',date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('first day of last month'))],
            'ytd'      => ['Año',         date('Y-01-01'), date('Y-m-d')],
          ];
          foreach ($presets as $key => [$lbl, $pf, $pt]):
            $active = ($from === $pf && $to === $pt);
        ?>
          <a href="<?= url('/admin/reports') ?>?from=<?= $pf ?>&to=<?= $pt ?>"
             class="px-3 py-1.5 rounded-xl text-[12px] font-semibold transition <?= $active ? 'bg-white text-navy shadow-sm border hairline' : 'text-slate-500 hover:text-navy' ?>"><?= e($lbl) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="flex items-end gap-2">
        <div><input type="date" name="from" value="<?= e($from) ?>" class="fld !h-10 !text-[13px] !w-auto"></div>
        <span class="text-slate-300 pb-2">→</span>
        <div><input type="date" name="to" value="<?= e($to) ?>" class="fld !h-10 !text-[13px] !w-auto"></div>
        <button class="k-btn k-btn-dark !h-10">Aplicar</button>
      </div>
      <div class="flex items-end gap-2">
        <a href="<?= url('/admin/reports/pnl') ?>?month=<?= e(substr($from, 0, 7)) ?>" target="_blank" class="k-btn k-btn-outline !h-10 hidden sm:inline-flex"><i data-lucide="file-text" class="w-4 h-4"></i> P&L</a>
        <?php if (can('reports.export')): ?>
        <div class="relative" x-data="{open:false}">
          <button type="button" @click="open=!open" class="k-btn k-btn-grad !h-10"><i data-lucide="download" class="w-4 h-4"></i> Exportar</button>
          <div x-show="open" x-cloak @click.outside="open=false" x-transition.origin.top.right class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-soft border hairline py-1.5 z-20">
            <a href="<?= url('/admin/reports/export/income') ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-paper"><i data-lucide="line-chart" class="w-4 h-4 text-slate-400"></i> Ingresos mensuales</a>
            <a href="<?= url('/admin/reports/export/vehicles') ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-paper"><i data-lucide="car" class="w-4 h-4 text-slate-400"></i> Vehículos más rentados</a>
            <a href="<?= url('/admin/reports/export/payments') ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-paper"><i data-lucide="credit-card" class="w-4 h-4 text-slate-400"></i> Pagos</a>
            <a href="<?= url('/admin/reports/export/expenses') ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-paper"><i data-lucide="trending-down" class="w-4 h-4 text-slate-400"></i> Gastos</a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- KPI CARDS WITH PERIOD COMPARISON -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-6">
    <?php foreach ($kpis as $k):
      $delta = (float) $k['delta'];
      $isGood = !empty($k['invertGood']) ? $delta <= 0 : $delta >= 0;
      $deltaClass = $isGood ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50';
      $arrow = $delta >= 0 ? '↑' : '↓';
      // Compact format for KPI display (so it never overflows a narrow card)
      // + full value for tooltip on hover.
      $fullValue = null;
      if (!empty($k['isMoney']))      { $valDisplay = money_compact($k['value']); $fullValue = money($k['value']); }
      elseif (!empty($k['isPercent'])){ $valDisplay = number_format($k['value'], 1) . '%'; }
      else                             { $valDisplay = number_format((int)$k['value']); }
    ?>
    <div class="card p-4 sm:p-5 reveal hover:-translate-y-0.5 hover:shadow-lift transition-all"
         <?php if ($fullValue): ?>title="<?= e($fullValue) ?>"<?php endif; ?>>
      <div class="flex items-center justify-between gap-1 mb-2.5 min-w-0">
        <div class="w-9 h-9 rounded-xl grid place-items-center shrink-0 <?= $toneClass[$k['tone']] ?? $toneClass['indigo'] ?>">
          <i data-lucide="<?= e($k['icon']) ?>" class="w-[18px] h-[18px]"></i>
        </div>
        <?php if (abs($delta) > 0.01 || !empty($k['isMoney'])): ?>
        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-lg <?= $deltaClass ?> tnum shrink-0">
          <?= $arrow ?> <?= number_format(abs($delta), 1) ?>%
        </span>
        <?php endif; ?>
      </div>
      <p class="font-display text-[18px] sm:text-[22px] leading-tight font-extrabold text-navy dark:text-white tnum break-words"><?= $valDisplay ?></p>
      <p class="text-[11.5px] sm:text-[12.5px] text-slate-400 mt-1.5 truncate"><?= e($k['label']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- TABS -->
  <div class="flex gap-1 mb-5 overflow-x-auto pb-1 -mx-1 px-1">
    <?php foreach ([
      'overview'=>['Resumen','layout-dashboard'],
      'income'  =>['Ingresos','arrow-down-to-line'],
      'expenses'=>['Gastos','arrow-up-from-line'],
      'fleet'   =>['Flotilla & clientes','car'],
    ] as $key => [$lbl, $icon]): ?>
    <button @click="tab='<?= $key ?>'" type="button"
            :class="tab==='<?= $key ?>'?'bg-navy text-white':'text-slate-500 hover:bg-paper hover:text-navy'"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-[13px] font-semibold transition shrink-0">
      <i data-lucide="<?= $icon ?>" class="w-4 h-4"></i><?= $lbl ?>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- TAB: OVERVIEW -->
  <div x-show="tab==='overview'" class="space-y-5">
    <div class="grid lg:grid-cols-[1fr_320px] gap-5">
      <!-- Daily income/expense line chart -->
      <div class="card p-5 sm:p-6 min-w-0">
        <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
          <div>
            <h2 class="font-display font-bold text-navy dark:text-white">Ingresos vs Gastos diarios</h2>
            <p class="text-[12px] text-slate-400 mt-0.5">Período seleccionado</p>
          </div>
          <div class="flex items-center gap-3 text-xs text-slate-500">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-brand"></span>Ingresos</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>Gastos</span>
          </div>
        </div>
        <div class="relative h-[280px] sm:h-[320px]">
          <canvas id="dailyChart"></canvas>
        </div>
      </div>
      <!-- Payment methods donut -->
      <div class="card p-5 sm:p-6 min-w-0">
        <h2 class="font-display font-bold text-navy dark:text-white mb-3">Métodos de pago</h2>
        <?php if (empty($paymentMethods)): ?>
          <?= View::renderPartial('_partials/empty_state', ['icon'=>'credit-card','title'=>'Sin pagos en el período','tone'=>'neutral']) ?>
        <?php else: ?>
          <div class="relative h-[180px]"><canvas id="methodsChart"></canvas></div>
          <div class="mt-4 space-y-2">
            <?php
            $totMethods = array_sum(array_map(fn($m)=>(float)$m['total'], $paymentMethods));
            foreach ($paymentMethods as $i => $m):
              $pct = $totMethods > 0 ? round(((float)$m['total'] / $totMethods) * 100, 1) : 0;
              $colors = ['#F23645','#1C2433','#6366F1','#10B981','#F59E0B','#06B6D4','#8B5CF6','#94A3B8'];
              $color = $colors[$i % count($colors)];
            ?>
            <div class="flex items-center justify-between text-[13px]">
              <span class="flex items-center gap-2 text-slate-500"><span class="w-2 h-2 rounded-full" style="background:<?= $color ?>"></span><?= e($paymentLabels[$m['method']] ?? $m['method']) ?></span>
              <span class="font-semibold text-navy tnum"><?= money((float)$m['total']) ?> <span class="text-slate-400 font-normal">(<?= $pct ?>%)</span></span>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Branch performance -->
    <?php if (!empty($branchPerformance)): ?>
    <div class="card overflow-hidden">
      <div class="px-5 sm:px-6 py-4 border-b hairline flex items-center justify-between">
        <h2 class="font-display font-bold text-navy dark:text-white">Rendimiento por sucursal</h2>
        <span class="text-[11px] text-slate-400">Periodo seleccionado</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
          <thead class="text-left text-slate-400 bg-paper text-[11px] uppercase tracking-wider">
            <tr><th class="px-5 sm:px-6 py-3">Sucursal</th><th class="px-5 sm:px-6 py-3">Flotilla</th><th class="px-5 sm:px-6 py-3">Ingresos</th><th class="px-5 sm:px-6 py-3">Gastos</th><th class="px-5 sm:px-6 py-3 text-right">Neto</th></tr>
          </thead>
          <tbody class="divide-y hairline">
            <?php foreach ($branchPerformance as $b): $bnet = (float)$b['revenue'] - (float)$b['expenses']; ?>
            <tr class="hover:bg-paper">
              <td class="px-5 sm:px-6 py-3"><span class="font-medium text-navy flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand/70"></i><?= e($b['name']) ?></span></td>
              <td class="px-5 sm:px-6 py-3 text-slate-500 tnum"><?= (int)$b['vehicles'] ?> uds</td>
              <td class="px-5 sm:px-6 py-3 text-emerald-600 font-medium tnum"><?= money($b['revenue']) ?></td>
              <td class="px-5 sm:px-6 py-3 text-slate-500 tnum"><?= money($b['expenses']) ?></td>
              <td class="px-5 sm:px-6 py-3 text-right font-bold tnum <?= $bnet>=0?'text-navy':'text-brand' ?>"><?= money($bnet) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- TAB: INGRESOS -->
  <div x-show="tab==='income'" x-cloak class="space-y-5">
    <div class="card p-5 sm:p-6">
      <h2 class="font-display font-bold text-navy mb-3">Ingresos por mes (12 meses)</h2>
      <div class="relative h-[260px] sm:h-[320px]"><canvas id="monthlyIncomeChart"></canvas></div>
    </div>
    <div class="grid lg:grid-cols-2 gap-5">
      <div class="card p-5 sm:p-6">
        <h2 class="font-display font-bold text-navy mb-3">Métodos de pago</h2>
        <?php if (empty($paymentMethods)): ?>
          <p class="text-sm text-slate-400 py-4 text-center">Sin datos en el período</p>
        <?php else: ?>
          <div class="space-y-3">
            <?php
            $maxM = max(array_map(fn($m)=>(float)$m['total'], $paymentMethods));
            foreach ($paymentMethods as $m):
              $pct = $maxM > 0 ? round(((float)$m['total'] / $maxM) * 100) : 0;
            ?>
            <div>
              <div class="flex items-center justify-between text-sm mb-1.5">
                <span class="font-medium text-navy"><?= e($paymentLabels[$m['method']] ?? $m['method']) ?> <span class="text-slate-400 font-normal">· <?= (int)$m['cnt'] ?> pagos</span></span>
                <span class="text-navy font-bold tnum"><?= money((float)$m['total']) ?></span>
              </div>
              <div class="progress"><i style="width:<?= $pct ?>%"></i></div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <!-- Top customers -->
      <div class="card p-5 sm:p-6">
        <h2 class="font-display font-bold text-navy mb-3">Mejores clientes</h2>
        <?php if (empty($topCustomers)): ?>
          <p class="text-sm text-slate-400 py-4 text-center">Sin clientes en el período</p>
        <?php else: ?>
          <div class="space-y-3">
            <?php $maxC = (float) ($topCustomers[0]['revenue'] ?? 0);
              foreach ($topCustomers as $i => $c):
                $pct = $maxC > 0 ? round(((float)$c['revenue'] / $maxC) * 100) : 0;
            ?>
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-navy/5 text-navy grid place-items-center text-xs font-bold shrink-0">
                <?= e(initials($c['first_name'].' '.($c['last_name'] ?? ''))) ?>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <a href="<?= url('/admin/customers/show/'.$c['id']) ?>" class="text-[13.5px] font-semibold text-navy hover:text-brand truncate"><?= e(trim($c['first_name'].' '.($c['last_name'] ?? ''))) ?></a>
                  <span class="text-[13px] font-bold text-navy tnum shrink-0" title="<?= e(money((float)$c['revenue'])) ?>"><?= money_compact((float)$c['revenue']) ?></span>
                </div>
                <div class="flex items-center gap-2 mt-1">
                  <div class="flex-1 h-1 rounded-full bg-slate-100 overflow-hidden"><div class="h-full grad-bg" style="width: <?= max(2,$pct) ?>%"></div></div>
                  <span class="text-[10.5px] text-slate-400 tnum"><?= (int)$c['contracts'] ?> contr.</span>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- TAB: GASTOS -->
  <div x-show="tab==='expenses'" x-cloak class="space-y-5">
    <?php if (empty($expensesByCategory)): ?>
      <?= View::renderPartial('_partials/empty_state', ['icon'=>'trending-down','title'=>'Sin gastos en el período','message'=>'No se han registrado gastos para este rango de fechas.','tone'=>'neutral']) ?>
    <?php else:
      $catTotal = array_sum(array_map(fn($r)=>(float)$r['total'],$expensesByCategory)); ?>
    <div class="grid lg:grid-cols-2 gap-5">
      <div class="card p-5 sm:p-6">
        <h2 class="font-display font-bold text-navy mb-3">Gastos por categoría</h2>
        <div class="relative h-[220px]"><canvas id="expCatChart"></canvas></div>
      </div>
      <div class="card p-5 sm:p-6">
        <h2 class="font-display font-bold text-navy mb-3">Desglose detallado <span class="text-sm font-normal text-slate-400">— <?= money($catTotal) ?> total</span></h2>
        <div class="space-y-3">
          <?php foreach ($expensesByCategory as $row): $pct = $catTotal>0 ? round((float)$row['total']/$catTotal*100) : 0; ?>
          <div>
            <div class="flex items-center justify-between text-sm mb-1.5">
              <span class="font-medium text-navy"><?= e($catLabels[$row['category']] ?? $row['category']) ?> <span class="text-slate-400 font-normal">· <?= (int)$row['cnt'] ?></span></span>
              <span class="text-slate-500 tnum"><?= money($row['total']) ?> <span class="text-slate-400">(<?= $pct ?>%)</span></span>
            </div>
            <div class="progress"><i style="width:<?= $pct ?>%"></i></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- TAB: FLOTILLA + CLIENTES -->
  <div x-show="tab==='fleet'" x-cloak class="space-y-5">
    <div class="card overflow-hidden">
      <div class="px-5 sm:px-6 py-4 border-b hairline">
        <h2 class="font-display font-bold text-navy">Vehículos más rentados</h2>
        <p class="text-[12px] text-slate-400 mt-0.5">Por ingresos generados en el período</p>
      </div>
      <?php if (empty(array_filter($topVehicles, fn($v)=>(float)$v['revenue'] > 0))): ?>
        <div class="p-6">
          <?= View::renderPartial('_partials/empty_state', ['icon'=>'car-front','title'=>'Sin contratos en el período','tone'=>'neutral']) ?>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-[13px]">
            <thead class="text-left text-slate-400 bg-paper text-[11px] uppercase tracking-wider">
              <tr>
                <th class="px-5 sm:px-6 py-3">#</th>
                <th class="px-5 sm:px-6 py-3">Vehículo</th>
                <th class="px-5 sm:px-6 py-3">Placa</th>
                <th class="px-5 sm:px-6 py-3">Rentas</th>
                <th class="px-5 sm:px-6 py-3 text-right">Ingresos</th>
              </tr>
            </thead>
            <tbody class="divide-y hairline">
              <?php foreach ($topVehicles as $i => $v): ?>
              <tr class="hover:bg-paper">
                <td class="px-5 sm:px-6 py-3 text-slate-400 font-mono tnum"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></td>
                <td class="px-5 sm:px-6 py-3">
                  <div class="flex items-center gap-3">
                    <?php if (!empty($v['main_image'])): ?>
                      <img src="<?= e(media($v['main_image'])) ?>" class="w-9 h-9 rounded-lg object-cover bg-slate-100" alt="">
                    <?php else: ?>
                      <div class="w-9 h-9 rounded-lg bg-slate-100 grid place-items-center"><i data-lucide="car-front" class="w-4 h-4 text-slate-400"></i></div>
                    <?php endif; ?>
                    <span class="font-medium text-navy"><?= e($v['brand'].' '.$v['model']) ?></span>
                  </div>
                </td>
                <td class="px-5 sm:px-6 py-3 text-slate-500 tnum"><?= e($v['plate_number'] ?? '—') ?></td>
                <td class="px-5 sm:px-6 py-3"><span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 rounded-full bg-navy/5 text-navy text-xs font-semibold tnum"><?= (int)$v['rentals'] ?></span></td>
                <td class="px-5 sm:px-6 py-3 text-right font-bold text-navy tnum"><?= money($v['revenue']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
// Chart data for JS
$dailyLabels = $dailySeries['labels'];
$dailyInc = $dailySeries['income'];
$dailyExp = $dailySeries['expense'];
$pmLabels = array_map(fn($m) => $paymentLabels[$m['method']] ?? $m['method'], $paymentMethods);
$pmValues = array_map(fn($m) => (float)$m['total'], $paymentMethods);
$monthIncLabels = array_map(fn($r) => $r['ym'], $monthlyIncome);
$monthIncValues = array_map(fn($r) => (float)$r['total'], $monthlyIncome);
$monthExpValues = array_map(fn($r) => (float)($monthlyExpenses[$r['ym']] ?? 0), $monthlyIncome);
$expCatLabels = array_map(fn($r) => $catLabels[$r['category']] ?? $r['category'], $expensesByCategory);
$expCatValues = array_map(fn($r) => (float)$r['total'], $expensesByCategory);

View::push('scripts', '<script>
(function(){
  // Never set maintainAspectRatio=false globally — locks via wrapper + lockSize().
  Chart.defaults.font.family="Inter"; Chart.defaults.color="#9aa3b2";
  var brand="#F23645", navy="#1C2433";
  var lockSize = function(opts){ opts = opts || {}; opts.responsive = true; opts.maintainAspectRatio = false; return opts; };

  // Daily revenue + expense
  var d = document.getElementById("dailyChart");
  if (d) {
    var ctx = d.getContext("2d");
    var g = ctx.createLinearGradient(0,0,0,300); g.addColorStop(0,"rgba(242,54,69,.22)"); g.addColorStop(1,"rgba(242,54,69,0)");
    new Chart(d, {
      type:"line",
      data:{labels:'.json_encode($dailyLabels).',datasets:[
        {label:"Ingresos",data:'.json_encode($dailyInc).',borderColor:brand,backgroundColor:g,fill:true,tension:.4,pointRadius:0,borderWidth:2.5},
        {label:"Gastos",data:'.json_encode($dailyExp).',borderColor:"#94A3B8",backgroundColor:"transparent",fill:false,tension:.4,pointRadius:0,borderWidth:1.5,borderDash:[5,4]}
      ]},
      options:lockSize({plugins:{legend:{display:false},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10,mode:"index",intersect:false}},scales:{y:{beginAtZero:true,grid:{color:"#EEF1F6"},border:{display:false}},x:{grid:{display:false},border:{display:false}}}})
    });
  }

  // Payment methods donut
  var m = document.getElementById("methodsChart");
  if (m) new Chart(m, {
    type:"doughnut",
    data:{labels:'.json_encode($pmLabels).',datasets:[{data:'.json_encode($pmValues).',backgroundColor:[brand,navy,"#6366F1","#10B981","#F59E0B","#06B6D4","#8B5CF6","#94A3B8"],borderWidth:0,hoverOffset:6}]},
    options:lockSize({cutout:"68%",plugins:{legend:{display:false},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10}}})
  });

  // Monthly income bars
  var mi = document.getElementById("monthlyIncomeChart");
  if (mi) new Chart(mi, {
    type:"bar",
    data:{labels:'.json_encode($monthIncLabels).',datasets:[
      {label:"Ingresos",data:'.json_encode($monthIncValues).',backgroundColor:brand,borderRadius:6,maxBarThickness:28},
      {label:"Gastos",data:'.json_encode($monthExpValues).',backgroundColor:"#CBD5E1",borderRadius:6,maxBarThickness:28}
    ]},
    options:lockSize({plugins:{legend:{position:"bottom",labels:{boxWidth:9,font:{size:11},padding:10,usePointStyle:true}},tooltip:{backgroundColor:navy,padding:10,cornerRadius:10,mode:"index",intersect:false}},scales:{y:{beginAtZero:true,grid:{color:"#EEF1F6"},border:{display:false}},x:{grid:{display:false},border:{display:false}}}})
  });

  // Expense categories donut
  var ec = document.getElementById("expCatChart");
  if (ec) new Chart(ec, {
    type:"doughnut",
    data:{labels:'.json_encode($expCatLabels).',datasets:[{data:'.json_encode($expCatValues).',backgroundColor:[brand,"#1C2433","#6366F1","#10B981","#F59E0B","#06B6D4","#8B5CF6","#94A3B8","#EF4444","#EAB308","#22C55E","#F97316"],borderWidth:0,hoverOffset:6}]},
    options:lockSize({cutout:"60%",plugins:{legend:{position:"right",labels:{boxWidth:8,font:{size:10},padding:8,usePointStyle:true}}}})
  });
})();
</script>'); ?>
