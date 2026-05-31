<h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-1">Planes SaaS</h1>
<p class="text-sm text-slate-500 mb-6">Configura los planes de suscripcion</p>

<div class="grid md:grid-cols-3 gap-6">
  <?php foreach ($plans as $p): ?>
  <div class="bg-white dark:bg-slate-900 rounded-2xl border hairline shadow-card p-6">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= e($p['name']) ?></h2>
      <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($p['status']) ?>"><?= status_label($p['status']) ?></span>
    </div>
    <p class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white"><?= money($p['price_monthly']) ?><span class="text-sm font-normal text-slate-400">/mes</span></p>
    <p class="text-xs text-slate-400 mt-1"><?= $p['tenants_count'] ?> empresas en este plan</p>
    <ul class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
      <li class="flex items-center gap-2"><i data-lucide="car" class="w-4 h-4 text-indigo-500"></i> <?= ((int)$p['max_vehicles'] < 0) ? 'Vehiculos ilimitados' : $p['max_vehicles'] . ' vehiculos' ?></li>
      <li class="flex items-center gap-2"><i data-lucide="users" class="w-4 h-4 text-indigo-500"></i> <?= ((int)$p['max_users'] < 0) ? 'Usuarios ilimitados' : $p['max_users'] . ' usuarios' ?></li>
      <li class="flex items-center gap-2"><i data-lucide="hard-drive" class="w-4 h-4 text-indigo-500"></i> <?= (int)$p['storage_mb'] >= 1024 ? number_format((int)$p['storage_mb']/1024, 1) . ' GB' : (int)$p['storage_mb'] . ' MB' ?> de almacenamiento</li>
      <?php foreach ($p['features_list'] as $f): ?>
        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> <?= e($f) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endforeach; ?>
</div>
