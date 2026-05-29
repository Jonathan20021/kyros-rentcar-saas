<?php
use App\Core\View;
$demoOffers = $demoOffers ?? [];
?>
<style>
  /* ========================================================
     Landing — "Kyros Apex" edition (premium dark + neon brand)
     ======================================================== */
  @keyframes kShimmer { 0%{background-position:0% 50%} 100%{background-position:200% 50%} }
  @keyframes kFloat   { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-14px)} }
  @keyframes kSpin    { to{ transform:rotate(360deg) } }
  @keyframes kPulse   { 0%,100%{opacity:.5} 50%{opacity:1} }
  @keyframes kRise    { from{ opacity:0; transform:translateY(28px) } to{ opacity:1; transform:none } }

  .text-grad-anim {
    background:linear-gradient(110deg,var(--brand) 0%, #FF8A9B 25%, #ffffff 50%, #FF8A9B 75%, var(--brand) 100%);
    background-size:220% auto; -webkit-background-clip:text; background-clip:text; color:transparent;
    animation:kShimmer 6s linear infinite;
  }
  .orb-a{ width:34rem; height:34rem; background:radial-gradient(circle, color-mix(in srgb,var(--brand) 60%,transparent), transparent 65%); top:-10rem; left:-8rem; animation:kFloat 18s ease-in-out infinite; }
  .orb-b{ width:30rem; height:30rem; background:radial-gradient(circle, color-mix(in srgb,#6366F1 50%,transparent), transparent 65%); top:6rem; right:-9rem; animation:kFloat 22s ease-in-out infinite reverse; }
  .orb-c{ width:24rem; height:24rem; background:radial-gradient(circle, color-mix(in srgb,#10B981 38%,transparent), transparent 65%); bottom:-6rem; left:30%; animation:kFloat 26s ease-in-out infinite; }

  /* Magnetic CTA */
  .magnetic{ position:relative; overflow:hidden; }
  .magnetic::after{ content:""; position:absolute; inset:0; background:linear-gradient(120deg,transparent 30%, rgba(255,255,255,.35) 50%, transparent 70%); transform:translateX(-100%); transition:none; }
  .magnetic:hover::after{ transform:translateX(100%); transition:transform .9s ease; }

  /* Grid pattern */
  .dot-grid{ background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.08) 1px, transparent 0); background-size:24px 24px; mask-image:radial-gradient(60% 50% at 50% 30%, #000, transparent); -webkit-mask-image:radial-gradient(60% 50% at 50% 30%, #000, transparent); }

  /* Bento card */
  .bento { position:relative; border-radius:1.5rem; background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.08); transition:border-color .3s ease, transform .3s ease; overflow:hidden; }
  .bento:hover{ border-color:rgba(255,255,255,.18); transform:translateY(-2px); }
  .bento::before{ content:""; position:absolute; inset:0; background:radial-gradient(800px circle at var(--mx,50%) var(--my,50%), color-mix(in srgb, var(--brand) 18%, transparent), transparent 40%); opacity:0; transition:opacity .35s ease; pointer-events:none; }
  .bento:hover::before{ opacity:1; }

  /* Progress bar in showcase */
  .kbar{ height:3px; border-radius:99px; background:rgba(255,255,255,.08); overflow:hidden; }
  .kbar > i{ display:block; height:100%; background:var(--brand); width:0; transition:width .12s linear; }

  /* Animated number ticker */
  .ticker{ font-variant-numeric:tabular-nums; }

  /* Pricing pop */
  .plan-card{ transition:transform .35s cubic-bezier(.2,1,.3,1), box-shadow .35s ease, border-color .25s ease; }
  .plan-card:hover{ transform:translateY(-4px); }
  .plan-popular{ box-shadow:0 30px 60px -28px color-mix(in srgb,var(--brand) 50%, transparent); }

  /* Smooth section transitions */
  @media (prefers-reduced-motion:reduce){ *{ animation:none !important; transition:none !important } }

  /* Soft underline for nav links */
  .nav-link{ position:relative; }
  .nav-link::after{ content:""; position:absolute; left:0; bottom:-4px; width:0; height:2px; background:var(--brand); transition:width .3s ease; }
  .nav-link:hover::after{ width:100%; }

  /* Floating notification cards */
  .ping-dot::before{ content:""; position:absolute; inset:-4px; border-radius:50%; background:var(--brand); opacity:.5; animation:kPulse 2s ease-in-out infinite; }
</style>

<!-- =================================================================
     HERO
     ================================================================= -->
<section class="mesh-dark relative overflow-hidden pt-28 pb-32">
  <div class="aurora"></div>
  <div class="orb absolute orb-a"></div>
  <div class="orb absolute orb-b"></div>
  <div class="orb absolute orb-c"></div>
  <div class="absolute inset-0 dot-grid"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-4xl mx-auto">
      <a href="#showcase" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-white/10 bg-white/[0.04] backdrop-blur text-[12.5px] font-medium text-white/70 mb-8 hover:border-white/20 hover:bg-white/[0.07] transition-all duration-300 reveal">
        <span class="relative w-1.5 h-1.5"><span class="absolute inset-0 rounded-full bg-brand ping-dot"></span></span>
        <span>v1.0 · Multi-tenant · API REST</span>
        <i data-lucide="arrow-right" class="w-3.5 h-3.5 opacity-60"></i>
      </a>

      <h1 class="font-display text-[44px] sm:text-[68px] lg:text-[84px] font-extrabold leading-[1.02] tracking-tight reveal">
        El sistema operativo<br>
        de tu <span class="text-grad-anim">rent car</span>
      </h1>
      <p class="mt-7 text-lg sm:text-xl text-white/55 max-w-2xl mx-auto leading-relaxed reveal">
        Flotilla, reservas, contratos, pagos y tu página pública de alquiler.
        Una sola plataforma — veloz, segura y hecha para vender.
      </p>

      <div class="flex flex-col sm:flex-row gap-3 justify-center mt-10 reveal">
        <a href="<?= url('/register') ?>" class="k-btn k-btn-grad magnetic px-7 text-[15px] h-13 !rounded-2xl" style="height:52px">
          Crear mi rent car gratis <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
        <a href="<?= url('/login') ?>" class="k-btn k-btn-glass px-7 text-[15px] !rounded-2xl" style="height:52px">
          <i data-lucide="play" class="w-4 h-4"></i> Probar demo · 5h
        </a>
      </div>

      <p class="mt-5 text-[12.5px] text-white/35 flex items-center justify-center gap-2 flex-wrap reveal">
        <span class="inline-flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-400/70"></i>Sin tarjeta de crédito</span>
        <span class="text-white/15">·</span>
        <span class="inline-flex items-center gap-1.5"><i data-lucide="zap" class="w-3.5 h-3.5 text-amber-400/70"></i>Listo en 3 minutos</span>
        <span class="text-white/15">·</span>
        <span class="inline-flex items-center gap-1.5"><i data-lucide="shield" class="w-3.5 h-3.5 text-indigo-400/70"></i>Multi-tenant seguro</span>
      </p>
    </div>

    <!-- Live dashboard mockup -->
    <div class="relative max-w-5xl mx-auto mt-20" x-data="{px:0, py:0}" @mousemove.window="if($event.target.closest('#heroShot')){let r=$event.target.closest('#heroShot').getBoundingClientRect(); px=(($event.clientX-r.left)/r.width-.5)*8; py=(($event.clientY-r.top)/r.height-.5)*8;}">
      <div class="absolute -inset-x-10 -top-10 h-44 grad-bg opacity-25 blur-3xl rounded-full"></div>

      <!-- Floating notification: Nueva reserva -->
      <div class="hidden lg:flex absolute -left-12 top-24 z-20 glass rounded-2xl p-3.5 items-center gap-3 shadow-lift reveal-s" style="animation:kFloat 7s ease-in-out infinite">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 grid place-items-center"><i data-lucide="calendar-check" class="w-5 h-5"></i></div>
        <div>
          <p class="text-[13px] font-semibold text-white">Nueva reserva</p>
          <p class="text-[11px] text-white/50">Honda Civic · 3 días</p>
        </div>
        <span class="text-[10px] text-emerald-400 font-bold">+</span>
      </div>

      <!-- Floating notification: Pago recibido -->
      <div class="hidden lg:block absolute -right-10 top-44 z-20 glass rounded-2xl p-4 shadow-lift reveal-s" style="animation:kFloat 9s ease-in-out infinite reverse; animation-delay:.4s">
        <p class="text-[11px] text-white/50">Pago recibido · Tarjeta</p>
        <p class="text-xl font-extrabold ticker text-white mt-0.5">RD$ 18,880</p>
        <p class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1 mt-0.5">
          <i data-lucide="trending-up" class="w-3 h-3"></i> Ingresos del día: RD$ 64k
        </p>
      </div>

      <!-- Floating KPI badge bottom-left -->
      <div class="hidden lg:flex absolute -left-8 bottom-12 z-20 glass rounded-2xl p-3.5 items-center gap-3 shadow-lift reveal-s" style="animation:kFloat 11s ease-in-out infinite">
        <div class="w-10 h-10 rounded-xl bg-brand/20 text-brand grid place-items-center"><i data-lucide="car" class="w-5 h-5"></i></div>
        <div>
          <p class="text-[13px] font-semibold text-white">Flotilla activa</p>
          <p class="text-[11px] text-white/50">23 / 30 vehículos</p>
        </div>
      </div>

      <div id="heroShot" class="relative will-change-transform reveal-s"
           :style="'transform: perspective(1600px) rotateX(' + (8 - py) + 'deg) rotateY(' + px + 'deg)'">
        <div class="rounded-3xl overflow-hidden border border-white/10 bg-[#0E1422] shadow-lift">
          <!-- Window chrome -->
          <div class="h-10 flex items-center gap-1.5 px-4 border-b border-white/[0.06] bg-white/[0.02]">
            <span class="w-2.5 h-2.5 rounded-full bg-white/15"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-white/15"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-white/15"></span>
            <span class="mx-auto text-[11px] text-white/30 tnum">app.kyros.com/admin/dashboard</span>
          </div>
          <div class="grid grid-cols-[220px_1fr]">
            <!-- sidebar -->
            <div class="border-r border-white/[0.06] p-4 space-y-1 hidden sm:block bg-white/[0.01]">
              <div class="flex items-center gap-2 mb-3 px-2">
                <div class="w-6 h-6 rounded-md grad-bg"></div>
                <div class="h-2.5 w-14 rounded bg-white/15"></div>
              </div>
              <?php foreach ([
                ['Dashboard',true],['Reservas',false],['Flotilla',false],['Clientes',false],
                ['Contratos',false],['Pagos',false],['Reportes',false],['Configuración',false]
              ] as $row): [$lbl,$on] = $row; ?>
              <div class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg <?= $on ? 'bg-brand/15' : '' ?>">
                <div class="w-3.5 h-3.5 rounded <?= $on ? 'bg-brand' : 'bg-white/15' ?>"></div>
                <div class="h-2 w-16 rounded <?= $on ? 'bg-brand/60' : 'bg-white/10' ?>"></div>
              </div>
              <?php endforeach; ?>
            </div>
            <!-- main -->
            <div class="p-5">
              <div class="flex items-center justify-between mb-4">
                <div class="h-3 w-32 rounded bg-white/15"></div>
                <div class="flex gap-2"><div class="h-7 w-7 rounded-lg bg-white/[0.07]"></div><div class="h-7 w-20 rounded-lg grad-bg"></div></div>
              </div>
              <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                <?php for ($i = 0; $i < 4; $i++):
                  $tints = ['bg-brand/15 text-brand','bg-indigo-500/15 text-indigo-300','bg-emerald-500/15 text-emerald-300','bg-amber-500/15 text-amber-300'];
                  $tint = $tints[$i];
                  $values = ['184k','42','12','98%'];
                  $labels = ['Ingresos','Reservas','Rentados','Ocupación'];
                ?>
                <div class="rounded-xl border border-white/[0.07] p-3 bg-white/[0.02]">
                  <div class="flex items-center gap-2 mb-1.5">
                    <div class="w-6 h-6 rounded-md <?= $tint ?> grid place-items-center text-[10px] font-bold">★</div>
                    <div class="h-2 w-10 rounded bg-white/15"></div>
                  </div>
                  <div class="text-white font-bold text-base ticker"><?= $values[$i] ?></div>
                  <div class="h-2 w-14 rounded bg-white/10 mt-1.5"></div>
                </div>
                <?php endfor; ?>
              </div>
              <div class="rounded-xl border border-white/[0.07] p-4 h-40 flex items-end gap-1.5 bg-gradient-to-b from-white/[0.02] to-transparent">
                <?php foreach ([42, 58, 48, 72, 55, 82, 66, 90, 68, 96, 80, 64, 86, 72, 88, 95] as $i => $h):
                  $isPeak = $h >= 95;
                ?>
                <div class="flex-1 rounded-t transition-all duration-500" style="height:<?= $h ?>%; background: <?= $isPeak ? 'linear-gradient(180deg, #F23645, #FF5C72)' : 'rgba(255,255,255,.12)' ?>; box-shadow: <?= $isPeak ? '0 0 24px rgba(242,54,69,.4)' : 'none' ?>"></div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- =================================================================
     LIVE METRICS BAND
     ================================================================= -->
<section class="relative overflow-hidden" style="background:var(--grad)">
  <div class="absolute inset-0 grid-dark opacity-25"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-14 grid grid-cols-2 md:grid-cols-4 gap-6 text-white text-center">
    <?php
    $metrics = [
      ['1240','+','Reservas gestionadas'],
      ['98','%','Uptime de plataforma'],
      ['4.9','','Calificación promedio'],
      ['3',' min','Para publicar tu rent car'],
    ];
    foreach ($metrics as $m): ?>
    <div class="reveal">
      <p class="font-display text-4xl sm:text-5xl font-extrabold ticker" data-count="<?= $m[0] ?>" data-suf="<?= $m[1] ?>" data-dec="<?= strpos($m[0],'.')!==false?'1':'0' ?>">0</p>
      <p class="text-[13px] text-white/85 mt-1.5 font-medium"><?= e($m[2]) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- =================================================================
     TRUSTED BY (marquee)
     ================================================================= -->
<section class="border-b border-white/[0.06] py-7 overflow-hidden bg-[#0E1422]">
  <p class="text-center text-[11px] uppercase tracking-[0.18em] text-white/30 mb-4 font-bold">Lo que cubre Kyros</p>
  <div class="flex overflow-hidden [mask-image:linear-gradient(90deg,transparent,#000_8%,#000_92%,transparent)]">
    <div class="marquee shrink-0 text-white/30 text-[13px] font-medium items-center">
      <?php $items = ['Multi-tenant seguro','Reservas sin doble booking','Contratos con firma digital','Pagos y facturas formales','Control de gastos','Cierre de caja diario','Multi-sucursal','Estado de resultados (P&L)','Página pública con slug propio','API REST','Choferes y promociones','Bitácora de actividad','Cuadrante de mantenimiento','Reportes en vivo'];
      for ($r = 0; $r < 2; $r++) foreach ($items as $it): ?>
        <span class="flex items-center gap-2 whitespace-nowrap">
          <span class="w-1 h-1 rounded-full bg-brand"></span><?= e($it) ?>
        </span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =================================================================
     INTERACTIVE SHOWCASE (auto-rotating tabs)
     ================================================================= -->
<section id="showcase" class="bg-[#0E1422] py-28"
         x-data="{ t:0, p:0, paused:false, go(i){ this.t=i; this.p=0; }, tick(){ if(this.paused) return; this.p+=1.7; if(this.p>=100){ this.p=0; this.t=(this.t+1)%4; } } }"
         x-init="setInterval(()=>tick(),100)" @mouseenter="paused=true" @mouseleave="paused=false">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <p class="text-[12.5px] font-bold tracking-[0.18em] uppercase text-brand mb-3">Recorre el producto</p>
      <h2 class="font-display text-3xl sm:text-[48px] font-extrabold leading-[1.05] tracking-tight">Todo lo que necesitas,<br>en un solo clic</h2>
    </div>
    <div class="grid lg:grid-cols-[400px_1fr] gap-6 lg:gap-10">
      <!-- tabs -->
      <div class="space-y-3">
        <?php
        $tabs = [
          ['calendar-check','Reservas inteligentes','Recibe reservas online y créalas internas con disponibilidad en tiempo real. Calendario, estados, conversión a contrato.'],
          ['car','Flotilla controlada','Vehículos con fotos, vencimientos de documentos, mantenimiento, estados y multi-sucursal.'],
          ['file-text','Contratos con firma','Genera contratos con firma digital, fotos de entrega/devolución, cierre con penalidades y PDF listo.'],
          ['bar-chart-3','Finanzas en vivo','Pagos, facturas, gastos, cierre de caja diario y reportes con P&L mensual. Todo conectado.'],
        ];
        foreach ($tabs as $i => $tb): ?>
        <button type="button" @click="go(<?= $i ?>)"
                :class="t===<?= $i ?>?'bg-white/[0.06] border-white/15':'border-transparent hover:bg-white/[0.03] hover:border-white/[0.06]'"
                class="w-full text-left p-5 rounded-2xl border transition-all duration-300 reveal">
          <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-xl grid place-items-center shrink-0 transition-all"
                 :class="t===<?= $i ?>?'grad-bg text-white scale-105':'bg-white/5 text-white/50'">
              <i data-lucide="<?= $tb[0] ?>" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-display font-bold text-[15.5px] text-white"><?= e($tb[1]) ?></p>
              <p class="text-[13px] text-white/55 mt-1 leading-relaxed"><?= e($tb[2]) ?></p>
            </div>
          </div>
          <div class="kbar mt-3.5" x-show="t===<?= $i ?>" x-cloak><i :style="'width:'+p+'%'"></i></div>
        </button>
        <?php endforeach; ?>
      </div>
      <!-- panel -->
      <div class="surface rounded-3xl p-6 lg:p-10 relative overflow-hidden reveal-s min-h-[420px]">
        <div class="absolute -top-12 -right-12 w-56 h-56 grad-bg opacity-15 blur-3xl rounded-full"></div>
        <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-indigo-500/20 blur-3xl rounded-full"></div>

        <!-- Reservas -->
        <div x-show="t===0" x-transition.opacity.duration.300ms class="relative">
          <div class="flex items-center justify-between mb-5">
            <p class="font-display font-bold text-lg">Reservas de la semana</p>
            <span class="text-[11px] px-2.5 py-1 rounded-lg bg-white/5 text-white/55 font-medium">7 días</span>
          </div>
          <div class="space-y-2.5">
            <?php foreach ([
              ['Honda Civic 2022','RSV-0042','Confirmada','bg-emerald-500/15 text-emerald-400','15 Jun → 18 Jun','RD$ 7,788'],
              ['Tesla Model 3','RSV-0041','Pendiente',  'bg-amber-500/15 text-amber-400','12 Jun → 14 Jun','RD$ 12,400'],
              ['Mercedes C300', 'RSV-0040','En proceso', 'bg-indigo-500/15 text-indigo-300','10 Jun → 16 Jun','RD$ 51,000'],
              ['Toyota Corolla','RSV-0039','Confirmada','bg-emerald-500/15 text-emerald-400','08 Jun → 11 Jun','RD$ 6,600'],
            ] as $r): ?>
            <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.05] transition">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-lg bg-white/5 grid place-items-center shrink-0"><i data-lucide="car" class="w-4.5 h-4.5 text-white/50"></i></div>
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-white truncate"><?= e($r[0]) ?></p>
                  <p class="text-[11px] text-white/45 tnum"><?= e($r[1]) ?> · <?= e($r[4]) ?></p>
                </div>
              </div>
              <div class="flex items-center gap-3 shrink-0">
                <span class="text-xs px-2.5 py-1 rounded-full <?= $r[3] ?> font-semibold"><?= $r[2] ?></span>
                <span class="text-sm font-bold text-white tnum hidden sm:inline"><?= $r[5] ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Flotilla -->
        <div x-show="t===1" x-cloak x-transition.opacity.duration.300ms class="relative">
          <div class="flex items-center justify-between mb-5">
            <p class="font-display font-bold text-lg">Flotilla en tiempo real</p>
            <span class="text-[11px] px-2.5 py-1 rounded-lg bg-white/5 text-white/55 font-medium">23 vehículos</span>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <?php
            $states = ['Disponible','Rentado','Mantenimiento','Disponible','Limpieza','Disponible'];
            $cars = ['Toyota Corolla','Honda Civic','Hyundai Tucson','Kia Picanto','Mercedes C300','Tesla Model 3'];
            foreach ($states as $i => $st):
              $cm = ['Disponible'=>['#10B981','bg-emerald-500/10'],'Rentado'=>['#6366F1','bg-indigo-500/10'],'Mantenimiento'=>['#F59E0B','bg-amber-500/10'],'Limpieza'=>['#06B6D4','bg-cyan-500/10']][$st];
            ?>
            <div class="rounded-xl bg-white/[0.03] border border-white/[0.06] p-4 hover:bg-white/[0.05] transition group">
              <div class="h-20 rounded-lg <?= $cm[1] ?> mb-3 grid place-items-center group-hover:scale-105 transition-transform"><i data-lucide="car-front" class="w-8 h-8" style="color:<?= $cm[0] ?>"></i></div>
              <p class="text-[12.5px] font-semibold text-white truncate"><?= e($cars[$i]) ?></p>
              <p class="text-[11px] mt-0.5" style="color:<?= $cm[0] ?>"><?= $st ?></p>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Contratos -->
        <div x-show="t===2" x-cloak x-transition.opacity.duration.300ms class="relative">
          <div class="rounded-2xl bg-white/[0.03] border border-white/[0.06] p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-[11px] text-white/45 uppercase tracking-wide font-bold">Contrato</p>
                <p class="font-display font-extrabold text-white text-xl tnum mt-0.5">CTR-2026-0042</p>
              </div>
              <span class="text-xs px-3 py-1.5 rounded-full bg-emerald-500/15 text-emerald-400 font-semibold">Activo</span>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-6 text-sm">
              <div><p class="text-white/40 text-xs">Cliente</p><p class="font-semibold text-white mt-0.5">Pedro Martínez</p></div>
              <div><p class="text-white/40 text-xs">Vehículo</p><p class="font-semibold text-white mt-0.5">Honda Civic 2022</p></div>
              <div><p class="text-white/40 text-xs">Total</p><p class="font-bold text-white tnum mt-0.5">RD$ 18,880</p></div>
              <div><p class="text-white/40 text-xs">Balance</p><p class="font-bold text-brand tnum mt-0.5">RD$ 0.00</p></div>
            </div>
            <div class="mt-6 pt-5 border-t border-white/[0.06] flex items-center gap-3">
              <div class="flex-1 h-14 rounded-xl bg-white/[0.04] grid place-items-center text-white/40 text-xs font-medium border border-dashed border-white/[0.08]">
                <i data-lucide="pen-line" class="w-4 h-4 inline mr-1.5"></i> Firma digital del cliente
              </div>
              <span class="text-xs px-3 py-1.5 rounded-full bg-white/5 text-white/55 font-medium">PDF listo</span>
            </div>
          </div>
        </div>

        <!-- Finanzas -->
        <div x-show="t===3" x-cloak x-transition.opacity.duration.300ms class="relative">
          <div class="rounded-2xl grad-bg p-6 text-white">
            <p class="text-xs text-white/85 font-medium">Ingresos del mes</p>
            <p class="text-4xl font-extrabold ticker mt-1">RD$ 184,500</p>
            <p class="text-xs text-white/85 mt-1 flex items-center gap-1"><i data-lucide="trending-up" class="w-3 h-3"></i> +24% vs. mes anterior</p>
          </div>
          <div class="grid grid-cols-2 gap-3 mt-4">
            <div class="rounded-xl bg-white/[0.03] border border-white/[0.06] p-4">
              <p class="text-[11px] text-white/45">Gastos del mes</p>
              <p class="text-lg font-bold text-white tnum mt-1">RD$ 42,180</p>
            </div>
            <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-4">
              <p class="text-[11px] text-emerald-300">Margen neto</p>
              <p class="text-lg font-bold text-emerald-300 tnum mt-1">RD$ 142,320</p>
            </div>
          </div>
          <div class="space-y-2 mt-3">
            <?php foreach ([['PAY-0099','RD$ 10,000','Tarjeta','#6366F1'],['PAY-0098','RD$ 6,372','Efectivo','#10B981'],['PAY-0097','RD$ 5,000','Transferencia','#F59E0B']] as $p): ?>
            <div class="flex items-center justify-between p-3 rounded-xl bg-white/[0.03] border border-white/[0.06]">
              <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full" style="background:<?= $p[3] ?>"></span>
                <p class="text-sm font-medium text-white tnum"><?= $p[0] ?></p>
                <span class="text-[11px] text-white/45"><?= $p[2] ?></span>
              </div>
              <span class="font-bold text-emerald-400 tnum"><?= $p[1] ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- =================================================================
     BENTO FEATURES
     ================================================================= -->
<section id="features" class="bg-[#0E1422] pb-28">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <p class="text-[12.5px] font-bold tracking-[0.18em] uppercase text-brand mb-3">Todo en uno</p>
      <h2 class="font-display text-3xl sm:text-[48px] font-extrabold leading-[1.05] tracking-tight">Una plataforma para toda tu operación</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 auto-rows-[160px]">
      <!-- Storefront (large) -->
      <div class="bento md:col-span-4 md:row-span-2 p-7 lg:p-9 reveal-s flex flex-col justify-between"
           onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div class="relative z-10">
          <p class="text-[12px] font-bold uppercase tracking-wide text-brand">Tu marca, online</p>
          <h3 class="font-display text-2xl lg:text-3xl font-extrabold text-white mt-3">Página pública con tu propio slug</h3>
          <p class="text-white/55 mt-3 max-w-md leading-relaxed">Buscador, filtros, histograma de precios, galería de vehículos y reservas online — listo sin escribir una línea de código.</p>
          <div class="mt-6 inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-sm text-white/75 tnum">
            <i data-lucide="link-2" class="w-4 h-4 text-white/40"></i> kyros.com/r/tu-rentcar
          </div>
        </div>
        <div class="relative z-10 mt-6 grid grid-cols-4 gap-2.5">
          <?php for ($i = 0; $i < 4; $i++): ?>
          <div class="rounded-lg border border-white/[0.07] bg-white/[0.025] p-2.5 hover:border-white/[0.14] transition">
            <div class="h-14 rounded bg-white/[0.04] mb-2 grid place-items-center"><i data-lucide="car-front" class="w-6 h-6 text-white/15"></i></div>
            <div class="h-2 w-10 rounded bg-white/15"></div>
            <div class="h-2 w-7 rounded bg-brand/60 mt-1.5"></div>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Security -->
      <div class="bento md:col-span-2 p-6 reveal" onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div class="w-10 h-10 rounded-xl bg-indigo-500/15 text-indigo-300 grid place-items-center mb-3"><i data-lucide="shield-check" class="w-5 h-5"></i></div>
        <h3 class="font-display font-bold text-white text-lg">Seguridad real</h3>
        <p class="text-sm text-white/55 mt-1.5 leading-relaxed">Multi-tenant aislado, CSRF, prepared statements y roles.</p>
      </div>

      <!-- Dashboard -->
      <div class="bento md:col-span-2 p-6 reveal" onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-400 grid place-items-center mb-3"><i data-lucide="gauge" class="w-5 h-5"></i></div>
        <h3 class="font-display font-bold text-white text-lg">Dashboard en vivo</h3>
        <p class="text-sm text-white/55 mt-1.5 leading-relaxed">KPIs, ingresos, ocupación de flotilla y alertas al instante.</p>
      </div>

      <!-- API -->
      <div class="bento md:col-span-3 p-7 reveal-s flex flex-col justify-between"
           onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div>
          <p class="text-[12px] font-bold uppercase tracking-wide text-brand">Plan Premium</p>
          <h3 class="font-display text-xl font-extrabold text-white mt-2">API REST · Conecta con todo</h3>
        </div>
        <div class="font-mono text-[11px] leading-relaxed bg-[#0A0F1A] rounded-xl border border-white/[0.06] p-3.5 mt-3">
          <p class="text-white/40"><span class="text-emerald-400">GET</span> /api/v1/vehicles</p>
          <p class="text-white/30 mt-1">Authorization: Bearer kyro_***</p>
        </div>
      </div>

      <!-- Multi-currency / Locale -->
      <div class="bento md:col-span-3 p-7 reveal-s flex flex-col justify-between"
           onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div>
          <p class="text-[12px] font-bold uppercase tracking-wide text-brand">Hecho para LATAM</p>
          <h3 class="font-display text-xl font-extrabold text-white mt-2">Multi-sucursal, multi-moneda</h3>
          <p class="text-sm text-white/55 mt-2 leading-relaxed">DOP por defecto, ITBIS configurable, múltiples sucursales con stock independiente.</p>
        </div>
        <div class="flex items-center gap-2 mt-3">
          <?php foreach (['🇩🇴','🇲🇽','🇨🇴','🇵🇦','🇨🇷','🇵🇷'] as $flag): ?>
            <span class="text-xl"><?= $flag ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- =================================================================
     STEPS — How it works
     ================================================================= -->
<section class="bg-[#111A2B] border-y border-white/[0.06] py-28">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <p class="text-[12.5px] font-bold tracking-[0.18em] uppercase text-brand mb-3">Cómo funciona</p>
      <h2 class="font-display text-3xl sm:text-[48px] font-extrabold leading-tight">En línea en tres pasos</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-4 relative">
      <!-- connector line on desktop -->
      <div class="hidden md:block absolute top-12 left-[20%] right-[20%] h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
      <?php foreach ([
        ['Crea tu rent car','Registra tu empresa, elige colores y recibe tu página pública con slug propio.'],
        ['Carga tu flotilla','Vehículos, fotos, precios, categorías y disponibilidad — listo en minutos.'],
        ['Recibe reservas','Tus clientes reservan online, tú gestionas todo desde un solo panel.'],
      ] as $i => $s): ?>
      <div class="surface p-7 reveal relative z-10">
        <span class="w-12 h-12 rounded-2xl grad-bg grid place-items-center text-base font-extrabold text-white tnum shadow-lift mb-5"><?= str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) ?></span>
        <h3 class="font-display font-bold text-lg text-white"><?= e($s[0]) ?></h3>
        <p class="text-white/55 mt-2 text-sm leading-relaxed"><?= e($s[1]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =================================================================
     DEMO LICENSE CTA
     ================================================================= -->
<?php if (!empty($demoOffers)): ?>
<section class="bg-[#0E1422] py-20">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="surface rounded-3xl overflow-hidden relative reveal-s">
      <div class="absolute inset-0 grid-dark opacity-30"></div>
      <div class="relative p-8 lg:p-12 grid lg:grid-cols-[1fr_auto] gap-8 items-center">
        <div class="max-w-xl">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-brand/15 text-brand mb-3">
            <i data-lucide="zap" class="w-3 h-3"></i> Sin registro
          </span>
          <h2 class="font-display text-2xl sm:text-3xl font-extrabold text-white leading-tight">Pruébalo con un código demo de 5 horas</h2>
          <p class="text-white/55 mt-3 leading-relaxed">Cuenta nueva, datos seed cargados, todas las funciones del plan. Al expirar se borra automáticamente.</p>
          <a href="<?= url('/login#demo') ?>" class="k-btn k-btn-grad magnetic mt-6 px-6">
            Probar ahora <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
        </div>
        <div class="grid sm:grid-cols-3 lg:grid-cols-1 gap-2.5 w-full lg:w-72">
          <?php foreach ($demoOffers as $o): ?>
          <div class="glass rounded-xl p-3.5 hover:border-white/20 transition">
            <p class="text-[11px] text-white/45 uppercase tracking-wider font-bold"><?= e($o['plan_name']) ?></p>
            <p class="font-mono font-bold text-white text-sm mt-0.5 truncate"><?= e($o['code']) ?></p>
            <p class="text-[11px] text-white/55 mt-1"><?= (int)$o['hours_valid'] ?>h · <?= (int)$o['max_vehicles'] === -1 ? '∞' : (int)$o['max_vehicles'] ?> vehículos</p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- =================================================================
     ALL FEATURES CHECKLIST (3 columns)
     ================================================================= -->
<section class="bg-[#0E1422] py-28">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <p class="text-[12.5px] font-bold tracking-[0.18em] uppercase text-brand mb-3">Todo incluido</p>
      <h2 class="font-display text-3xl sm:text-[48px] font-extrabold leading-[1.05] tracking-tight">Desde la reserva al cierre de caja</h2>
      <p class="mt-4 text-white/50 leading-relaxed">Sin integraciones complicadas. Sin hojas de cálculo. Solo un sistema que crece contigo.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-4">
      <?php
      $groups = [
        ['gauge','Operación','#F23645',[
          'Reservas online y manuales','Sin doble booking','Contratos con firma digital','Fotos de entrega y devolución',
          'Flotilla con estados y fotos','Multi-sucursal','Mantenimiento e incidencias','Vencimientos de documentos',
        ]],
        ['wallet','Finanzas','#6366F1',[
          'Pagos y recibos','Facturación formal con PDF','Control de gastos operativos','Cierre de caja diario',
          'Estado de resultados (P&L)','Reportes por sucursal','Ingresos vs gastos','Exportación a CSV',
        ]],
        ['rocket','Crecimiento','#10B981',[
          'Página pública con tu slug','Servicios y promociones','Choferes (chauffeur)','Correos automáticos',
          'API REST para integraciones','Roles y permisos','Bitácora de actividad','Búsqueda global ⌘K',
        ]],
      ];
      foreach ($groups as $g): ?>
      <div class="surface p-7 reveal hover:border-white/15 transition-all duration-300">
        <div class="w-12 h-12 rounded-2xl grid place-items-center mb-5" style="background:color-mix(in srgb,<?= $g[2] ?> 20%,transparent); color:<?= $g[2] ?>">
          <i data-lucide="<?= $g[0] ?>" class="w-5 h-5"></i>
        </div>
        <h3 class="font-display font-bold text-xl text-white mb-5"><?= e($g[1]) ?></h3>
        <ul class="space-y-2.5">
          <?php foreach ($g[3] as $f): ?>
          <li class="flex items-start gap-2.5 text-[14px] text-white/65">
            <i data-lucide="check" class="w-4 h-4 mt-0.5 shrink-0" style="color:<?= $g[2] ?>"></i>
            <span><?= e($f) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =================================================================
     TESTIMONIALS
     ================================================================= -->
<section class="bg-[#0E1422] pt-12 pb-28">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-xl mx-auto mb-14 reveal">
      <p class="text-[12.5px] font-bold tracking-[0.18em] uppercase text-brand mb-3">Casos reales</p>
      <h2 class="font-display text-3xl sm:text-[44px] font-extrabold leading-tight">Lo que dicen las rent cars</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-4">
      <?php foreach ([
        ['Montamos nuestra rent car online en una tarde. La página pública nos trae reservas todos los días.','Carlos M.','Speed Rent Car · Santo Domingo','#F23645'],
        ['Los contratos con firma y las fotos de entrega nos ahorraron muchísimos problemas con clientes.','Ana R.','Luxury Drive RD · Santiago','#6366F1'],
        ['Por fin veo mis ingresos y mi flotilla en tiempo real. Cambió cómo administramos el negocio.','José P.','Caribe Cars · Punta Cana','#10B981'],
      ] as $tm): ?>
      <div class="surface p-7 reveal hover:border-white/15 transition-all duration-300">
        <div class="flex gap-0.5 text-amber-400 mb-3"><?php for ($i = 0; $i < 5; $i++): ?><i data-lucide="star" class="w-4 h-4 fill-amber-400"></i><?php endfor; ?></div>
        <p class="text-white/80 leading-relaxed">&ldquo;<?= e($tm[0]) ?>&rdquo;</p>
        <div class="flex items-center gap-3 mt-6 pt-5 border-t border-white/[0.06]">
          <div class="w-10 h-10 rounded-full grid place-items-center text-white text-xs font-bold" style="background:<?= $tm[3] ?>"><?= e(mb_substr($tm[1], 0, 1)) ?></div>
          <div><p class="text-sm font-semibold text-white"><?= e($tm[1]) ?></p><p class="text-[12px] text-white/45"><?= e($tm[2]) ?></p></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =================================================================
     PLANS
     ================================================================= -->
<section id="planes" class="bg-[#111A2B] border-y border-white/[0.06] py-28"
         x-data="{yearly:false}">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-10 reveal">
      <p class="text-[12.5px] font-bold tracking-[0.18em] uppercase text-brand mb-3">Precios</p>
      <h2 class="font-display text-3xl sm:text-[48px] font-extrabold leading-tight">Empieza gratis. Escala cuando quieras.</h2>
    </div>

    <!-- Billing cycle toggle -->
    <div class="flex justify-center mb-10 reveal">
      <div class="inline-flex p-1 rounded-2xl bg-white/[0.04] border border-white/[0.08]">
        <button @click="yearly=false" :class="!yearly?'bg-white text-navy shadow-sm':'text-white/60'" class="px-5 py-2 rounded-xl text-sm font-semibold transition">Mensual</button>
        <button @click="yearly=true" :class="yearly?'bg-white text-navy shadow-sm':'text-white/60'" class="px-5 py-2 rounded-xl text-sm font-semibold transition">Anual <span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">-17%</span></button>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4 max-w-5xl mx-auto">
      <?php foreach ($plans as $i => $p):
        $featured = $i === 1;
        $feats = $p['features'] ? (json_decode($p['features'], true) ?: []) : [];
      ?>
      <div class="plan-card relative rounded-3xl p-7 reveal-s <?= $featured ? 'bg-white text-navy plan-popular' : 'surface' ?>">
        <?php if ($featured): ?>
          <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase bg-brand text-white shadow-card">Más popular</span>
        <?php endif; ?>
        <h3 class="font-display font-bold <?= $featured ? 'text-navy' : 'text-white' ?> text-lg"><?= e($p['name']) ?></h3>
        <p class="mt-4 tnum">
          <span class="font-display text-[48px] font-extrabold <?= $featured ? 'text-navy' : 'text-white' ?>" x-text="'<?= money($p['price_monthly']) ?>'.toString()" x-show="!yearly"></span>
          <span class="font-display text-[48px] font-extrabold <?= $featured ? 'text-navy' : 'text-white' ?>" x-text="'<?= money(round($p['price_yearly']/12,2)) ?>'.toString()" x-show="yearly" x-cloak></span>
          <span class="<?= $featured ? 'text-slate-400' : 'text-white/35' ?> text-sm">/mes</span>
        </p>
        <p class="text-xs <?= $featured ? 'text-slate-500' : 'text-white/45' ?> mt-1">
          <span x-show="!yearly">facturado mensualmente</span>
          <span x-show="yearly" x-cloak>facturado anualmente · <?= money($p['price_yearly']) ?></span>
        </p>

        <ul class="mt-6 space-y-3 text-sm <?= $featured ? 'text-slate-600' : 'text-white/65' ?>">
          <li class="flex items-center gap-2.5"><i data-lucide="car" class="w-4 h-4 text-brand"></i><?= (int)$p['max_vehicles'] < 0 ? 'Vehículos ilimitados' : $p['max_vehicles'] . ' vehículos' ?></li>
          <li class="flex items-center gap-2.5"><i data-lucide="users" class="w-4 h-4 text-brand"></i><?= (int)$p['max_users'] < 0 ? 'Usuarios ilimitados' : $p['max_users'] . ' usuarios' ?></li>
          <?php foreach ($feats as $f): ?>
            <li class="flex items-start gap-2.5"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5 shrink-0"></i><span><?= e($f) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= url('/register') ?>" class="k-btn w-full mt-7 <?= $featured ? 'k-btn-grad magnetic' : 'k-btn-glass' ?>">
          Empezar con <?= e($p['name']) ?> <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <p class="text-center text-white/45 text-sm mt-8">
      ¿Necesitas algo a medida? <a href="mailto:soporte@kyrosrentcar.com" class="text-white font-medium hover:underline">Escríbenos</a>.
    </p>
  </div>
</section>

<!-- =================================================================
     FAQ
     ================================================================= -->
<section id="faq" class="bg-[#0E1422] py-28" x-data="{open:0}">
  <div class="max-w-3xl mx-auto px-4 sm:px-6">
    <div class="text-center mb-12 reveal">
      <p class="text-[12.5px] font-bold tracking-[0.18em] uppercase text-brand mb-3">FAQ</p>
      <h2 class="font-display text-3xl sm:text-[44px] font-extrabold leading-tight">Preguntas frecuentes</h2>
    </div>
    <div class="space-y-1">
      <?php foreach ([
        ['¿Necesito conocimientos técnicos?','No. Creas tu cuenta y empiezas a cargar tu flotilla en minutos. Todo es visual y guiado.'],
        ['¿Cómo funciona la demo de 5 horas?','En la página de login eliges un código de plan (Starter, Business o Premium). Te creamos una cuenta nueva con datos de ejemplo y la podrás usar 5 horas. Al expirar se elimina automáticamente — junto con todo lo que registres.'],
        ['¿Mis datos están seguros?','Sí. Cada empresa está aislada (multi-tenant), con prepared statements, CSRF en todas las acciones, control de roles y headers de seguridad endurecidos.'],
        ['¿Puedo personalizar mi página pública?','Sí. Configuras logo, colores, descripción, datos de contacto y horario. Cada cliente reserva en `/r/tu-rentcar`.'],
        ['¿Puedo cambiar de plan?','Sí, en cualquier momento desde tu panel. Conservas todos tus datos.'],
        ['¿Tienen API REST?','Sí, en el plan Premium. Con tokens por empresa, aislamiento total entre tenants y respuestas JSON limpias.'],
      ] as $i => $f): ?>
      <div class="border-b border-white/[0.07] reveal">
        <button type="button" @click="open===<?= $i ?>?open=null:open=<?= $i ?>" class="w-full flex items-center justify-between text-left py-5 group">
          <span class="font-medium text-[15.5px] text-white group-hover:text-brand transition-colors"><?= e($f[0]) ?></span>
          <i data-lucide="plus" class="w-4 h-4 text-white/40 transition-transform shrink-0 ml-4" :class="open===<?= $i ?>?'rotate-45 text-brand':''"></i>
        </button>
        <div x-show="open===<?= $i ?>" x-collapse x-cloak class="text-sm text-white/55 pb-5 leading-relaxed -mt-1"><?= e($f[1]) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =================================================================
     FINAL CTA
     ================================================================= -->
<section class="bg-[#0E1422] pb-28">
  <div class="max-w-5xl mx-auto px-4 sm:px-6">
    <div class="relative rounded-[2rem] p-12 lg:p-20 text-center reveal-s overflow-hidden" style="background:var(--grad)">
      <div class="absolute inset-0 grid-dark opacity-30"></div>
      <div class="absolute -top-20 -left-20 w-80 h-80 bg-white/10 blur-3xl rounded-full"></div>
      <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-white/10 blur-3xl rounded-full"></div>
      <div class="relative">
        <h2 class="font-display text-3xl sm:text-[56px] font-extrabold leading-[1.05] text-white tracking-tight">
          Lleva tu rent car<br>al siguiente nivel
        </h2>
        <p class="mt-5 text-white/85 max-w-md mx-auto text-lg">Únete a las empresas que ya gestionan su negocio con Kyros.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center mt-10">
          <a href="<?= url('/register') ?>" class="k-btn k-btn-light magnetic px-8" style="height:54px">Crear mi rent car</a>
          <a href="<?= url('/login') ?>" class="k-btn k-btn-glass px-8" style="height:54px">Iniciar sesión</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php View::push('scripts', '<script>
(function(){
  // Magnetic CTA: small mouse-tracking translate on .magnetic
  document.querySelectorAll(".magnetic").forEach(function(el){
    el.addEventListener("mousemove",function(e){
      var r=el.getBoundingClientRect();
      var x=(e.clientX-r.left-r.width/2)*0.18;
      var y=(e.clientY-r.top-r.height/2)*0.18;
      el.style.transform="translate("+x+"px,"+y+"px)";
    });
    el.addEventListener("mouseleave",function(){ el.style.transform=""; });
  });
})();
</script>'); ?>
