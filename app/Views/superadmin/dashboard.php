<?php
$cards = [
  ['Empresas',$stats['tenants_total'],'building-2','bg-navy/5 text-navy'],
  ['Activas',$stats['tenants_active'],'circle-check-big','bg-emerald-50 text-emerald-600'],
  ['En prueba',$stats['tenants_trial'],'clock','bg-amber-50 text-amber-600'],
  ['Suspendidas',$stats['tenants_suspended'],'pause-circle','bg-red-50 text-brand'],
  ['Vehículos',$stats['vehicles_total'],'car','bg-indigo-50 text-indigo-600'],
  ['Reservas',$stats['reservations_total'],'calendar-check','bg-cyan-50 text-cyan-600'],
];
?>
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-[26px] font-extrabold text-navy dark:text-white tracking-tight">Panel Global Kyros</h1>
    <p class="text-[13px] text-slate-500 mt-1">Visión general de la plataforma · <?= strftime_es() ?></p>
  </div>
  <a href="<?= url('/super-admin/tenants/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nueva empresa</a>
</div>

<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
  <?php foreach ($cards as $c): ?>
  <div class="card p-4 reveal">
    <div class="w-9 h-9 rounded-xl grid place-items-center <?= $c[3] ?>"><i data-lucide="<?= $c[2] ?>" class="w-[18px] h-[18px]"></i></div>
    <p class="mt-3 text-[24px] leading-none font-extrabold text-navy dark:text-white tnum" data-count="<?= (int)$c[1] ?>">0</p>
    <p class="text-[13px] text-slate-400 mt-1.5"><?= $c[0] ?></p>
  </div>
  <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  <!-- MRR (solid navy) -->
  <div class="relative overflow-hidden rounded-2xl p-6 bg-navy text-white">
    <div class="absolute -top-16 -right-12 w-44 h-44 rounded-full opacity-25" style="background:var(--grad);filter:blur(48px)"></div>
    <div class="relative">
      <div class="flex items-center gap-2 text-white/55 text-[13px] font-medium"><i data-lucide="trending-up" class="w-4 h-4"></i> Ingresos estimados (MRR)</div>
      <p class="mt-2 text-[34px] leading-none font-extrabold tracking-tight tnum"><?= money($stats['mrr']) ?></p>
      <p class="text-[11px] text-white/40 mt-2">Basado en suscripciones activas y en prueba</p>
      <div class="mt-6"><canvas id="planChart" height="170"></canvas></div>
    </div>
  </div>

  <!-- Recent tenants -->
  <div class="lg:col-span-2 card overflow-hidden">
    <div class="px-6 py-4 border-b hairline flex items-center justify-between">
      <h2 class="font-display font-bold text-navy dark:text-white">Empresas recientes</h2>
      <a href="<?= url('/super-admin/tenants') ?>" class="text-sm text-brand font-semibold hover:underline">Ver todas</a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-left text-slate-400 bg-paper"><tr><th class="px-6 py-3 font-medium">Empresa</th><th class="px-6 py-3 font-medium">Plan</th><th class="px-6 py-3 font-medium">Estado</th><th class="px-6 py-3 font-medium">Creada</th></tr></thead>
        <tbody class="divide-y hairline">
          <?php foreach ($recentTenants as $t): ?>
          <tr class="hover:bg-paper">
            <td class="px-6 py-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-navy/5 text-navy grid place-items-center text-xs font-bold"><?= e(initials($t['name'])) ?></div>
                <div><div class="font-medium text-navy dark:text-white"><?= e($t['name']) ?></div><a href="<?= url('/r/'.$t['slug']) ?>" target="_blank" class="text-xs text-brand hover:underline">/r/<?= e($t['slug']) ?></a></div>
              </div>
            </td>
            <td class="px-6 py-3 text-slate-500"><?= e($t['plan_name'] ?? '-') ?></td>
            <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($t['status']) ?>"><?= status_label($t['status']) ?></span></td>
            <td class="px-6 py-3 text-slate-500"><?= format_date($t['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recentTenants)): ?><tr><td colspan="4" class="px-6 py-10 text-center text-slate-400">Sin empresas registradas</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
$labels = array_map(fn($p) => $p['name'], $perPlan);
$values = array_map(fn($p) => (int) $p['c'], $perPlan);
\App\Core\View::push('scripts', '<script>
(function(){
  const ctx=document.getElementById("planChart"); if(!ctx) return;
  Chart.defaults.color="rgba(255,255,255,.6)";
  new Chart(ctx,{type:"doughnut",data:{labels:'.json_encode($labels).',datasets:[{data:'.json_encode($values).',backgroundColor:["#F23645","#FF8FA0","#FFC2CB","#4B5468"],borderWidth:0,hoverOffset:6}]},options:{plugins:{legend:{position:"bottom",labels:{boxWidth:10,font:{size:11},padding:12,usePointStyle:true,color:"rgba(255,255,255,.7)"}}},cutout:"66%"}});
})();
</script>'); ?>
