<?php
use App\Models\Plan;
$tenant      = is_array($tenant ?? null) ? $tenant : [];
$currentSlug = $tenant['plan_slug'] ?? null;
$currentPlanId = (int) ($tenant['plan_id'] ?? 0);
$currentPlan = null;
foreach ($plans as $p) {
  if (!empty($currentSlug) && !empty($p['slug']) && $p['slug'] === $currentSlug) { $currentPlan = $p; break; }
}
// Fallback: match by id.
if (!$currentPlan && $currentPlanId > 0) {
  foreach ($plans as $p) {
    if ((int) $p['id'] === $currentPlanId) { $currentPlan = $p; $currentSlug = $p['slug'] ?? null; break; }
  }
}
$vPct = $vehiclesMax > 0 ? min(100, (int) round($vehiclesCount / $vehiclesMax * 100)) : 0;
$uPct = $usersMax > 0 ? min(100, (int) round($usersCount / $usersMax * 100)) : 0;
$tier = ['starter'=>1,'business'=>2,'premium'=>3];
?>
<?php if ($feature && $required): ?>
<div class="card p-5 mb-6 border-2 border-amber-200 bg-gradient-to-r from-amber-50 to-white dark:from-amber-500/10 dark:to-slate-900 flex items-center gap-4">
  <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300 grid place-items-center">
    <i data-lucide="lock" class="w-6 h-6"></i>
  </div>
  <div class="flex-1 min-w-0">
    <p class="font-display font-bold text-navy dark:text-white">Esta función está en el plan <?= e($required) ?></p>
    <p class="text-sm text-slate-500 mt-0.5">Estás en el plan <b><?= e($tenant['plan_name'] ?? '—') ?></b>. Actualiza para desbloquearla.</p>
  </div>
</div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Tu plan</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Tu uso actual y qué desbloqueas al cambiar de plan.</p>
  </div>
  <?php if ($currentPlan): ?>
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-ink text-white text-xs font-semibold">
      <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Plan actual: <?= e($currentPlan['name']) ?>
    </span>
  <?php endif; ?>
</div>

<!-- Capacity meter -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
  <div class="card p-5 reveal">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand grid place-items-center"><i data-lucide="car" class="w-5 h-5"></i></div>
        <p class="font-display font-bold text-navy dark:text-white">Vehículos</p>
      </div>
      <p class="text-sm font-semibold text-navy dark:text-white tnum">
        <?= $vehiclesCount ?> / <?= $vehiclesMax === -1 ? '∞' : $vehiclesMax ?>
      </p>
    </div>
    <div class="progress mt-3"><i style="width:<?= $vehiclesMax === -1 ? 20 : $vPct ?>%; background: var(--brand);"></i></div>
    <?php if ($vehiclesMax !== -1 && $vPct >= 80): ?>
      <p class="text-xs text-amber-600 mt-2 flex items-center gap-1"><i data-lucide="alert-triangle" class="w-3 h-3"></i>Cerca del límite — considera actualizar.</p>
    <?php elseif ($vehiclesMax === -1): ?>
      <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1"><i data-lucide="infinity" class="w-3 h-3"></i>Vehículos ilimitados con tu plan.</p>
    <?php endif; ?>
  </div>
  <div class="card p-5 reveal">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 grid place-items-center"><i data-lucide="users" class="w-5 h-5"></i></div>
        <p class="font-display font-bold text-navy dark:text-white">Equipo</p>
      </div>
      <p class="text-sm font-semibold text-navy dark:text-white tnum">
        <?= $usersCount ?> / <?= $usersMax === -1 ? '∞' : $usersMax ?>
      </p>
    </div>
    <div class="progress mt-3"><i style="width:<?= $usersMax === -1 ? 20 : $uPct ?>%; background: #6366F1;"></i></div>
    <?php if ($usersMax !== -1 && $uPct >= 80): ?>
      <p class="text-xs text-amber-600 mt-2 flex items-center gap-1"><i data-lucide="alert-triangle" class="w-3 h-3"></i>Cerca del límite — considera actualizar.</p>
    <?php elseif ($usersMax === -1): ?>
      <p class="text-xs text-emerald-600 mt-2 flex items-center gap-1"><i data-lucide="infinity" class="w-3 h-3"></i>Usuarios ilimitados con tu plan.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Pricing grid -->
<div class="grid lg:grid-cols-3 gap-5">
  <?php
  $tierIcons = ['starter'=>'gem','business'=>'rocket','premium'=>'crown'];
  foreach ($plans as $p):
    $isCurrent = $currentPlan && (int)$p['id'] === (int)$currentPlan['id'];
    $features = $p['features'] ? (json_decode($p['features'], true) ?: []) : [];
    $rank = $tier[$p['slug']] ?? 0;
    $currentRank = $currentPlan ? ($tier[$currentPlan['slug']] ?? 0) : 0;
    $isUpgrade = $rank > $currentRank;
    $isDowngrade = $rank > 0 && $rank < $currentRank;
    $isBusiness = $p['slug'] === 'business';
  ?>
  <div class="card relative reveal-s overflow-hidden <?= $isCurrent ? 'border-brand ring-2 ring-brand/30' : ($isBusiness ? 'border-brand/40' : '') ?>">
    <?php if ($isBusiness && !$isCurrent): ?>
      <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-brand text-white text-[10px] font-bold uppercase tracking-wide">Más popular</span>
    <?php endif; ?>
    <?php if ($isCurrent): ?>
      <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-bold uppercase tracking-wide">Tu plan</span>
    <?php endif; ?>

    <div class="p-6 pb-4">
      <div class="w-11 h-11 rounded-2xl bg-brand/10 text-brand grid place-items-center mb-4">
        <i data-lucide="<?= $tierIcons[$p['slug']] ?? 'package' ?>" class="w-5 h-5"></i>
      </div>
      <h3 class="font-display font-extrabold text-navy dark:text-white text-xl"><?= e($p['name']) ?></h3>
      <div class="mt-3 flex items-baseline gap-1">
        <span class="text-3xl font-extrabold text-navy dark:text-white tnum"><?= money($p['price_monthly']) ?></span>
        <span class="text-sm text-slate-400">/mes</span>
      </div>
      <p class="text-xs text-slate-400 mt-1">o <?= money($p['price_yearly']) ?>/año (2 meses gratis)</p>
    </div>

    <div class="px-6 pb-2 text-sm text-slate-600 dark:text-slate-300 space-y-2">
      <p class="flex items-center gap-2"><i data-lucide="car" class="w-4 h-4 text-brand"></i>
        <?= $p['max_vehicles'] === -1 ? 'Vehículos ilimitados' : ($p['max_vehicles'] . ' vehículos') ?></p>
      <p class="flex items-center gap-2"><i data-lucide="users" class="w-4 h-4 text-brand"></i>
        <?= $p['max_users'] === -1 ? 'Usuarios ilimitados' : ($p['max_users'] . ' usuarios') ?></p>
    </div>

    <div class="px-6 pt-3 pb-6 border-t hairline mt-4 space-y-2">
      <?php foreach ($features as $f): ?>
        <p class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
          <i data-lucide="check" class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5"></i><?= e($f) ?>
        </p>
      <?php endforeach; ?>
    </div>

    <div class="px-6 pb-6">
      <?php if ($isCurrent): ?>
        <button disabled class="w-full k-btn k-btn-outline opacity-60 cursor-default">Plan actual</button>
      <?php elseif ($isUpgrade): ?>
        <a href="mailto:soporte@kyrosrd.com?subject=<?= rawurlencode('Quiero actualizar a ' . $p['name']) ?>" class="w-full k-btn k-btn-grad">Actualizar <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
      <?php else: ?>
        <a href="mailto:soporte@kyrosrd.com?subject=<?= rawurlencode('Cambio a ' . $p['name']) ?>" class="w-full k-btn k-btn-outline">Cambiar a este plan</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<p class="text-center text-sm text-slate-400 mt-6">¿Necesitas un plan a medida? Escríbenos a <a href="mailto:soporte@kyrosrd.com" class="text-brand font-medium hover:underline">soporte@kyrosrd.com</a>.</p>
