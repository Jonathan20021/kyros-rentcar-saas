<section class="mesh-dark relative overflow-hidden pt-36 pb-24 min-h-screen">
  <div class="grid-dark absolute inset-0"></div>
  <div class="orb w-[30rem] h-[30rem] -top-32 left-1/4 grad-bg"></div>
  <div class="relative max-w-5xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <h1 class="font-display text-4xl sm:text-6xl font-extrabold tracking-tightest">Planes y precios</h1>
      <p class="mt-4 text-white/55 text-lg">Elige el plan ideal para tu rent car. Cambia cuando quieras.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      <?php foreach ($plans as $i => $p): $featured=$i===1; $feats=$p['features']?(json_decode($p['features'],true)?:[]):[]; ?>
      <div class="relative rounded-3xl p-7 reveal <?= $featured ? 'bg-white text-ink lg:-translate-y-4 shadow-lift' : 'glass' ?>">
        <?php if ($featured): ?><span class="absolute top-6 right-6 px-2.5 py-1 rounded-full grad-bg text-white text-[11px] font-bold">POPULAR</span><?php endif; ?>
        <h3 class="font-display text-lg font-bold"><?= e($p['name']) ?></h3>
        <p class="mt-3"><span class="font-display text-4xl font-extrabold"><?= money($p['price_monthly']) ?></span><span class="<?= $featured?'text-slate-400':'text-white/40' ?>">/mes</span></p>
        <ul class="mt-6 space-y-3 text-sm <?= $featured?'text-slate-600':'text-white/60' ?>">
          <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 <?= $featured?'text-brand':'text-brand2' ?>"></i> <?= ((int)$p['max_vehicles']<0)?'Vehiculos ilimitados':$p['max_vehicles'].' vehiculos' ?></li>
          <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 <?= $featured?'text-brand':'text-brand2' ?>"></i> <?= ((int)$p['max_users']<0)?'Usuarios ilimitados':$p['max_users'].' usuarios' ?></li>
          <?php foreach ($feats as $f): ?><li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 <?= $featured?'text-brand':'text-brand2' ?>"></i> <?= e($f) ?></li><?php endforeach; ?>
        </ul>
        <a href="<?= url('/register?plan=' . urlencode($p['slug'])) ?>" class="k-btn w-full mt-7 <?= $featured ? 'k-btn-grad' : 'k-btn-glass' ?>">Empezar con <?= e($p['name']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
