<?php
use App\Core\View;

echo View::renderPartial('public/storefront/_nav', ['tenant' => $tenant]);

$primary = $tenant['primary_color'];
$curSym  = \App\Services\LocaleService::currencySymbol($tenant['currency'] ?? 'DOP');
$cover   = $tenant['cover_image'] ?? null;
$hMin = $histogram['min']; $hMax = $histogram['max']; $bars = $histogram['bars'];
$loVal = $filters['price_min'] ?: $hMin;
$hiVal = $filters['price_max'] ?: $hMax;
$maxBar = max(1, max($bars));

$fuelLabel = static fn($f) => ['gasoline'=>'Gasolina','diesel'=>'Diésel','electric'=>'Eléctrico','hybrid'=>'Híbrido','gas'=>'Gas'][$f] ?? ucfirst((string)$f);

// Marketing chrome uses the FULL public fleet (passed from the controller); the
// filtered $vehicles is reserved for the #catalogo results grid only. This keeps the
// hero, brands, spotlight and fleet preview stable no matter what filter is applied.
$allVehicles = $allVehicles ?? $vehicles;

// Featured pool for the spotlight carousel (prefer is_featured w/ image, then any w/ image).
$featuredPool = [];
foreach ($allVehicles as $v) { if (!empty($v['main_image']) && (int)($v['is_featured'] ?? 0) === 1) $featuredPool[] = $v; }
if (count($featuredPool) < 2) {
  foreach ($allVehicles as $v) { if (!empty($v['main_image'])) { $featuredPool[$v['id']] = $v; } }
  $featuredPool = array_values($featuredPool);
}
$featuredPool = array_slice($featuredPool, 0, 6);
$featured = $featuredPool[0] ?? (!empty($allVehicles) ? $allVehicles[0] : null);

$heroImage = $cover ?: ($featured['main_image'] ?? 'demo/vehicles/landing-hero-fleet.jpg');
$heroAlt = $featured ? trim($featured['brand'].' '.$featured['model']) : ($tenant['name'].' flota');

// Brand collection tiles.
$descriptors = ['Confort ejecutivo','Diseño y potencia','Lujo y presencia','Ingeniería refinada','Listo para la ruta','Estilo y eficiencia'];
$brandMap = [];
foreach ($allVehicles as $v) {
  $brand = trim((string)($v['brand'] ?? ''));
  if ($brand === '') continue;
  if (!isset($brandMap[$brand])) $brandMap[$brand] = ['name'=>$brand,'count'=>0];
  $brandMap[$brand]['count']++;
}
$brandTiles = array_slice(array_values($brandMap), 0, 6);
$fleetPreview = array_slice($allVehicles, 0, 8);

// Carousel payload
$spotlightJs = array_map(fn($v) => [
  'brand'=>$v['brand'],'model'=>$v['model'],'year'=>$v['year'],
  'img'=>media($v['main_image']),
  'passengers'=>$v['passengers'],'fuel'=>$fuelLabel($v['fuel_type']),
  'trans'=>$v['transmission']==='automatic'?'Automática':'Manual',
  'price'=>money($v['daily_price']),
  'url'=>url('/r/'.$tenant['slug'].'/vehiculo/'.$v['slug']),
  'category'=>$v['category_name'] ?? 'Premium',
], $featuredPool);
?>

<!-- ============================== HERO ============================== -->
<section id="inicio" class="relative min-h-[92dvh] overflow-hidden">
  <div class="absolute inset-0">
    <img src="<?= e(media($heroImage)) ?>" class="h-full w-full object-cover" alt="<?= e($heroAlt) ?>">
    <div class="absolute inset-0" style="background:linear-gradient(90deg,#0a0a0a 0%,rgba(10,10,10,.86) 40%,rgba(10,10,10,.35) 75%,rgba(10,10,10,.55) 100%)"></div>
    <div class="absolute inset-0" style="background:linear-gradient(0deg,#0a0a0a 2%,transparent 38%)"></div>
    <div class="lux-orb" style="width:560px;height:560px;left:-120px;top:-120px;opacity:.22"></div>
  </div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 flex min-h-[92dvh] flex-col justify-center pt-28 pb-16">
    <div class="max-w-3xl" data-aos="fade-up">
      <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-[10px] sm:text-[11px] font-bold uppercase tracking-[.22em] text-[var(--lux-muted)]">
        <span><?= e($tenant['address'] ? strtok($tenant['address'], ',') : 'República Dominicana') ?></span>
        <span class="h-1 w-1 rounded-full bg-[var(--brand)]"></span>
        <span>Flota premium</span>
        <span class="h-1 w-1 rounded-full bg-[var(--brand)]"></span>
        <span>Servicio 24/7</span>
      </div>
      <h1 class="mt-7 font-display text-[clamp(44px,7vw,104px)] font-extrabold leading-[.92] tracking-[-.05em] text-white">
        Renta el auto correcto.<br>
        <span style="color:var(--lux-brand-text)">Conduce la experiencia.</span>
      </h1>
      <p class="mt-7 max-w-xl text-lg sm:text-xl leading-relaxed text-[var(--lux-muted)]">
        <?= e(trim((string)($tenant['description'] ?? '')) ?: 'Flota moderna e inspeccionada, reservas rápidas y atención personalizada para cada viaje.') ?>
      </p>
      <div class="mt-9 flex flex-col sm:flex-row sm:flex-wrap gap-3">
        <a href="#flota" class="lux-btn lux-btn-brand text-[15px] px-7 py-4">Reservar un vehículo <i data-lucide="arrow-right" class="h-4 w-4"></i></a>
        <?php if (!empty($tenant['whatsapp'])): ?>
        <a href="<?= e(whatsapp_link($tenant['whatsapp'], 'Hola ' . $tenant['name'] . ', quiero información sobre alquiler de vehículos.')) ?>" target="_blank" class="lux-btn lux-btn-outline text-[15px] px-7 py-4">Hablar con un asesor <i data-lucide="message-circle" class="h-4 w-4"></i></a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Hero stats strip -->
    <div class="mt-14 grid max-w-2xl grid-cols-3 gap-px overflow-hidden rounded-2xl border border-[#262626]" style="background:#262626" data-aos="fade-up" data-aos-delay="120">
      <?php
        $stats = [
          [count($allVehicles), 'Vehículos disponibles'],
          [count($brandMap), 'Marcas en flota'],
          ['24/7', 'Asistencia directa'],
        ];
        foreach ($stats as $s): ?>
        <div class="px-5 py-5" style="background:#0d0d0d">
          <p class="font-display text-3xl font-extrabold text-white tnum"><?= e($s[0]) ?></p>
          <p class="mt-1 text-[12px] text-[var(--lux-dim)]"><?= e($s[1]) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <a href="#flota" class="absolute bottom-7 left-1/2 -translate-x-1/2 text-[var(--lux-dim)] hover:text-white transition-colors hidden sm:block" aria-label="Ver flota"><i data-lucide="chevron-down" class="h-6 w-6 animate-bounce"></i></a>
</section>

<!-- ============================== MARCAS ============================== -->
<?php if (!empty($brandTiles)): ?>
<section id="marcas" class="relative py-20 lg:py-28">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="grid lg:grid-cols-[.8fr_1fr] gap-8 items-end">
      <div data-aos="fade-up">
        <span class="lux-eyebrow">Marcas destacadas</span>
        <h2 class="mt-5 font-display text-[clamp(30px,4vw,56px)] font-extrabold tracking-[-.045em] leading-[1.02] text-white">Una colección curada de marcas.</h2>
      </div>
      <div class="lg:justify-self-end lg:text-right" data-aos="fade-up" data-aos-delay="80">
        <p class="max-w-xl text-[var(--lux-muted)] leading-relaxed">Explora la flota por marca: vehículos inspeccionados, listos para entregar y con reserva directa por WhatsApp o formulario.</p>
        <a href="#catalogo" class="mt-5 inline-flex lux-link items-center gap-2">Ver flota completa <i data-lucide="arrow-right" class="h-4 w-4"></i></a>
      </div>
    </div>

    <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($brandTiles as $i => $brand): ?>
      <a href="#catalogo" class="lux-card group p-7 flex flex-col justify-between min-h-[168px]" data-aos="fade-up" data-aos-delay="<?= ($i%3)*70 ?>">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-[.2em] text-[var(--lux-dim)]"><?= e($descriptors[$i % count($descriptors)]) ?></span>
          <i data-lucide="arrow-up-right" class="h-5 w-5 text-[var(--lux-dim)] group-hover:text-[var(--lux-brand-text)] transition-colors"></i>
        </div>
        <div>
          <p class="font-display text-3xl font-extrabold tracking-[-.04em] text-white group-hover:text-[var(--lux-brand-text)] transition-colors"><?= e($brand['name']) ?></p>
          <p class="mt-1 text-sm text-[var(--lux-dim)]"><?= (int)$brand['count'] ?> disponible<?= $brand['count'] === 1 ? '' : 's' ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================== FLOTA DESTACADA (preview) ============================== -->
<section id="flota" class="py-20 lg:py-24" style="background:#0d0d0d">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between" data-aos="fade-up">
      <div>
        <span class="lux-eyebrow">Flota destacada</span>
        <h2 class="mt-5 font-display text-[clamp(30px,4vw,56px)] font-extrabold tracking-[-.045em] text-white">Vehículos listos para reservar.</h2>
      </div>
      <a href="#catalogo" class="inline-flex lux-link items-center gap-2 shrink-0">Ver toda la flota <i data-lucide="arrow-down" class="h-4 w-4"></i></a>
    </div>

    <?php if (!empty($fleetPreview)): ?>
    <div class="mt-12 grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
      <?php foreach ($fleetPreview as $i => $v):
        $detail = url('/r/'.$tenant['slug'].'/vehiculo/'.$v['slug']);
        $reserve = url('/r/'.$tenant['slug'].'/reservar/'.$v['slug'].($rangeStart?'?start='.urlencode($rangeStart).'&end='.urlencode($rangeEnd):'')); ?>
      <article class="lux-card group overflow-hidden flex flex-col" data-aos="fade-up" data-aos-delay="<?= ($i%4)*60 ?>">
        <a href="<?= e($detail) ?>" class="relative block overflow-hidden">
          <?php if (!empty($v['main_image'])): ?>
            <img src="<?= e(media($v['main_image'])) ?>" alt="<?= e($v['brand'].' '.$v['model']) ?>" class="aspect-[16/11] w-full object-cover transition duration-700 group-hover:scale-105">
          <?php else: ?>
            <div class="aspect-[16/11] grid place-items-center text-[#2a2a2a]"><i data-lucide="car" class="h-16 w-16"></i></div>
          <?php endif; ?>
          <span class="absolute top-3 left-3 lux-chip lux-chip-brand backdrop-blur"><?= e($v['category_name'] ?? 'Premium') ?></span>
        </a>
        <div class="p-5 flex flex-col flex-1">
          <p class="text-[11px] font-bold uppercase tracking-[.18em] text-[var(--lux-dim)]"><?= e($v['brand']) ?> · <?= e($v['year']) ?></p>
          <a href="<?= e($detail) ?>" class="mt-1.5 block font-display text-lg font-extrabold tracking-[-.03em] text-white hover:text-[var(--lux-brand-text)] transition-colors"><?= e($v['model']) ?></a>
          <div class="mt-3 flex flex-wrap items-center gap-3 text-[12px] text-[var(--lux-muted)]">
            <span class="flex items-center gap-1.5"><i data-lucide="users" class="h-3.5 w-3.5"></i><?= e($v['passengers']) ?></span>
            <span class="flex items-center gap-1.5"><i data-lucide="fuel" class="h-3.5 w-3.5"></i><?= e($fuelLabel($v['fuel_type'])) ?></span>
            <span class="flex items-center gap-1.5"><i data-lucide="cog" class="h-3.5 w-3.5"></i><?= $v['transmission']==='automatic'?'Auto':'Manual' ?></span>
          </div>
          <div class="mt-5 flex items-end justify-between border-t border-[#262626] pt-4">
            <p class="font-display text-xl font-extrabold text-white tnum"><?= money($v['daily_price']) ?><span class="block text-[11px] font-medium text-[var(--lux-dim)]">por día</span></p>
            <a href="<?= e($reserve) ?>" class="lux-btn lux-btn-brand lux-btn-sm">Reservar <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============================== SPOTLIGHT carousel ============================== -->
<?php if (count($spotlightJs) >= 1): ?>
<section class="relative overflow-hidden py-20 lg:py-28" style="background:#0a0a0a"
  x-data='luxSpotlight(<?= htmlspecialchars(json_encode($spotlightJs, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)'
  @mouseenter="stop()" @mouseleave="start()" @focusin="stop()" @focusout="start()">
  <div class="lux-grid absolute inset-0 opacity-60"></div>
  <div class="lux-orb" style="width:600px;height:600px;right:-180px;top:-140px;opacity:.14"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-4">
      <span class="lux-eyebrow">Flota destacada</span>
      <div class="flex items-center gap-4">
        <span class="font-display text-sm font-bold text-[var(--lux-muted)] tnum"><span x-text="String(index+1).padStart(2,'0')"></span> / <span x-text="String(items.length).padStart(2,'0')"></span></span>
        <div class="flex gap-2">
          <button @click="prev()" class="grid h-11 w-11 place-items-center rounded-full border border-[#363636] text-white hover:border-[var(--brand)] hover:text-[var(--lux-brand-text)] transition"><i data-lucide="arrow-left" class="h-4 w-4"></i></button>
          <button @click="next()" class="grid h-11 w-11 place-items-center rounded-full border border-[#363636] text-white hover:border-[var(--brand)] hover:text-[var(--lux-brand-text)] transition"><i data-lucide="arrow-right" class="h-4 w-4"></i></button>
        </div>
      </div>
    </div>

    <div class="relative mt-10 grid lg:grid-cols-[1.4fr_.85fr] gap-8 lg:gap-12 items-center">
      <!-- Big image -->
      <div class="relative">
        <span class="lux-watermark absolute -top-16 -left-2 text-[clamp(120px,18vw,260px)] hidden sm:block" x-text="String(index+1).padStart(2,'0')"></span>
        <div class="relative aspect-[16/10] overflow-hidden rounded-3xl border border-[#262626]" style="background:#141414">
          <template x-for="(it,i) in items" :key="i">
            <img x-show="i===index" x-transition.opacity.duration.500ms :src="it.img" :alt="it.brand+' '+it.model" class="absolute inset-0 h-full w-full object-cover">
          </template>
        </div>
      </div>

      <!-- Spec panel -->
      <div class="lux-surface rounded-3xl p-7 lg:p-8">
        <span class="lux-chip lux-chip-brand" x-text="items[index].category"></span>
        <h3 class="mt-5 font-display text-3xl lg:text-4xl font-extrabold tracking-[-.04em] leading-[1.02] text-white">
          <span x-text="items[index].brand"></span> <span x-text="items[index].model"></span>
        </h3>
        <p class="mt-1.5 text-sm text-[var(--lux-dim)]" x-text="'Modelo ' + items[index].year"></p>
        <div class="mt-7 grid grid-cols-2 gap-5">
          <div><p class="text-[12px] text-[var(--lux-dim)]">Pasajeros</p><p class="mt-1 font-display text-xl font-bold text-white" x-text="items[index].passengers"></p></div>
          <div><p class="text-[12px] text-[var(--lux-dim)]">Transmisión</p><p class="mt-1 font-display text-xl font-bold text-white" x-text="items[index].trans"></p></div>
          <div><p class="text-[12px] text-[var(--lux-dim)]">Combustible</p><p class="mt-1 font-display text-xl font-bold text-white" x-text="items[index].fuel"></p></div>
          <div><p class="text-[12px] text-[var(--lux-dim)]">Desde · por día</p><p class="mt-1 font-display text-xl font-extrabold" style="color:var(--lux-brand-text)" x-text="items[index].price"></p></div>
        </div>
        <a :href="items[index].url" class="mt-8 lux-btn lux-btn-brand w-full">Ver detalles <i data-lucide="arrow-up-right" class="h-4 w-4"></i></a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================== NOSOTROS / SERVICIO ============================== -->
<?php if ($featured): ?>
<section id="servicio" class="py-20 lg:py-28" style="background:#0d0d0d">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="grid lg:grid-cols-[1.1fr_.9fr] gap-12 lg:gap-16 items-center">
      <div class="relative order-2 lg:order-1" data-aos="fade-up">
        <div class="absolute -left-4 -top-4 h-20 w-20 rounded-tl-3xl border-l-2 border-t-2" style="border-color:var(--lux-brand-text)"></div>
        <div class="relative overflow-hidden rounded-3xl border border-[#262626]" style="background:#141414">
          <?php if (!empty($featured['main_image'])): ?>
            <img src="<?= e(media($featured['main_image'])) ?>" alt="<?= e($featured['brand'].' '.$featured['model']) ?>" class="aspect-[5/4] w-full object-cover">
          <?php else: ?>
            <div class="aspect-[5/4] grid place-items-center text-[#2a2a2a]"><i data-lucide="car" class="h-24 w-24"></i></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="order-1 lg:order-2" data-aos="fade-up" data-aos-delay="80">
        <span class="lux-eyebrow">Servicio personal</span>
        <h2 class="mt-5 font-display text-[clamp(32px,4.4vw,60px)] font-extrabold tracking-[-.05em] leading-[.98] text-white">Servicio al cliente <span style="color:var(--lux-brand-text)">superior.</span></h2>
        <p class="mt-6 max-w-xl text-[17px] leading-relaxed text-[var(--lux-muted)]">
          Coordinamos entrega y recogida en aeropuerto, hotel o domicilio. Cada vehículo se inspecciona antes de la entrega y la asistencia es directa, 24/7. Reserva en minutos y confirma disponibilidad sin fricción.
        </p>
        <div class="mt-9 grid sm:grid-cols-3 gap-5">
          <?php foreach ([
            ['shield-check','Inspeccionados','Cada unidad revisada antes de entregar'],
            ['map-pin','Entrega flexible','Aeropuerto, hotel o domicilio'],
            ['headset','Asistencia 24/7','Contacto directo cuando lo necesites'],
          ] as $b): ?>
          <div class="border-l-2 pl-4" style="border-color:var(--lux-brand-text)">
            <i data-lucide="<?= $b[0] ?>" class="h-5 w-5" style="color:var(--lux-brand-text)"></i>
            <h3 class="mt-3 font-bold text-white"><?= e($b[1]) ?></h3>
            <p class="mt-1 text-sm text-[var(--lux-dim)]"><?= e($b[2]) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================== CATÁLOGO (filtros) ============================== -->
<section id="catalogo" class="max-w-7xl mx-auto px-4 sm:px-6 py-20 lg:py-24">
  <div class="mb-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between" data-aos="fade-up">
    <div>
      <span class="lux-eyebrow">Catálogo completo</span>
      <h2 class="mt-5 font-display text-[clamp(30px,4vw,56px)] font-extrabold tracking-[-.045em] text-white">Encuentra la unidad ideal.</h2>
    </div>
    <p class="max-w-xl text-[var(--lux-muted)]">Filtra por fecha, precio, combustible y capacidad. La disponibilidad se valida antes de confirmar la reserva.</p>
  </div>

  <form method="GET" class="grid lg:grid-cols-[300px_1fr] gap-8"
        x-data='priceRange(<?= $hMin ?>, <?= $hMax ?>, <?= (int)$loVal ?>, <?= (int)$hiVal ?>)'>

    <!-- FILTER RAIL -->
    <aside class="lg:sticky lg:top-[92px] h-fit">
      <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>" x-ref="sortMirror">
      <div class="lux-surface rounded-2xl p-6">
        <div class="flex items-center justify-between">
          <h3 class="font-display font-extrabold tracking-[-.03em] text-white">Filtrar</h3>
          <a href="<?= url('/r/'.$tenant['slug'].'#catalogo') ?>" class="text-xs font-semibold text-[var(--lux-dim)] hover:text-[var(--lux-brand-text)] transition-colors">Limpiar todo</a>
        </div>

        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-[.16em] text-[var(--lux-dim)] mb-2.5">Fechas de renta</p>
          <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start" value="<?= e($rangeStart) ?>" class="lux-field !h-11 !text-[13px]">
            <input type="date" name="end" value="<?= e($rangeEnd) ?>" class="lux-field !h-11 !text-[13px]">
          </div>
        </div>

        <div class="mt-7">
          <p class="text-[11px] font-bold uppercase tracking-[.16em] text-[var(--lux-dim)] mb-2.5">Precio por día</p>
          <div class="flex items-end gap-[2px] h-12 mb-2">
            <?php foreach ($bars as $i => $count):
              $hh = max(8, (int)round(($count / $maxBar) * 100)); ?>
              <div class="flex-1 rounded-sm transition-colors" :class="barActive(<?= $i ?>, <?= count($bars) ?>) ? 'bg-[color:var(--lux-brand-text)]' : 'bg-[#2a2a2a]'" style="height: <?= $hh ?>%"></div>
            <?php endforeach; ?>
          </div>
          <div class="range-wrap">
            <div class="range-track"></div>
            <div class="range-fill" :style="fillStyle()"></div>
            <input type="range" :min="min" :max="max" x-model.number="lo" @input="clamp()">
            <input type="range" :min="min" :max="max" x-model.number="hi" @input="clamp()">
          </div>
          <input type="hidden" name="price_min" :value="lo">
          <input type="hidden" name="price_max" :value="hi">
          <div class="flex items-center justify-between mt-3 text-sm">
            <span class="px-2.5 py-1 rounded-lg bg-[#1a1a1a] font-semibold text-white tnum" x-text="money(lo)"></span>
            <span class="text-[var(--lux-dim)]">—</span>
            <span class="px-2.5 py-1 rounded-lg bg-[#1a1a1a] font-semibold text-white tnum" x-text="money(hi)"></span>
          </div>
        </div>

        <?php if (!empty($categories)): ?>
        <div class="mt-7">
          <p class="text-[11px] font-bold uppercase tracking-[.16em] text-[var(--lux-dim)] mb-2.5">Categoría</p>
          <div class="space-y-1 max-h-44 overflow-y-auto pr-1">
            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-[#1a1a1a] cursor-pointer transition-colors">
              <input type="radio" name="category_id" value="" <?= !$filters['category_id']?'checked':'' ?> style="accent-color:var(--lux-brand-text)">
              <span class="text-sm text-[var(--lux-muted)]">Todas</span>
            </label>
            <?php foreach ($categories as $c): ?>
            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-[#1a1a1a] cursor-pointer transition-colors">
              <input type="radio" name="category_id" value="<?= $c['id'] ?>" <?= $filters['category_id']==$c['id']?'checked':'' ?> style="accent-color:var(--lux-brand-text)">
              <span class="text-sm text-[var(--lux-muted)]"><?= e($c['name']) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="mt-7">
          <p class="text-[11px] font-bold uppercase tracking-[.16em] text-[var(--lux-dim)] mb-2.5">Transmisión</p>
          <div class="seg w-full grid grid-cols-3">
            <?php foreach (['' =>'Todas','automatic'=>'Auto','manual'=>'Manual'] as $val=>$lbl): $id='tr_'.($val?:'all'); ?>
              <input type="radio" id="<?= $id ?>" name="transmission" value="<?= $val ?>" <?= $filters['transmission']===$val?'checked':'' ?>>
              <label for="<?= $id ?>" class="text-center justify-center flex"><?= $lbl ?></label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mt-7">
          <p class="text-[11px] font-bold uppercase tracking-[.16em] text-[var(--lux-dim)] mb-2.5">Combustible</p>
          <div class="grid grid-cols-2 gap-1">
            <?php foreach (['' =>'Todos','gasoline'=>'Gasolina','diesel'=>'Diésel','electric'=>'Eléctrico','hybrid'=>'Híbrido'] as $val=>$lbl): $id='fu_'.($val?:'all'); ?>
            <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-[#1a1a1a] cursor-pointer transition-colors">
              <input type="radio" id="<?= $id ?>" name="fuel_type" value="<?= $val ?>" <?= $filters['fuel_type']===$val?'checked':'' ?> style="accent-color:var(--lux-brand-text)">
              <span class="text-sm text-[var(--lux-muted)]"><?= $lbl ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mt-7">
          <p class="text-[11px] font-bold uppercase tracking-[.16em] text-[var(--lux-dim)] mb-2.5">Pasajeros (mín)</p>
          <div class="flex gap-1.5">
            <?php foreach (['' =>'Todos',2=>'2',4=>'4',5=>'5',7=>'7+'] as $val=>$lbl): ?>
              <label class="flex-1">
                <input type="radio" name="passengers" value="<?= $val ?>" <?= (string)$filters['passengers']===(string)$val?'checked':'' ?> class="peer sr-only">
                <span class="block text-center text-sm py-1.5 rounded-lg border border-[#363636] text-[var(--lux-muted)] cursor-pointer transition peer-checked:bg-brand peer-checked:text-[var(--lux-ink)] peer-checked:border-[color:var(--lux-brand-text)]"><?= $lbl ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="lux-btn lux-btn-brand w-full mt-7">Aplicar filtros</button>
      </div>
    </aside>

    <!-- RESULTS -->
    <section>
      <div class="flex items-center justify-between gap-3 mb-6">
        <div>
          <h3 class="font-display text-2xl font-extrabold tracking-[-.035em] text-white"><?= count($vehicles) ?> vehículos</h3>
          <p class="text-sm text-[var(--lux-dim)]">disponibles para rentar</p>
        </div>
        <div class="flex items-center gap-2">
          <label class="text-sm text-[var(--lux-dim)] hidden sm:block">Ordenar:</label>
          <select name="sort" onchange="this.form.submit()" class="lux-field !h-11 !w-auto !text-[13px] font-medium">
            <option value="" <?= $filters['sort']===''?'selected':'' ?>>Destacados</option>
            <option value="price_asc" <?= $filters['sort']==='price_asc'?'selected':'' ?>>Precio: menor</option>
            <option value="price_desc" <?= $filters['sort']==='price_desc'?'selected':'' ?>>Precio: mayor</option>
            <option value="newest" <?= $filters['sort']==='newest'?'selected':'' ?>>Más nuevos</option>
          </select>
        </div>
      </div>

      <?php if (empty($vehicles)): ?>
        <div class="lux-surface rounded-2xl p-16 text-center">
          <div class="w-14 h-14 rounded-2xl bg-[#1a1a1a] grid place-items-center mx-auto"><i data-lucide="search-x" class="w-7 h-7 text-[var(--lux-dim)]"></i></div>
          <h3 class="font-semibold text-white mt-4">Sin resultados</h3>
          <p class="text-sm text-[var(--lux-dim)] mt-1">Ajusta los filtros para ver más vehículos.</p>
          <a href="<?= url('/r/'.$tenant['slug'].'#catalogo') ?>" class="inline-block mt-4 text-sm font-semibold" style="color:var(--lux-brand-text)">Limpiar filtros</a>
        </div>
      <?php else: ?>
      <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php foreach ($vehicles as $i => $v):
          $unavailable = isset($v['available_in_range']) && !$v['available_in_range'];
          $detail = url('/r/'.$tenant['slug'].'/vehiculo/'.$v['slug']);
          $reserve = url('/r/'.$tenant['slug'].'/reservar/'.$v['slug'].($rangeStart?'?start='.urlencode($rangeStart).'&end='.urlencode($rangeEnd):'')); ?>
        <article class="lux-card group overflow-hidden flex flex-col <?= $unavailable?'opacity-55':'' ?>"
                 data-aos="fade-up" data-aos-delay="<?= ($i%3)*60 ?>">
          <a href="<?= e($detail) ?>" class="relative block overflow-hidden">
            <?php if (!empty($v['main_image'])): ?>
              <img src="<?= e(media($v['main_image'])) ?>" alt="<?= e($v['brand'].' '.$v['model']) ?>" class="aspect-[16/10] w-full object-cover transition duration-700 group-hover:scale-105">
            <?php else: ?>
              <div class="aspect-[16/10] grid place-items-center text-[#2a2a2a]"><i data-lucide="car" class="w-16 h-16"></i></div>
            <?php endif; ?>
            <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
              <span class="lux-chip backdrop-blur"><i data-lucide="car-front" class="w-3.5 h-3.5"></i><?= e($v['category_name'] ?? 'Vehículo') ?></span>
              <?php if ($unavailable): ?>
                <span class="lux-chip backdrop-blur" style="color:#fca5a5;border-color:rgba(248,113,113,.4)">No disponible</span>
              <?php else: ?>
                <span class="lux-chip backdrop-blur" style="color:#86efac;border-color:rgba(34,197,94,.35)"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Disponible</span>
              <?php endif; ?>
            </div>
          </a>
          <div class="p-5 flex flex-col flex-1">
            <a href="<?= e($detail) ?>" class="font-display font-extrabold tracking-[-.03em] text-white text-[17px] leading-tight hover:text-[var(--lux-brand-text)] transition-colors"><?= e($v['brand'].' '.$v['model']) ?></a>
            <p class="text-[13px] text-[var(--lux-dim)] mt-0.5"><?= e($v['version'] ? $v['version'].' · '.$v['year'] : $v['year']) ?></p>
            <div class="flex flex-wrap items-center gap-3.5 mt-3.5 text-[12px] text-[var(--lux-muted)]">
              <span class="flex items-center gap-1.5"><i data-lucide="users" class="w-3.5 h-3.5"></i><?= $v['passengers'] ?></span>
              <span class="flex items-center gap-1.5"><i data-lucide="cog" class="w-3.5 h-3.5"></i><?= $v['transmission']==='automatic'?'Auto':'Manual' ?></span>
              <span class="flex items-center gap-1.5"><i data-lucide="fuel" class="w-3.5 h-3.5"></i><?= e($fuelLabel($v['fuel_type'])) ?></span>
              <span class="flex items-center gap-1.5"><i data-lucide="briefcase" class="w-3.5 h-3.5"></i><?= $v['luggage_capacity'] ?></span>
            </div>
            <div class="flex items-end justify-between mt-5 pt-4 border-t border-[#262626]">
              <p class="font-display text-xl font-extrabold text-white leading-none tnum"><?= money($v['daily_price']) ?><span class="text-[12px] font-medium text-[var(--lux-dim)]">/día</span></p>
              <a href="<?= e($reserve) ?>" class="lux-btn lux-btn-brand lux-btn-sm">Reservar <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>
  </form>
</section>

<!-- ============================== CTA ============================== -->
<section class="relative overflow-hidden" style="background:#0d0d0d">
  <div class="lux-orb" style="width:520px;height:520px;left:50%;top:-260px;transform:translateX(-50%);opacity:.18"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-20 lg:py-28">
    <div class="grid lg:grid-cols-[1fr_auto] gap-10 items-end">
      <div data-aos="fade-up">
        <span class="lux-eyebrow">¿Listo para tu viaje?</span>
        <h2 class="mt-5 max-w-3xl font-display text-[clamp(34px,5vw,72px)] font-extrabold tracking-[-.05em] leading-[.96] text-white">Reserva en minutos. Confirmamos por WhatsApp.</h2>
      </div>
      <div class="flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="100">
        <a href="#catalogo" class="lux-btn lux-btn-light px-7 py-4">Explorar catálogo <i data-lucide="arrow-up-right" class="h-4 w-4"></i></a>
        <?php if (!empty($tenant['whatsapp'])): ?>
        <a href="<?= e(whatsapp_link($tenant['whatsapp'])) ?>" target="_blank" class="lux-btn lux-btn-outline px-7 py-4">Contactar asesor <i data-lucide="message-circle" class="h-4 w-4"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php echo View::renderPartial('public/storefront/_footer', ['tenant' => $tenant]); ?>

<?php View::push('scripts', '<script>
function priceRange(min,max,lo,hi){
  return {
    min, max, lo: Math.max(min, lo||min), hi: Math.min(max, hi||max),
    money(v){ return "'.addslashes($curSym).' " + Math.round(v); },
    clamp(){ if(this.lo<this.min)this.lo=this.min; if(this.hi>this.max)this.hi=this.max; this.lo=Math.min(this.lo,this.hi); this.hi=Math.max(this.lo,this.hi); },
    pct(v){ return this.max === this.min ? 0 : ((v - this.min) / (this.max - this.min)) * 100; },
    fillStyle(){ return { left: this.pct(this.lo) + "%", width: (this.pct(this.hi) - this.pct(this.lo)) + "%" }; },
    barActive(i,n){ const span=(this.max-this.min)/n; const center=this.min + (i+0.5)*span; return center>=this.lo && center<=this.hi; }
  }
}
function luxSpotlight(items){
  return {
    items, index:0, timer:null,
    reduced(){ return window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches; },
    init(){ this.start(); },
    start(){ this.stop(); if(this.items.length>1 && !this.reduced()){ this.timer=setInterval(()=>this.next(), 6000); } },
    stop(){ if(this.timer){ clearInterval(this.timer); this.timer=null; } },
    next(){ this.index=(this.index+1)%this.items.length; this.ping(); },
    prev(){ this.index=(this.index-1+this.items.length)%this.items.length; this.ping(); },
    ping(){ this.$nextTick(()=>window.lucide&&lucide.createIcons()); }
  }
}
</script>'); ?>
