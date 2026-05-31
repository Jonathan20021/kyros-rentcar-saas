<?php
use App\Core\View;

echo View::renderPartial('public/storefront/_nav', ['tenant' => $tenant]);

$primary = $tenant['primary_color'];
$cover   = $tenant['cover_image'] ?? null;
$hMin = $histogram['min']; $hMax = $histogram['max']; $bars = $histogram['bars'];
$loVal = $filters['price_min'] ?: $hMin;
$hiVal = $filters['price_max'] ?: $hMax;
$maxBar = max(1, max($bars));

$allVehicles = $vehicles;
$featured = null;
foreach ($allVehicles as $candidate) {
  if (!empty($candidate['main_image']) && (int)($candidate['is_featured'] ?? 0) === 1) { $featured = $candidate; break; }
}
if (!$featured) {
  foreach ($allVehicles as $candidate) {
    if (!empty($candidate['main_image'])) { $featured = $candidate; break; }
  }
}
if (!$featured && !empty($allVehicles)) { $featured = $allVehicles[0]; }

$heroImage = $cover ?: ($featured['main_image'] ?? 'demo/vehicles/landing-hero-fleet.jpg');
$brandMap = [];
foreach ($allVehicles as $v) {
  $brand = trim((string)($v['brand'] ?? ''));
  if ($brand === '') continue;
  if (!isset($brandMap[$brand])) {
    $brandMap[$brand] = [
      'name' => $brand,
      'category' => $v['category_name'] ?? 'Flota premium',
      'count' => 0,
    ];
  }
  $brandMap[$brand]['count']++;
}
$brandTiles = array_slice(array_values($brandMap), 0, 6);
$fleetPreview = array_slice($allVehicles, 0, 8);
$featuredUrl = $featured ? url('/r/'.$tenant['slug'].'/vehiculo/'.$featured['slug']) : '#catalogo';
$heroAlt = $featured ? trim($featured['brand'].' '.$featured['model']) : ($tenant['name'].' flota');
?>

<!-- Editorial hero -->
<section id="inicio" class="relative min-h-[88dvh] overflow-hidden bg-[#101620] text-white">
  <div class="absolute inset-0">
    <img src="<?= e(media($heroImage)) ?>" class="h-full w-full object-cover opacity-70" alt="<?= e($heroAlt) ?>">
    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(8,12,18,.96)_0%,rgba(8,12,18,.72)_42%,rgba(8,12,18,.18)_100%)]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_16%,color-mix(in_srgb,var(--brand)_38%,transparent),transparent_32%),radial-gradient(circle_at_88%_72%,rgba(255,255,255,.18),transparent_24%)]"></div>
  </div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 pt-20 lg:pt-28 pb-16">
    <div class="grid lg:grid-cols-[minmax(0,1fr)_390px] gap-10 items-end min-h-[70dvh]">
      <div class="max-w-4xl" data-aos="fade-up">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-[10px] sm:text-[11px] font-bold uppercase tracking-[.18em] sm:tracking-[.28em] text-white/68">
          <span><?= e($tenant['address'] ? strtok($tenant['address'], ',') : 'Republica Dominicana') ?></span>
          <span class="h-px w-8 sm:w-10 bg-white/30"></span>
          <span>Flota curada</span>
          <span class="h-px w-8 sm:w-10 bg-white/30"></span>
          <span>Servicio 24/7</span>
        </div>
        <h1 class="mt-6 max-w-[10ch] sm:max-w-4xl font-display text-[44px] sm:text-6xl lg:text-[88px] font-black leading-[.94] sm:leading-[.9] tracking-[-.045em] sm:tracking-[-.055em]">
          Renta el auto correcto. Conduce sin friccion.
        </h1>
        <p class="mt-6 max-w-2xl text-lg sm:text-xl leading-relaxed text-white/78">
          <?= e($tenant['description'] ?? 'Flota moderna, reservas rapidas y atencion personalizada para cada viaje.') ?>
        </p>
        <div class="mt-8 flex flex-col sm:flex-row sm:flex-wrap gap-3">
          <a href="#catalogo" class="inline-flex items-center justify-center gap-2 rounded-none px-5 sm:px-6 py-3.5 text-sm font-bold text-white shadow-card transition hover:-translate-y-0.5" style="background:var(--brand)">
            Reservar un vehiculo <i data-lucide="arrow-right" class="h-4 w-4"></i>
          </a>
          <?php if (!empty($tenant['whatsapp'])): ?>
          <a href="<?= e(whatsapp_link($tenant['whatsapp'], 'Hola ' . $tenant['name'] . ', quiero informacion sobre alquiler de vehiculos.')) ?>" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-none border border-white/24 bg-white/8 px-5 sm:px-6 py-3.5 text-sm font-bold text-white backdrop-blur transition hover:bg-white/14">
            Hablar con un asesor <i data-lucide="message-circle" class="h-4 w-4"></i>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <aside class="hidden lg:block border border-white/14 bg-white/[.07] p-5 backdrop-blur-xl" data-aos="fade-left">
        <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-[.22em] text-white/55">
          <span>Flota destacada</span>
          <span><?= count($allVehicles) ?> unidades</span>
        </div>
        <?php if ($featured): ?>
        <a href="<?= e($featuredUrl) ?>" class="group mt-5 block overflow-hidden bg-black/25">
          <?php if (!empty($featured['main_image'])): ?>
            <img src="<?= e(media($featured['main_image'])) ?>" alt="<?= e($featured['brand'].' '.$featured['model']) ?>" class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-[1.04]">
          <?php else: ?>
            <div class="aspect-[4/3] grid place-items-center text-white/30"><i data-lucide="car" class="h-20 w-20"></i></div>
          <?php endif; ?>
        </a>
        <div class="mt-5 grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-white/45">Modelo</p>
            <p class="mt-1 font-semibold text-white"><?= e($featured['brand'].' '.$featured['model']) ?></p>
          </div>
          <div>
            <p class="text-white/45">Desde</p>
            <p class="mt-1 text-2xl font-black tnum"><?= money($featured['daily_price']) ?></p>
          </div>
          <div>
            <p class="text-white/45">Pasajeros</p>
            <p class="mt-1 font-semibold"><?= e($featured['passengers']) ?></p>
          </div>
          <div>
            <p class="text-white/45">Combustible</p>
            <p class="mt-1 font-semibold"><?= e(ucfirst($featured['fuel_type'])) ?></p>
          </div>
        </div>
        <a href="<?= e($featuredUrl) ?>" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-white hover:text-brand">
          Ver detalles <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
        </a>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</section>

<!-- Brand collection -->
<?php if (!empty($brandTiles)): ?>
<section id="marcas" class="bg-white py-16 lg:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="grid lg:grid-cols-[.7fr_1fr] gap-10 items-end">
      <div>
        <p class="text-[11px] font-black uppercase tracking-[.26em] text-brand">Marcas destacadas</p>
        <h2 class="mt-3 font-display text-3xl lg:text-5xl font-black tracking-[-.045em] text-ink">Selecciona por estilo, marca o plan de viaje.</h2>
      </div>
      <p class="max-w-2xl text-slate-500 leading-relaxed lg:justify-self-end">
        Explora nuestra flota por marca: vehiculos inspeccionados, listos para entregar y con reserva directa por WhatsApp o formulario.
      </p>
    </div>

    <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 border-y hairline">
      <?php foreach ($brandTiles as $i => $brand): ?>
      <a href="#catalogo" class="group min-h-[168px] border-b sm:border-r hairline p-5 transition hover:bg-slate-50 <?= $i >= 3 ? 'lg:border-b-0' : '' ?>">
        <div class="flex h-full flex-col justify-between">
          <span class="text-[11px] font-bold uppercase tracking-[.22em] text-slate-400"><?= e($brand['category']) ?></span>
          <div>
            <p class="font-display text-2xl font-black tracking-[-.04em] text-ink group-hover:text-brand"><?= e($brand['name']) ?></p>
            <p class="mt-1 text-sm text-slate-400"><?= (int)$brand['count'] ?> disponible<?= $brand['count'] === 1 ? '' : 's' ?></p>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Fleet preview -->
<section class="bg-[#F4F6FA] py-16 lg:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-[11px] font-black uppercase tracking-[.26em] text-brand">Flota destacada</p>
        <h2 class="mt-3 font-display text-3xl lg:text-5xl font-black tracking-[-.045em] text-ink">Vehiculos listos para reservar.</h2>
      </div>
      <a href="#catalogo" class="inline-flex items-center gap-2 text-sm font-bold text-ink hover:text-brand">Ver toda la flota <i data-lucide="arrow-down" class="h-4 w-4"></i></a>
    </div>

    <?php if (!empty($fleetPreview)): ?>
    <div class="mt-10 grid md:grid-cols-2 xl:grid-cols-4 gap-4">
      <?php foreach ($fleetPreview as $i => $v):
        $detail = url('/r/'.$tenant['slug'].'/vehiculo/'.$v['slug']);
        $reserve = url('/r/'.$tenant['slug'].'/reservar/'.$v['slug'].($rangeStart?'?start='.urlencode($rangeStart).'&end='.urlencode($rangeEnd):'')); ?>
      <article class="group bg-white border hairline overflow-hidden transition duration-300 hover:-translate-y-1 hover:shadow-lift" data-aos="fade-up" data-aos-delay="<?= ($i%4)*45 ?>">
        <a href="<?= e($detail) ?>" class="block bg-slate-100">
          <?php if (!empty($v['main_image'])): ?>
            <img src="<?= e(media($v['main_image'])) ?>" alt="<?= e($v['brand'].' '.$v['model']) ?>" class="aspect-[16/11] w-full object-cover transition duration-500 group-hover:scale-[1.04]">
          <?php else: ?>
            <div class="aspect-[16/11] grid place-items-center text-slate-300"><i data-lucide="car" class="h-16 w-16"></i></div>
          <?php endif; ?>
        </a>
        <div class="p-5">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-[11px] font-bold uppercase tracking-[.18em] text-slate-400"><?= e($v['brand']) ?> · <?= e($v['year']) ?></p>
              <a href="<?= e($detail) ?>" class="mt-1 block font-display text-xl font-black tracking-[-.035em] text-ink hover:text-brand"><?= e($v['model']) ?></a>
            </div>
            <p class="text-right text-lg font-black text-ink tnum"><?= money($v['daily_price']) ?><span class="block text-[11px] font-medium text-slate-400">por dia</span></p>
          </div>
          <div class="mt-4 grid grid-cols-3 gap-2 text-[12px] text-slate-500">
            <span class="flex items-center gap-1"><i data-lucide="users" class="h-3.5 w-3.5"></i><?= e($v['passengers']) ?></span>
            <span class="flex items-center gap-1"><i data-lucide="fuel" class="h-3.5 w-3.5"></i><?= e(ucfirst($v['fuel_type'])) ?></span>
            <span class="flex items-center gap-1"><i data-lucide="cog" class="h-3.5 w-3.5"></i><?= $v['transmission']==='automatic'?'Auto':'Manual' ?></span>
          </div>
          <div class="mt-5 flex items-center justify-between border-t hairline pt-4">
            <a href="<?= e($detail) ?>" class="text-sm font-bold text-ink hover:text-brand">Ver</a>
            <a href="<?= e($reserve) ?>" class="inline-flex items-center gap-1.5 px-4 py-2 text-[13px] font-bold text-white transition hover:opacity-90" style="background:var(--navy)">Reservar <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Feature editorial -->
<?php if ($featured): ?>
<section id="nosotros" class="bg-white py-16 lg:py-24 overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="grid lg:grid-cols-[.9fr_1.1fr] gap-10 lg:gap-16 items-center">
      <div class="relative order-2 lg:order-1">
        <div class="absolute -left-6 -top-6 h-24 w-24 border-l-2 border-t-2 border-brand"></div>
        <div class="relative bg-[#101620] p-3">
          <?php if (!empty($featured['main_image'])): ?>
            <img src="<?= e(media($featured['main_image'])) ?>" alt="<?= e($featured['brand'].' '.$featured['model']) ?>" class="aspect-[5/4] w-full object-cover">
          <?php else: ?>
            <div class="aspect-[5/4] grid place-items-center text-white/30"><i data-lucide="car" class="h-24 w-24"></i></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="order-1 lg:order-2">
        <p class="text-[11px] font-black uppercase tracking-[.26em] text-brand">Servicio personal</p>
        <h2 class="mt-3 font-display text-4xl lg:text-6xl font-black tracking-[-.055em] leading-[.95] text-ink">Entrega coordinada, soporte directo y autos inspeccionados.</h2>
        <p class="mt-6 max-w-2xl text-lg leading-relaxed text-slate-500">
          Presentamos nuestra flota con imagenes reales, fichas completas de cada vehiculo y reserva directa. Filtra por lo que necesitas y confirma tu unidad en minutos, con entrega coordinada y atencion personalizada.
        </p>
        <div class="mt-8 grid sm:grid-cols-3 gap-4">
          <?php foreach ([['shield-check','Seguro','Cobertura disponible'],['map-pin','Entrega','Aeropuerto, hotel o domicilio'],['headset','Asistencia','Contacto directo 24/7']] as $b): ?>
          <div class="border-l-2 pl-4" style="border-color:var(--brand)">
            <i data-lucide="<?= $b[0] ?>" class="h-5 w-5 text-brand"></i>
            <h3 class="mt-3 font-bold text-ink"><?= e($b[1]) ?></h3>
            <p class="mt-1 text-sm text-slate-500"><?= e($b[2]) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Catalog browser -->
<section id="catalogo" class="max-w-7xl mx-auto px-4 sm:px-6 py-16 lg:py-20">
  <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
      <p class="text-[11px] font-black uppercase tracking-[.26em] text-brand">Catalogo completo</p>
      <h2 class="mt-3 font-display text-3xl lg:text-5xl font-black tracking-[-.045em] text-ink">Encuentra la unidad ideal.</h2>
    </div>
    <p class="max-w-xl text-slate-500">Filtra por fecha, precio, combustible y capacidad. La disponibilidad se valida antes de confirmar la reserva.</p>
  </div>

  <form method="GET" class="grid lg:grid-cols-[290px_1fr] gap-7"
        x-data='priceRange(<?= $hMin ?>, <?= $hMax ?>, <?= (int)$loVal ?>, <?= (int)$hiVal ?>)'>

    <!-- FILTER RAIL -->
    <aside class="lg:sticky lg:top-[88px] h-fit">
      <input type="hidden" name="start" value="<?= e($rangeStart) ?>">
      <input type="hidden" name="end" value="<?= e($rangeEnd) ?>">
      <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>" x-ref="sortMirror">

      <div class="bg-white border hairline shadow-card p-5">
        <div class="flex items-center justify-between">
          <h2 class="font-display font-black tracking-[-.03em] text-ink">Filtrar</h2>
          <a href="<?= url('/r/'.$tenant['slug'].'#catalogo') ?>" class="text-xs font-semibold text-slate-400 hover:text-brand">Limpiar todo</a>
        </div>

        <div class="mt-5">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Fechas de renta</p>
          <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start" value="<?= e($rangeStart) ?>" class="fld !py-2 !text-[13px]">
            <input type="date" name="end" value="<?= e($rangeEnd) ?>" class="fld !py-2 !text-[13px]">
          </div>
        </div>

        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Precio por dia</p>
          <div class="flex items-end gap-[2px] h-12 mb-1">
            <?php foreach ($bars as $i => $count):
              $h = max(8, (int)round(($count / $maxBar) * 100)); ?>
              <div class="flex-1 rounded-sm transition-colors" :class="barActive(<?= $i ?>, <?= count($bars) ?>) ? 'bg-[color:var(--brand)]' : 'bg-slate-200'" style="height: <?= $h ?>%"></div>
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
          <div class="flex items-center justify-between mt-2 text-sm">
            <span class="px-2.5 py-1 bg-slate-100 font-semibold text-ink" x-text="money(lo)"></span>
            <span class="text-slate-300">-</span>
            <span class="px-2.5 py-1 bg-slate-100 font-semibold text-ink" x-text="money(hi)"></span>
          </div>
        </div>

        <?php if (!empty($categories)): ?>
        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Categoria</p>
          <div class="space-y-1 max-h-44 overflow-y-auto pr-1">
            <label class="flex items-center gap-2.5 px-2 py-1.5 hover:bg-slate-50 cursor-pointer">
              <input type="radio" name="category_id" value="" <?= !$filters['category_id']?'checked':'' ?> class="text-brand focus:ring-brand/30">
              <span class="text-sm text-slate-600">Todas</span>
            </label>
            <?php foreach ($categories as $c): ?>
            <label class="flex items-center gap-2.5 px-2 py-1.5 hover:bg-slate-50 cursor-pointer">
              <input type="radio" name="category_id" value="<?= $c['id'] ?>" <?= $filters['category_id']==$c['id']?'checked':'' ?> class="text-brand focus:ring-brand/30">
              <span class="text-sm text-slate-600"><?= e($c['name']) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Transmision</p>
          <div class="seg w-full grid grid-cols-3">
            <?php foreach (['' =>'Todas','automatic'=>'Auto','manual'=>'Manual'] as $val=>$lbl): $id='tr_'.($val?:'all'); ?>
              <input type="radio" id="<?= $id ?>" name="transmission" value="<?= $val ?>" <?= $filters['transmission']===$val?'checked':'' ?>>
              <label for="<?= $id ?>" class="text-center justify-center flex"><?= $lbl ?></label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Combustible</p>
          <div class="grid grid-cols-2 gap-1">
            <?php foreach (['' =>'Todos','gasoline'=>'Gasolina','diesel'=>'Diesel','electric'=>'Electrico','hybrid'=>'Hibrido'] as $val=>$lbl): $id='fu_'.($val?:'all'); ?>
            <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-slate-50 cursor-pointer">
              <input type="radio" id="<?= $id ?>" name="fuel_type" value="<?= $val ?>" <?= $filters['fuel_type']===$val?'checked':'' ?> class="text-brand focus:ring-brand/30">
              <span class="text-sm text-slate-600"><?= $lbl ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Pasajeros (min)</p>
          <div class="flex gap-1.5">
            <?php foreach (['' =>'Todos',2=>'2',4=>'4',5=>'5',7=>'7+'] as $val=>$lbl): ?>
              <label class="flex-1">
                <input type="radio" name="passengers" value="<?= $val ?>" <?= (string)$filters['passengers']===(string)$val?'checked':'' ?> class="peer sr-only">
                <span class="block text-center text-sm py-1.5 border hairline cursor-pointer peer-checked:bg-ink peer-checked:text-white peer-checked:border-ink"><?= $lbl ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="w-full mt-6 py-2.5 text-white font-bold shadow-card hover:opacity-90 transition" style="background:<?= e($primary) ?>">Aplicar filtros</button>
      </div>
    </aside>

    <!-- RESULTS -->
    <section>
      <div class="flex items-center justify-between gap-3 mb-5">
        <div>
          <h2 class="font-display text-2xl font-black tracking-[-.035em] text-ink"><?= count($vehicles) ?> vehiculos</h2>
          <p class="text-sm text-slate-400">disponibles para rentar</p>
        </div>
        <div class="flex items-center gap-2">
          <label class="text-sm text-slate-400 hidden sm:block">Ordenar:</label>
          <select name="sort" onchange="this.form.submit()" class="fld !py-2 !w-auto !text-[13px] font-medium">
            <option value="" <?= $filters['sort']===''?'selected':'' ?>>Destacados</option>
            <option value="price_asc" <?= $filters['sort']==='price_asc'?'selected':'' ?>>Precio: menor</option>
            <option value="price_desc" <?= $filters['sort']==='price_desc'?'selected':'' ?>>Precio: mayor</option>
            <option value="newest" <?= $filters['sort']==='newest'?'selected':'' ?>>Mas nuevos</option>
          </select>
        </div>
      </div>

      <?php if (empty($vehicles)): ?>
        <div class="bg-white border hairline p-16 text-center">
          <div class="w-14 h-14 bg-slate-100 grid place-items-center mx-auto"><i data-lucide="search-x" class="w-7 h-7 text-slate-400"></i></div>
          <h3 class="font-semibold text-ink mt-4">Sin resultados</h3>
          <p class="text-sm text-slate-400 mt-1">Ajusta los filtros para ver mas vehiculos.</p>
          <a href="<?= url('/r/'.$tenant['slug'].'#catalogo') ?>" class="inline-block mt-4 text-sm font-semibold text-brand hover:underline">Limpiar filtros</a>
        </div>
      <?php else: ?>
      <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php foreach ($vehicles as $i => $v):
          $unavailable = isset($v['available_in_range']) && !$v['available_in_range'];
          $detail = url('/r/'.$tenant['slug'].'/vehiculo/'.$v['slug']);
          $reserve = url('/r/'.$tenant['slug'].'/reservar/'.$v['slug'].($rangeStart?'?start='.urlencode($rangeStart).'&end='.urlencode($rangeEnd):'')); ?>
        <article class="group bg-white border hairline shadow-card hover:shadow-lift hover:-translate-y-0.5 transition-all duration-200 overflow-hidden flex flex-col <?= $unavailable?'opacity-60':'' ?>"
                 data-aos="fade-up" data-aos-delay="<?= ($i%3)*60 ?>">
          <div class="relative">
            <div class="flex items-center justify-between px-4 pt-4">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-[12px] font-semibold text-slate-600">
                <i data-lucide="car-front" class="w-3.5 h-3.5"></i><?= e($v['category_name'] ?? 'Vehiculo') ?>
              </span>
              <?php if ($unavailable): ?>
                <span class="px-2.5 py-1 bg-red-50 text-red-600 text-[12px] font-semibold">No disponible</span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[12px] font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Disponible</span>
              <?php endif; ?>
            </div>
            <a href="<?= e($detail) ?>" class="block aspect-[16/10] grid place-items-center px-3 py-1">
              <?php if (!empty($v['main_image'])): ?>
                <img src="<?= e(media($v['main_image'])) ?>" alt="<?= e($v['brand'].' '.$v['model']) ?>" class="w-full h-full object-cover group-hover:scale-[1.04] transition duration-300">
              <?php else: ?>
                <div class="w-full h-full grid place-items-center text-slate-200"><i data-lucide="car" class="w-16 h-16"></i></div>
              <?php endif; ?>
            </a>
          </div>
          <div class="px-5 pb-5 pt-1 flex flex-col flex-1">
            <a href="<?= e($detail) ?>" class="font-display font-black tracking-[-.03em] text-ink text-[17px] leading-tight hover:text-brand transition"><?= e($v['brand'].' '.$v['model']) ?></a>
            <p class="text-[13px] text-slate-400 mt-0.5"><?= e($v['version'] ?: $v['year']) ?> · <?= e($v['year']) ?></p>
            <div class="flex items-center gap-3.5 mt-3 text-[12px] text-slate-500">
              <span class="flex items-center gap-1"><i data-lucide="users" class="w-3.5 h-3.5"></i><?= $v['passengers'] ?></span>
              <span class="flex items-center gap-1"><i data-lucide="cog" class="w-3.5 h-3.5"></i><?= $v['transmission']==='automatic'?'Auto':'Manual' ?></span>
              <span class="flex items-center gap-1"><i data-lucide="fuel" class="w-3.5 h-3.5"></i><?= ucfirst($v['fuel_type']) ?></span>
              <span class="flex items-center gap-1"><i data-lucide="briefcase" class="w-3.5 h-3.5"></i><?= $v['luggage_capacity'] ?></span>
            </div>
            <div class="flex items-center justify-between mt-4 pt-4 border-t hairline">
              <p class="text-[20px] font-black text-ink leading-none tnum"><?= money($v['daily_price']) ?><span class="text-[12px] font-medium text-slate-400">/dia</span></p>
              <a href="<?= e($reserve) ?>" class="inline-flex items-center gap-1.5 px-4 py-2 text-white text-[13px] font-bold hover:opacity-90 transition" style="background:var(--navy)">
                Reservar <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
              </a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>
  </form>
</section>

<!-- CTA -->
<section id="contacto" class="relative overflow-hidden bg-[#101620] text-white">
  <div class="absolute inset-0 opacity-70" style="background:radial-gradient(circle at 14% 20%, color-mix(in srgb,var(--brand) 42%, transparent), transparent 28%), radial-gradient(circle at 90% 80%, color-mix(in srgb,var(--brand2) 28%, transparent), transparent 28%);"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-16 lg:py-20">
    <div class="grid lg:grid-cols-[1fr_auto] gap-8 items-end">
      <div>
        <p class="text-[11px] font-black uppercase tracking-[.26em] text-white/55">Listo para tu viaje</p>
        <h2 class="mt-3 max-w-3xl font-display text-4xl lg:text-6xl font-black tracking-[-.055em] leading-[.95]">Reserva en minutos. Confirmamos disponibilidad por WhatsApp.</h2>
      </div>
      <div class="flex flex-wrap gap-3">
        <a href="#catalogo" class="inline-flex items-center justify-center gap-2 bg-white px-6 py-3.5 text-sm font-bold text-ink transition hover:bg-slate-100">Explorar catalogo <i data-lucide="arrow-up-right" class="h-4 w-4"></i></a>
        <?php if (!empty($tenant['whatsapp'])): ?>
        <a href="<?= e(whatsapp_link($tenant['whatsapp'])) ?>" target="_blank" class="inline-flex items-center justify-center gap-2 border border-white/18 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">Contactar asesor <i data-lucide="message-circle" class="h-4 w-4"></i></a>
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
    money(v){ return "'.addslashes(\App\Core\Config::get('app.currency_symbol','RD$')).' " + Math.round(v); },
    clamp(){ if(this.lo<this.min)this.lo=this.min; if(this.hi>this.max)this.hi=this.max; this.lo=Math.min(this.lo,this.hi); this.hi=Math.max(this.lo,this.hi); },
    pct(v){ return this.max === this.min ? 0 : ((v - this.min) / (this.max - this.min)) * 100; },
    fillStyle(){ return { left: this.pct(this.lo) + "%", width: (this.pct(this.hi) - this.pct(this.lo)) + "%" }; },
    barActive(i,n){ const span=(this.max-this.min)/n; const center=this.min + (i+0.5)*span; return center>=this.lo && center<=this.hi; }
  }
}
</script>'); ?>
