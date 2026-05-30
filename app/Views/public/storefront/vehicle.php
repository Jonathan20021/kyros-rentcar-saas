<?php
use App\Core\View;
echo View::renderPartial('public/storefront/_nav', ['tenant' => $tenant]);
$gallery = !empty($images) ? array_column($images, 'path') : [];
if (empty($gallery) && !empty($vehicle['main_image'])) $gallery = [$vehicle['main_image']];
$primary = $tenant['primary_color'];
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
  <nav class="text-sm text-slate-400 mb-6 flex items-center gap-1.5">
    <a href="<?= url('/r/'.$tenant['slug']) ?>" class="hover:text-ink">Vehiculos</a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
    <span class="text-ink font-medium"><?= e($vehicle['brand'].' '.$vehicle['model']) ?></span>
  </nav>

  <div class="grid lg:grid-cols-2 gap-10" x-data="{ main: <?= json_encode($gallery[0] ?? '') ?> }">
    <!-- Gallery -->
    <div data-aos="fade-right">
      <div class="aspect-[16/11] bg-paper rounded-3xl border hairline grid place-items-center overflow-hidden">
        <?php if (!empty($gallery)): ?>
          <img :src="main" class="w-full h-full object-contain p-6" alt="<?= e($vehicle['brand']) ?>">
        <?php else: ?>
          <div class="text-slate-200"><i data-lucide="car" class="w-24 h-24"></i></div>
        <?php endif; ?>
      </div>
      <?php if (count($gallery) > 1): ?>
      <div class="flex gap-2.5 mt-3">
        <?php foreach ($gallery as $g): ?>
          <button type="button" @click="main=<?= json_encode($g) ?>" class="w-24 h-16 rounded-xl overflow-hidden border-2 bg-paper" :class="main===<?= json_encode($g) ?>?'border-ink':'border-transparent'">
            <img src="<?= e(media($g)) ?>" class="w-full h-full object-contain p-1">
          </button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div data-aos="fade-left">
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-[12px] font-semibold text-slate-600"><i data-lucide="car-front" class="w-3.5 h-3.5"></i><?= e($vehicle['category_name'] ?? 'Vehiculo') ?></span>
        <?php if ($vehicle['is_featured']): ?><span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-600 text-[12px] font-semibold">★ Destacado</span><?php endif; ?>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[12px] font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Disponible</span>
      </div>
      <h1 class="font-display text-3xl lg:text-4xl font-extrabold text-ink mt-4 leading-tight"><?= e($vehicle['brand'].' '.$vehicle['model']) ?></h1>
      <p class="text-slate-400 mt-1"><?= e($vehicle['version'] ?? '') ?> · <?= e($vehicle['year']) ?> · <?= e($vehicle['color'] ?? '') ?></p>

      <div class="flex items-baseline gap-2 mt-5">
        <span class="text-4xl font-extrabold text-ink"><?= money($vehicle['daily_price']) ?></span><span class="text-slate-400">/ dia</span>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-6">
        <?php foreach ([['users',$vehicle['passengers'].' pasajeros'],['cog',$vehicle['transmission']==='automatic'?'Automatica':'Manual'],['fuel',ucfirst($vehicle['fuel_type'])],['door-open',$vehicle['doors'].' puertas'],['briefcase',$vehicle['luggage_capacity'].' maletas'],['gauge',number_format((int)$vehicle['mileage']).' km']] as $s): ?>
        <div class="bg-white rounded-2xl border hairline p-3.5 text-center">
          <i data-lucide="<?= $s[0] ?>" class="w-5 h-5 mx-auto text-slate-400"></i>
          <p class="text-[13px] text-slate-600 mt-1.5 font-medium"><?= e($s[1]) ?></p>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if (!empty($features)): ?>
      <div class="mt-6">
        <h3 class="font-semibold text-ink mb-2.5 text-sm">Caracteristicas</h3>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($features as $f): ?>
            <span class="px-3 py-1.5 rounded-xl bg-paper border hairline text-sm text-slate-600 flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500"></i><?= e($f) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($vehicle['description'])): ?>
        <p class="mt-6 text-slate-600 leading-relaxed"><?= e($vehicle['description']) ?></p>
      <?php endif; ?>

      <div class="grid grid-cols-2 gap-3 mt-6 p-4 bg-paper rounded-2xl border hairline text-sm">
        <div><p class="text-slate-400">Deposito (reembolsable)</p><p class="font-semibold text-ink mt-0.5"><?= money($vehicle['deposit_amount']) ?></p></div>
        <div><p class="text-slate-400">Seguro / dia</p><p class="font-semibold text-ink mt-0.5"><?= money($vehicle['insurance_price']) ?></p></div>
      </div>

      <a href="<?= url('/r/'.$tenant['slug'].'/reservar/'.$vehicle['slug']) ?>" class="flex items-center justify-center gap-2 mt-6 px-6 py-4 rounded-2xl text-white font-semibold text-lg shadow-card hover:opacity-90 transition" style="background:<?= e($primary) ?>">
        <i data-lucide="calendar-check" class="w-5 h-5"></i> Reservar ahora
      </a>
    </div>
  </div>
</section>

<?php echo View::renderPartial('public/storefront/_footer', ['tenant' => $tenant]); ?>
