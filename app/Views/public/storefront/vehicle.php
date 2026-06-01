<?php
use App\Core\View;

echo View::renderPartial('public/storefront/_nav', ['tenant' => $tenant]);

$gallery = !empty($images) ? array_column($images, 'path') : [];
if (empty($gallery) && !empty($vehicle['main_image'])) $gallery = [$vehicle['main_image']];
$galleryUrls = array_map('media', $gallery);
$base = url('/r/'.$tenant['slug']);
$reserveUrl = url('/r/'.$tenant['slug'].'/reservar/'.$vehicle['slug']);
$fuelLabel = static fn($fuel) => ['gasoline'=>'Gasolina','diesel'=>'Diésel','electric'=>'Eléctrico','hybrid'=>'Híbrido','gas'=>'Gas'][$fuel] ?? ucfirst((string)$fuel);
$transLabel = static fn($t) => $t === 'automatic' ? 'Automática' : 'Manual';
?>

<!-- HERO -->
<section class="relative overflow-hidden border-b border-[#1c1c1c]" style="background:#0a0a0a">
  <div class="lux-orb" style="width:480px;height:480px;right:-160px;top:-180px;opacity:.16"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 pt-28 pb-14 lg:pb-20">
    <nav class="mb-8 flex items-center gap-2 text-sm text-[var(--lux-dim)]">
      <a href="<?= e($base) ?>" class="hover:text-white transition-colors">Catálogo</a>
      <i data-lucide="chevron-right" class="h-4 w-4"></i>
      <span class="font-semibold text-white"><?= e($vehicle['brand'].' '.$vehicle['model']) ?></span>
    </nav>

    <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-8 lg:gap-12 items-end">
      <div data-aos="fade-up">
        <span class="lux-chip lux-chip-brand"><?= e($vehicle['category_name'] ?? 'Vehículo') ?> · <?= e($vehicle['year']) ?></span>
        <h1 class="mt-5 max-w-3xl font-display text-[clamp(40px,6vw,84px)] font-extrabold leading-[.92] tracking-[-.05em] text-white">
          <?= e($vehicle['brand'].' '.$vehicle['model']) ?>
        </h1>
        <p class="mt-5 max-w-2xl text-lg leading-relaxed text-[var(--lux-muted)]">
          <?= e($vehicle['description'] ?: (trim(($vehicle['version'] ?? '').' '.$vehicle['year']).' · listo para reservar con '.$tenant['name'].'.')) ?>
        </p>
      </div>

      <aside class="lux-surface rounded-2xl p-6" data-aos="fade-up" data-aos-delay="80">
        <div class="flex items-start justify-between gap-5">
          <div>
            <p class="text-sm text-[var(--lux-dim)]">Desde</p>
            <p class="mt-1 font-display text-4xl font-extrabold text-white tnum"><?= money($vehicle['daily_price']) ?><span class="text-sm font-medium text-[var(--lux-dim)]"> / día</span></p>
          </div>
          <span class="lux-chip" style="color:#86efac;border-color:rgba(34,197,94,.35)"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Disponible</span>
        </div>
        <a href="<?= e($reserveUrl) ?>" class="mt-6 lux-btn lux-btn-brand w-full text-base py-4">
          <i data-lucide="calendar-check" class="h-5 w-5"></i> Reservar ahora
        </a>
        <?php if (!empty($tenant['whatsapp'])): ?>
        <a href="<?= e(whatsapp_link($tenant['whatsapp'], 'Hola, me interesa el '.$vehicle['brand'].' '.$vehicle['model'].'.')) ?>" target="_blank" class="mt-2.5 lux-btn lux-btn-outline w-full"><i data-lucide="message-circle" class="h-4 w-4"></i> Consultar por WhatsApp</a>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</section>

<!-- GALLERY + SPECS -->
<section class="py-14 lg:py-20" style="background:#0d0d0d">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-8 lg:gap-12" x-data='{ main: <?= json_encode($galleryUrls[0] ?? '') ?> }'>
      <div data-aos="fade-up">
        <div class="overflow-hidden rounded-2xl border border-[#262626]" style="background:#141414">
          <?php if (!empty($gallery)): ?>
            <img :src="main" class="aspect-[16/11] w-full object-cover" alt="<?= e($vehicle['brand'].' '.$vehicle['model']) ?>">
          <?php else: ?>
            <div class="aspect-[16/11] grid place-items-center text-[#2a2a2a]"><i data-lucide="car" class="h-24 w-24"></i></div>
          <?php endif; ?>
        </div>
        <?php if (count($gallery) > 1): ?>
        <div class="mt-3 grid grid-cols-4 gap-2.5">
          <?php foreach ($gallery as $g): ?>
            <button type="button" @click='main=<?= json_encode(media($g)) ?>' class="overflow-hidden rounded-xl border-2 transition" :class='main===<?= json_encode(media($g)) ?>?"border-[color:var(--lux-brand-text)]":"border-[#262626]"'>
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
          <div class="lux-surface rounded-xl p-4">
            <i data-lucide="<?= $s[0] ?>" class="h-5 w-5" style="color:var(--lux-brand-text)"></i>
            <p class="mt-3 text-sm font-bold text-white"><?= e($s[1]) ?></p>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($features)): ?>
        <div class="lux-surface rounded-2xl p-5">
          <h2 class="font-display text-lg font-extrabold tracking-[-.03em] text-white">Características</h2>
          <div class="mt-4 flex flex-wrap gap-2">
            <?php foreach ($features as $f): ?>
              <span class="lux-chip"><i data-lucide="check" class="h-3.5 w-3.5" style="color:var(--lux-brand-text)"></i><?= e($f) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-3">
          <div class="lux-surface rounded-xl p-5">
            <p class="text-sm text-[var(--lux-dim)]">Depósito reembolsable</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-white tnum"><?= money($vehicle['deposit_amount']) ?></p>
          </div>
          <div class="lux-surface rounded-xl p-5">
            <p class="text-sm text-[var(--lux-dim)]">Seguro / día</p>
            <p class="mt-1 font-display text-2xl font-extrabold text-white tnum"><?= money($vehicle['insurance_price']) ?></p>
          </div>
        </div>

        <div class="rounded-2xl border-l-2 p-6" style="border-color:var(--lux-brand-text); background:#141414">
          <h2 class="font-display text-lg font-extrabold tracking-[-.03em] text-white">Reserva directa</h2>
          <p class="mt-2 text-sm leading-relaxed text-[var(--lux-muted)]">Selecciona tus fechas y envía la solicitud. El equipo confirma disponibilidad, depósito y punto de entrega.</p>
          <a href="<?= e($reserveUrl) ?>" class="mt-5 lux-btn lux-btn-brand">Continuar reserva <i data-lucide="arrow-right" class="h-4 w-4"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php echo View::renderPartial('public/storefront/_footer', ['tenant' => $tenant]); ?>
