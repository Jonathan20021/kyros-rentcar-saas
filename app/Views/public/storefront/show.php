<?php
use App\Core\View;
echo View::renderPartial('public/storefront/_nav', ['tenant' => $tenant]);
$primary = $tenant['primary_color'];
$cover   = $tenant['cover_image'] ?? null;
$hMin = $histogram['min']; $hMax = $histogram['max']; $bars = $histogram['bars'];
$loVal = $filters['price_min'] ?: $hMin;
$hiVal = $filters['price_max'] ?: $hMax;
$maxBar = max(1, max($bars));
$qs = fn(array $extra=[]) => url('/r/'.$tenant['slug']).'?'.http_build_query(array_merge(array_filter([
  'start'=>$rangeStart,'end'=>$rangeEnd,
]), $extra));
?>

<!-- Brand band -->
<section class="relative overflow-hidden border-b hairline">
  <div class="absolute inset-0">
    <?php if ($cover): ?>
      <img src="<?= e(media($cover)) ?>" class="w-full h-full object-cover" alt=""><div class="absolute inset-0 bg-ink/55"></div>
    <?php else: ?>
      <div class="w-full h-full" style="background:linear-gradient(120deg,<?= e($primary) ?>,<?= e($tenant['secondary_color']) ?>)"></div>
      <div class="absolute inset-0 grid-bg opacity-[0.15]"></div>
    <?php endif; ?>
  </div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-12 lg:py-16 text-white">
    <div class="max-w-2xl" data-aos="fade-up">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur text-xs font-semibold mb-4">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Disponible para reservar
      </span>
      <h1 class="font-display text-3xl lg:text-[42px] font-extrabold leading-[1.05]"><?= e($tenant['name']) ?></h1>
      <p class="mt-3 text-white/85 text-base lg:text-lg max-w-xl"><?= e($tenant['description'] ?? 'Encuentra el vehiculo perfecto para tu proximo viaje.') ?></p>
      <div class="flex flex-wrap gap-5 mt-6 text-sm text-white/80">
        <span class="flex items-center gap-2"><i data-lucide="shield-check" class="w-4 h-4"></i> Seguro incluido</span>
        <span class="flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4"></i> Atencion 24/7</span>
        <span class="flex items-center gap-2"><i data-lucide="badge-check" class="w-4 h-4"></i> Reserva en linea</span>
      </div>
    </div>
  </div>
</section>

<!-- Browser -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-8 lg:py-10">
  <form method="GET" class="grid lg:grid-cols-[290px_1fr] gap-7"
        x-data='priceRange(<?= $hMin ?>, <?= $hMax ?>, <?= (int)$loVal ?>, <?= (int)$hiVal ?>)'>

    <!-- FILTER RAIL -->
    <aside class="lg:sticky lg:top-[88px] h-fit">
      <input type="hidden" name="start" value="<?= e($rangeStart) ?>">
      <input type="hidden" name="end" value="<?= e($rangeEnd) ?>">
      <input type="hidden" name="sort" value="<?= e($filters['sort']) ?>" x-ref="sortMirror">

      <div class="bg-white rounded-2xl border hairline shadow-card p-5">
        <div class="flex items-center justify-between">
          <h2 class="font-display font-bold text-ink">Filtrar</h2>
          <a href="<?= url('/r/'.$tenant['slug']) ?>" class="text-xs font-semibold text-slate-400 hover:text-brand">Limpiar todo</a>
        </div>

        <!-- Dates -->
        <div class="mt-5">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Fechas de renta</p>
          <div class="grid grid-cols-2 gap-2">
            <input type="date" name="start" value="<?= e($rangeStart) ?>" class="fld !py-2 !text-[13px]">
            <input type="date" name="end" value="<?= e($rangeEnd) ?>" class="fld !py-2 !text-[13px]">
          </div>
        </div>

        <!-- Price histogram -->
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
            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold text-ink" x-text="money(lo)"></span>
            <span class="text-slate-300">—</span>
            <span class="px-2.5 py-1 rounded-lg bg-slate-100 font-semibold text-ink" x-text="money(hi)"></span>
          </div>
        </div>

        <!-- Category -->
        <?php if (!empty($categories)): ?>
        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Categoria</p>
          <div class="space-y-1 max-h-44 overflow-y-auto pr-1">
            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer">
              <input type="radio" name="category_id" value="" <?= !$filters['category_id']?'checked':'' ?> class="text-brand focus:ring-brand/30">
              <span class="text-sm text-slate-600">Todas</span>
            </label>
            <?php foreach ($categories as $c): ?>
            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer">
              <input type="radio" name="category_id" value="<?= $c['id'] ?>" <?= $filters['category_id']==$c['id']?'checked':'' ?> class="text-brand focus:ring-brand/30">
              <span class="text-sm text-slate-600"><?= e($c['name']) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Transmission segmented -->
        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Transmision</p>
          <div class="seg w-full grid grid-cols-3">
            <?php foreach (['' =>'Todas','automatic'=>'Auto','manual'=>'Manual'] as $val=>$lbl): $id='tr_'.($val?:'all'); ?>
              <input type="radio" id="<?= $id ?>" name="transmission" value="<?= $val ?>" <?= $filters['transmission']===$val?'checked':'' ?>>
              <label for="<?= $id ?>" class="text-center justify-center flex"><?= $lbl ?></label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Fuel checkboxes (single-select radios for simplicity) -->
        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Combustible</p>
          <div class="grid grid-cols-2 gap-1">
            <?php foreach (['' =>'Todos','gasoline'=>'Gasolina','diesel'=>'Diesel','electric'=>'Electrico','hybrid'=>'Hibrido'] as $val=>$lbl): $id='fu_'.($val?:'all'); ?>
            <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer">
              <input type="radio" id="<?= $id ?>" name="fuel_type" value="<?= $val ?>" <?= $filters['fuel_type']===$val?'checked':'' ?> class="text-brand focus:ring-brand/30">
              <span class="text-sm text-slate-600"><?= $lbl ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Passengers -->
        <div class="mt-6">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Pasajeros (min)</p>
          <div class="flex gap-1.5">
            <?php foreach (['' =>'Todos',2=>'2',4=>'4',5=>'5',7=>'7+'] as $val=>$lbl): ?>
              <label class="flex-1">
                <input type="radio" name="passengers" value="<?= $val ?>" <?= (string)$filters['passengers']===(string)$val?'checked':'' ?> class="peer sr-only">
                <span class="block text-center text-sm py-1.5 rounded-lg border hairline cursor-pointer peer-checked:bg-ink peer-checked:text-white peer-checked:border-ink"><?= $lbl ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="w-full mt-6 py-2.5 rounded-xl text-white font-semibold shadow-card hover:opacity-90 transition" style="background:<?= e($primary) ?>">Aplicar filtros</button>
      </div>
    </aside>

    <!-- RESULTS -->
    <section>
      <div class="flex items-center justify-between gap-3 mb-5">
        <div>
          <h2 class="font-display text-2xl font-extrabold text-ink"><?= count($vehicles) ?> vehiculos</h2>
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
        <div class="bg-white rounded-2xl border hairline p-16 text-center">
          <div class="w-14 h-14 rounded-2xl bg-slate-100 grid place-items-center mx-auto"><i data-lucide="search-x" class="w-7 h-7 text-slate-400"></i></div>
          <h3 class="font-semibold text-ink mt-4">Sin resultados</h3>
          <p class="text-sm text-slate-400 mt-1">Ajusta los filtros para ver mas vehiculos.</p>
          <a href="<?= url('/r/'.$tenant['slug']) ?>" class="inline-block mt-4 text-sm font-semibold text-brand hover:underline">Limpiar filtros</a>
        </div>
      <?php else: ?>
      <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php foreach ($vehicles as $i => $v):
          $unavailable = isset($v['available_in_range']) && !$v['available_in_range'];
          $detail = url('/r/'.$tenant['slug'].'/vehiculo/'.$v['slug']);
          $reserve = url('/r/'.$tenant['slug'].'/reservar/'.$v['slug'].($rangeStart?'?start='.urlencode($rangeStart).'&end='.urlencode($rangeEnd):'')); ?>
        <article class="group bg-white rounded-2xl border hairline shadow-card hover:shadow-lift hover:-translate-y-0.5 transition-all duration-200 overflow-hidden flex flex-col <?= $unavailable?'opacity-60':'' ?>"
                 data-aos="fade-up" data-aos-delay="<?= ($i%3)*60 ?>">
          <div class="relative">
            <div class="flex items-center justify-between px-4 pt-4">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-[12px] font-semibold text-slate-600">
                <i data-lucide="car-front" class="w-3.5 h-3.5"></i><?= e($v['category_name'] ?? 'Vehiculo') ?>
              </span>
              <?php if ($unavailable): ?>
                <span class="px-2.5 py-1 rounded-full bg-red-50 text-red-600 text-[12px] font-semibold">No disponible</span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[12px] font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Disponible</span>
              <?php endif; ?>
            </div>
            <a href="<?= $detail ?>" class="block aspect-[16/10] grid place-items-center px-3 py-1">
              <?php if (!empty($v['main_image'])): ?>
                <img src="<?= e(media($v['main_image'])) ?>" alt="<?= e($v['brand'].' '.$v['model']) ?>" class="w-full h-full object-cover group-hover:scale-[1.04] transition duration-300">
              <?php else: ?>
                <div class="w-full h-full grid place-items-center text-slate-200"><i data-lucide="car" class="w-16 h-16"></i></div>
              <?php endif; ?>
            </a>
          </div>
          <div class="px-5 pb-5 pt-1 flex flex-col flex-1">
            <a href="<?= $detail ?>" class="font-display font-bold text-ink text-[17px] leading-tight hover:text-brand transition"><?= e($v['brand'].' '.$v['model']) ?></a>
            <p class="text-[13px] text-slate-400 mt-0.5"><?= e($v['version'] ?: $v['year']) ?> · <?= e($v['year']) ?></p>
            <div class="flex items-center gap-3.5 mt-3 text-[12px] text-slate-500">
              <span class="flex items-center gap-1"><i data-lucide="users" class="w-3.5 h-3.5"></i><?= $v['passengers'] ?></span>
              <span class="flex items-center gap-1"><i data-lucide="cog" class="w-3.5 h-3.5"></i><?= $v['transmission']==='automatic'?'Auto':'Manual' ?></span>
              <span class="flex items-center gap-1"><i data-lucide="fuel" class="w-3.5 h-3.5"></i><?= ucfirst($v['fuel_type']) ?></span>
              <span class="flex items-center gap-1"><i data-lucide="briefcase" class="w-3.5 h-3.5"></i><?= $v['luggage_capacity'] ?></span>
            </div>
            <div class="flex items-center justify-between mt-4 pt-4 border-t hairline">
              <p class="text-[20px] font-extrabold text-ink leading-none tnum"><?= money($v['daily_price']) ?><span class="text-[12px] font-medium text-slate-400">/día</span></p>
              <a href="<?= $reserve ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white text-[13px] font-semibold hover:opacity-90 transition" style="background:var(--navy)">
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

<!-- Benefits strip -->
<section class="bg-paper border-y hairline py-14">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <?php foreach ([['shield-check','Seguro incluido','Cobertura en toda la flotilla'],['headset','Soporte 24/7','Te asistimos a cualquier hora'],['credit-card','Pago flexible','Efectivo, tarjeta o transferencia'],['map-pin','Entrega flexible','Recibe el vehiculo donde quieras']] as $b): ?>
    <div class="bg-white rounded-2xl border hairline p-5 reveal">
      <div class="w-11 h-11 rounded-xl grid place-items-center text-white" style="background:<?= e($primary) ?>"><i data-lucide="<?= $b[0] ?>" class="w-5 h-5"></i></div>
      <h3 class="font-semibold text-ink mt-4"><?= e($b[1]) ?></h3>
      <p class="text-sm text-slate-400 mt-1"><?= e($b[2]) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- FAQ -->
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-16" x-data="{open:0}">
  <h2 class="font-display text-2xl font-extrabold text-center text-ink mb-8">Preguntas frecuentes</h2>
  <?php foreach ([
    ['¿Que necesito para alquilar?','Licencia vigente, documento de identidad y un metodo de pago. Edad minima 21 anos.'],
    ['¿Incluye seguro?','Si, todos los vehiculos incluyen seguro basico. Puedes agregar cobertura adicional.'],
    ['¿Como confirmo mi reserva?','Tras enviar la solicitud te contactamos por WhatsApp para confirmar disponibilidad y pago.'],
  ] as $i=>$f): ?>
  <div class="border-b hairline py-4">
    <button type="button" @click="open===<?= $i ?> ? open=null : open=<?= $i ?>" class="w-full flex items-center justify-between text-left">
      <span class="font-medium text-ink"><?= e($f[0]) ?></span>
      <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform" :class="open===<?= $i ?>?'rotate-180':''"></i>
    </button>
    <div x-show="open===<?= $i ?>" x-collapse x-cloak class="text-sm text-slate-500 mt-2"><?= e($f[1]) ?></div>
  </div>
  <?php endforeach; ?>
</section>

<?php echo View::renderPartial('public/storefront/_footer', ['tenant' => $tenant]); ?>

<?php View::push('scripts', '<script>
function priceRange(min,max,lo,hi){
  return {
    min, max, lo: Math.max(min, lo||min), hi: Math.min(max, hi||max),
    money(v){ return "'.addslashes(\App\Core\Config::get('app.currency_symbol','RD$')).' " + Math.round(v); },
    clamp(){ if(this.lo<this.min)this.lo=this.min; if(this.hi>this.max)this.hi=this.max; this.lo=Math.min(this.lo,this.hi); this.hi=Math.max(this.lo,this.hi); },
    pct(v){ return ((v - this.min) / (this.max - this.min)) * 100; },
    fillStyle(){ return { left: this.pct(this.lo) + "%", width: (this.pct(this.hi) - this.pct(this.lo)) + "%" }; },
    barActive(i,n){ const span=(this.max-this.min)/n; const center=this.min + (i+0.5)*span; return center>=this.lo && center<=this.hi; }
  }
}
</script>'); ?>
