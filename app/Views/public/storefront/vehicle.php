<?php
use App\Core\View;

echo View::renderPartial('public/storefront/_nav', ['tenant' => $tenant]);

$gallery = !empty($images) ? array_column($images, 'path') : [];
if (empty($gallery) && !empty($vehicle['main_image'])) $gallery = [$vehicle['main_image']];
$galleryUrls = array_map('media', $gallery);
$primary = $tenant['primary_color'];
$fuelLabel = static fn($fuel) => [
  'gasoline' => 'Gasolina',
  'diesel' => 'Diesel',
  'electric' => 'Electrico',
  'hybrid' => 'Hibrido',
][$fuel] ?? ucfirst((string)$fuel);
$transLabel = static fn($transmission) => $transmission === 'automatic' ? 'Automatica' : 'Manual';
?>

<section class="bg-[#101620] text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 lg:py-16">
    <nav class="mb-8 flex items-center gap-2 text-sm text-white/50">
      <a href="<?= url('/r/'.$tenant['slug']) ?>" class="hover:text-white">Catalogo</a>
      <i data-lucide="chevron-right" class="h-4 w-4"></i>
      <span class="font-semibold text-white"><?= e($vehicle['brand'].' '.$vehicle['model']) ?></span>
    </nav>

    <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-8 lg:gap-12 items-end">
      <div>
        <p class="text-[11px] font-black uppercase tracking-[.26em] text-white/50"><?= e($vehicle['category_name'] ?? 'Vehiculo') ?> · <?= e($vehicle['year']) ?></p>
        <h1 class="mt-4 max-w-4xl font-display text-5xl sm:text-6xl lg:text-[84px] font-black leading-[.92] tracking-[-.055em]">
          <?= e($vehicle['brand'].' '.$vehicle['model']) ?>
        </h1>
        <p class="mt-5 max-w-2xl text-lg leading-relaxed text-white/68">
          <?= e($vehicle['description'] ?: (($vehicle['version'] ?? '') . ' listo para reservar con ' . $tenant['name'] . '.')) ?>
        </p>
      </div>

      <aside class="border border-white/14 bg-white/[.07] p-5 backdrop-blur-xl">
        <div class="flex items-start justify-between gap-5">
          <div>
            <p class="text-sm text-white/45">Desde</p>
            <p class="mt-1 text-4xl font-black tnum"><?= money($vehicle['daily_price']) ?><span class="text-sm font-medium text-white/45"> / dia</span></p>
          </div>
          <span class="inline-flex items-center gap-1.5 bg-emerald-500/12 px-3 py-1.5 text-[12px] font-bold text-emerald-200">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>Disponible
          </span>
        </div>
        <a href="<?= url('/r/'.$tenant['slug'].'/reservar/'.$vehicle['slug']) ?>" class="mt-6 flex items-center justify-center gap-2 px-6 py-4 text-base font-bold text-white transition hover:opacity-90" style="background:<?= e($primary) ?>">
          <i data-lucide="calendar-check" class="h-5 w-5"></i> Reservar ahora
        </a>
      </aside>
    </div>
  </div>
</section>

<section class="bg-[#F4F6FA]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 lg:py-16">
    <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-8 lg:gap-12" x-data="{ main: <?= json_encode($galleryUrls[0] ?? '') ?> }">
      <div data-aos="fade-up">
        <div class="overflow-hidden border hairline bg-white">
          <?php if (!empty($gallery)): ?>
            <img :src="main" class="aspect-[16/11] w-full object-cover" alt="<?= e($vehicle['brand'].' '.$vehicle['model']) ?>">
          <?php else: ?>
            <div class="aspect-[16/11] grid place-items-center text-slate-300"><i data-lucide="car" class="h-24 w-24"></i></div>
          <?php endif; ?>
        </div>
        <?php if (count($gallery) > 1): ?>
        <div class="mt-3 grid grid-cols-4 gap-2.5">
          <?php foreach ($gallery as $g): ?>
            <button type="button" @click="main=<?= json_encode(media($g)) ?>" class="overflow-hidden border-2 bg-white transition" :class="main===<?= json_encode(media($g)) ?>?'border-[color:var(--brand)]':'border-transparent'">
              <img src="<?= e(media($g)) ?>" class="aspect-[4/3] w-full object-cover" alt="<?= e($vehicle['brand'].' '.$vehicle['model']) ?>">
            </button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="space-y-6" data-aos="fade-up" data-aos-delay="80">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <?php foreach ([
            ['users',$vehicle['passengers'].' pasajeros'],
            ['cog',$transLabel($vehicle['transmission'])],
            ['fuel',$fuelLabel($vehicle['fuel_type'])],
            ['door-open',$vehicle['doors'].' puertas'],
            ['briefcase',$vehicle['luggage_capacity'].' maletas'],
            ['gauge',number_format((int)$vehicle['mileage']).' km'],
          ] as $s): ?>
          <div class="border hairline bg-white p-4">
            <i data-lucide="<?= $s[0] ?>" class="h-5 w-5 text-brand"></i>
            <p class="mt-3 text-sm font-bold text-ink"><?= e($s[1]) ?></p>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($features)): ?>
        <div class="border hairline bg-white p-5">
          <h2 class="font-display text-xl font-black tracking-[-.035em] text-ink">Caracteristicas</h2>
          <div class="mt-4 flex flex-wrap gap-2">
            <?php foreach ($features as $f): ?>
              <span class="inline-flex items-center gap-1.5 border hairline bg-[#F4F6FA] px-3 py-1.5 text-sm font-medium text-slate-600"><i data-lucide="check" class="h-3.5 w-3.5 text-brand"></i><?= e($f) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-3">
          <div class="border hairline bg-white p-5">
            <p class="text-sm text-slate-400">Deposito reembolsable</p>
            <p class="mt-1 text-2xl font-black text-ink tnum"><?= money($vehicle['deposit_amount']) ?></p>
          </div>
          <div class="border hairline bg-white p-5">
            <p class="text-sm text-slate-400">Seguro / dia</p>
            <p class="mt-1 text-2xl font-black text-ink tnum"><?= money($vehicle['insurance_price']) ?></p>
          </div>
        </div>

        <div class="border-l-2 bg-white p-5" style="border-color:var(--brand)">
          <h2 class="font-display text-xl font-black tracking-[-.035em] text-ink">Reserva directa</h2>
          <p class="mt-2 text-sm leading-relaxed text-slate-500">Selecciona tus fechas y envia la solicitud. El equipo confirma disponibilidad, deposito y punto de entrega.</p>
          <a href="<?= url('/r/'.$tenant['slug'].'/reservar/'.$vehicle['slug']) ?>" class="mt-5 inline-flex items-center gap-2 px-5 py-3 text-sm font-bold text-white transition hover:opacity-90" style="background:var(--brand)">
            Continuar reserva <i data-lucide="arrow-right" class="h-4 w-4"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php echo View::renderPartial('public/storefront/_footer', ['tenant' => $tenant]); ?>
