<?php
/** Dark cinematic marketing layout (landing, plans). Expects $title, $content. */
use App\Core\View;
$flashes = $_flashes ?? [];
?>
<!DOCTYPE html>
<html lang="es" class="dark" x-data="{scrolled:false,mobile:false}" @scroll.window="scrolled = window.scrollY > 20" x-cloak>
<head>
<?= View::renderPartial('layouts/_assets', ['title' => $title ?? 'Kyros Rent Car', 'metaDescription' => $metaDescription ?? null]) ?>
<script src="https://unpkg.com/lucide@latest"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<!-- GSAP + ScrollTrigger for the landing page — loads only here, NOT in the admin panel -->
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<!-- Mark the page so the legacy IntersectionObserver in _assets.php stands down -->
<script>window.__USE_GSAP__ = true;</script>
</head>
<body class="bg-[#0E1422] text-white antialiased selection:bg-brand/40">

<!-- Scroll progress -->
<div class="fixed top-0 left-0 right-0 h-[3px] z-[60]"><div id="kprogress" class="h-full origin-left grad-bg" style="transform:scaleX(0)"></div></div>

<!-- Nav -->
<header class="fixed top-0 inset-x-0 z-50 transition-all duration-300" :class="scrolled ? 'py-2' : 'py-4'"
        x-data="{sec:''}" x-init="
          const ids=['features','showcase','planes','faq'];
          const io=new IntersectionObserver((es)=>es.forEach(e=>{if(e.isIntersecting) sec=e.target.id;}),{rootMargin:'-45% 0px -50% 0px'});
          ids.forEach(i=>{const el=document.getElementById(i); if(el) io.observe(el);});
        ">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between rounded-xl px-3 sm:px-4 h-14 transition-all duration-300"
         :class="scrolled ? 'bg-[#141E30]/85 backdrop-blur-xl border border-white/[0.08] shadow-soft' : ''">
      <a href="<?= url('/') ?>" class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg grad-bg grid place-items-center font-black text-white text-sm">K</div>
        <span class="font-display font-extrabold text-[17px] tracking-tight">Kyros Rent Car</span>
      </a>
      <nav class="hidden md:flex items-center gap-1 text-[14px] font-medium">
        <a href="#showcase" :class="sec==='showcase'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Producto</a>
        <a href="#modulos" :class="sec==='modulos'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Módulos</a>
        <a href="#storefront" :class="sec==='storefront'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Tu página</a>
        <a href="#planes" :class="sec==='planes'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Planes</a>
        <a href="#faq" :class="sec==='faq'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">FAQ</a>
      </nav>
      <div class="flex items-center gap-2">
        <a href="<?= url('/login') ?>" class="hidden sm:inline-flex k-btn k-btn-ghost !h-9 !px-3 !text-[14px] !text-white/70 hover:!bg-white/10 hover:!text-white">Iniciar sesión</a>
        <a href="<?= url('/register') ?>" class="k-btn k-btn-light !h-9 !px-4 !text-[14px]">Crear mi rent car</a>
      </div>
    </div>
  </div>
</header>

<?= $content ?>

<!-- Footer -->
<footer class="relative border-t border-white/[0.07] bg-[#0B1120] mt-10">
  <div class="max-w-7xl mx-auto px-5 sm:px-6 py-16 grid md:grid-cols-12 gap-10">
    <!-- Brand block -->
    <div class="md:col-span-5">
      <div class="flex items-center gap-2.5 mb-4">
        <div class="w-10 h-10 rounded-xl grad-bg grid place-items-center font-black text-white">K</div>
        <span class="font-display font-extrabold text-xl tracking-tight">Kyros Rent Car</span>
      </div>
      <p class="text-white/55 text-[14.5px] max-w-sm leading-relaxed">El sistema operativo de tu rent car. Flotilla, reservas, contratos, pagos y página pública — en una plataforma.</p>

      <!-- Status -->
      <a href="#" class="inline-flex items-center gap-2 mt-6 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[12.5px] text-emerald-400 hover:bg-emerald-500/15 transition">
        <span class="relative w-2 h-2 rounded-full bg-emerald-400">
          <span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-60"></span>
        </span>
        Todos los sistemas operativos
      </a>
    </div>

    <div class="md:col-span-2">
      <p class="font-semibold text-[13px] text-white/85 mb-4 uppercase tracking-wider">Producto</p>
      <ul class="space-y-2.5 text-[14px] text-white/55">
        <li><a href="#showcase" class="hover:text-white transition">Recorrido</a></li>
        <li><a href="#modulos" class="hover:text-white transition">Módulos</a></li>
        <li><a href="#storefront" class="hover:text-white transition">Tu página pública</a></li>
        <li><a href="#planes" class="hover:text-white transition">Planes</a></li>
        <li><a href="<?= url('/r/kyros-rent-car') ?>" class="hover:text-white transition flex items-center gap-1">Demo en vivo <i data-lucide="external-link" class="w-3 h-3"></i></a></li>
      </ul>
    </div>

    <div class="md:col-span-2">
      <p class="font-semibold text-[13px] text-white/85 mb-4 uppercase tracking-wider">Cuenta</p>
      <ul class="space-y-2.5 text-[14px] text-white/55">
        <li><a href="<?= url('/login') ?>" class="hover:text-white transition">Iniciar sesión</a></li>
        <li><a href="<?= url('/login#demo') ?>" class="hover:text-white transition">Probar demo · 5h</a></li>
        <li><a href="<?= url('/register') ?>" class="hover:text-white transition">Crear rent car</a></li>
      </ul>
    </div>

    <div class="md:col-span-3">
      <p class="font-semibold text-[13px] text-white/85 mb-4 uppercase tracking-wider">Contacto</p>
      <ul class="space-y-2.5 text-[14px] text-white/55">
        <li class="flex items-start gap-2"><i data-lucide="mail" class="w-3.5 h-3.5 mt-1 shrink-0"></i><a href="mailto:soporte@kyrosrd.com" class="hover:text-white transition">soporte@kyrosrd.com</a></li>
        <li class="flex items-start gap-2"><i data-lucide="map-pin" class="w-3.5 h-3.5 mt-1 shrink-0"></i>Santo Domingo, RD</li>
      </ul>
      <div class="flex items-center gap-2 mt-5">
        <a href="#" class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.07] grid place-items-center text-white/55 hover:text-white transition"><i data-lucide="twitter" class="w-4 h-4"></i></a>
        <a href="#" class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.07] grid place-items-center text-white/55 hover:text-white transition"><i data-lucide="instagram" class="w-4 h-4"></i></a>
        <a href="#" class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.07] grid place-items-center text-white/55 hover:text-white transition"><i data-lucide="linkedin" class="w-4 h-4"></i></a>
      </div>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="border-t border-white/[0.06]">
    <div class="max-w-7xl mx-auto px-5 sm:px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-[12.5px] text-white/45">
      <p>&copy; <?= date('Y') ?> Kyros Rent Car · Todos los derechos reservados.</p>
      <div class="flex items-center gap-5">
        <a href="#" class="hover:text-white transition">Términos</a>
        <a href="#" class="hover:text-white transition">Privacidad</a>
        <a href="#" class="hover:text-white transition">Seguridad</a>
      </div>
    </div>
  </div>
</footer>

<div class="fixed bottom-5 right-5 z-50 space-y-2.5" id="toasts"></div>
<script>
  const flashes=[<?php foreach ($flashes as $type=>$messages): foreach ((array)$messages as $m): ?>{type:<?= json_encode($type) ?>,message:<?= json_encode($m) ?>},<?php endforeach; endforeach; ?>];
  const dot={success:'bg-emerald-400',error:'bg-red-400',warning:'bg-amber-400',info:'bg-white/60'};
  flashes.forEach(f=>{const el=document.createElement('div');el.className='glass flex items-center gap-3 pl-4 pr-3 py-3 rounded-2xl min-w-[280px] text-white';el.innerHTML='<span class="w-2 h-2 rounded-full '+(dot[f.type]||'bg-white/60')+'"></span><span class="text-sm font-medium"></span>';el.querySelector('span:last-child').textContent=f.message;document.getElementById('toasts').appendChild(el);setTimeout(()=>el.remove(),6000);});
  document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons());
  document.addEventListener('alpine:initialized',()=>window.lucide&&lucide.createIcons());
</script>

<!--
  GSAP + ScrollTrigger ENHANCER
  Pro scroll-driven animations for the landing. Replaces the legacy
  IntersectionObserver in _assets.php (which is disabled by __USE_GSAP__).
-->
<script>
(function(){
  // ---- Safety net: if GSAP fails to load within 1.5s, force reveal so
  // the page is never trapped at opacity:0 from the legacy CSS. ----
  var safetyTimer = setTimeout(function(){
    document.querySelectorAll('.reveal, .reveal-s').forEach(function(el){ el.classList.add('in'); });
  }, 1500);

  // ---- Wait for GSAP + ScrollTrigger ----
  function ready(){
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
      return setTimeout(ready, 40);
    }
    clearTimeout(safetyTimer);
    init();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ready);
  } else {
    ready();
  }

  function init(){
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Honor reduced-motion: instantly reveal everything and bail out.
    if (reduced) {
      document.querySelectorAll('.reveal, .reveal-s').forEach(function(el){ el.classList.add('in'); });
      bindScrollProgress();
      bindSmoothAnchors();
      return;
    }

    gsap.registerPlugin(ScrollTrigger);
    gsap.config({ nullTargetWarn: false });

    // Defaults for ScrollTrigger across the whole page
    ScrollTrigger.defaults({
      start: 'top 88%',
      toggleActions: 'play none none none', // play once when entering, do not reverse
    });

    // ---- HARD RESET: strip the CSS reveal system so GSAP fully owns state ----
    // The legacy CSS sets opacity:0 + translateY(16px) which can fight GSAP's
    // animations. We remove transitions AND lock the start state via gsap.set
    // so the first paint is what we expect.
    var allReveals = document.querySelectorAll('.reveal, .reveal-s');
    allReveals.forEach(function(el){
      el.style.transition = 'none';
    });
    // Initial state: invisible + slightly translated. GSAP will animate from here.
    gsap.set('.reveal',   { autoAlpha: 0, y: 24 });
    gsap.set('.reveal-s', { autoAlpha: 0, scale: 0.96 });

    var heroSection = document.querySelector('section.scene');

    // ---------- 1. HERO STAGGERED ENTRANCE ----------
    // The hero is above the fold — animate immediately on page load.
    var heroChildren = document.querySelectorAll('section.scene > div > div.text-center > *');
    if (heroChildren.length) {
      // Lock start state for direct hero children (some may not have .reveal)
      gsap.set(heroChildren, { autoAlpha: 0, y: 32 });
      gsap.to(heroChildren, {
        autoAlpha: 1, y: 0, duration: 1.0, stagger: 0.09, ease: 'power3.out',
      });
    }

    // ---------- 2. HERO MOCKUP — fade-up only ----------
    // Entry stays; the scroll-bound yPercent parallax was contributing to
    // the "fighting scroll" feel and is now gone. The mockup sits still as
    // the page scrolls past it, the way Stripe / Linear leave their visuals.
    var heroShot = document.getElementById('heroShot');
    if (heroShot) {
      gsap.set(heroShot, { autoAlpha: 0, y: 60, scale: 0.96 });
      gsap.to(heroShot, {
        autoAlpha: 1, y: 0, scale: 1, duration: 1.2, delay: 0.5, ease: 'power3.out',
      });
    }

    // ---------- 3. FLOATING NOTIFICATION CARDS — gentle entry only ----------
    // The scroll-bound yPercent parallax used to drift the cards across the
    // hero. On long pages with many other scroll triggers, the combined
    // motion felt like the scroll was "fighting" the user. Entry animation
    // stays; parallax removed.
    var floatCards = Array.from(document.querySelectorAll('section.scene .shadow-lift')).filter(function(c){
      return !c.closest('#heroShot');
    });
    floatCards.forEach(function(card, i){
      gsap.set(card, { autoAlpha: 0, y: 40, x: i % 2 ? 30 : -30, rotation: i % 2 ? 3 : -3 });
      gsap.to(card, {
        autoAlpha: 1, y: 0, x: 0, rotation: 0, duration: 1.1,
        delay: 0.7 + i * 0.12, ease: 'power3.out',
      });
    });

    // ---------- 4. LOGO STRIP MARQUEE — already CSS animated, nothing to do ----------

    // ---------- 5. STATS COUNTERS — bound to viewport entry, smooth easing ----------
    document.querySelectorAll('[data-count]').forEach(function(el){
      var end = parseFloat(el.getAttribute('data-count'));
      var suf = el.getAttribute('data-suf') || '';
      var dec = parseInt(el.getAttribute('data-dec') || '0');
      var pre = el.getAttribute('data-pre') || '';
      var obj = { v: 0 };
      gsap.to(obj, {
        v: end, duration: 1.8, ease: 'power2.out',
        scrollTrigger: { trigger: el, start: 'top 80%' },
        onUpdate: function(){
          el.textContent = pre + obj.v.toLocaleString('en-US', {
            minimumFractionDigits: dec, maximumFractionDigits: dec,
          }) + suf;
        }
      });
    });

    // ---------- 6. SECTION REVEALS — stagger per-section, skipping hero ----------
    // Initial state already locked via gsap.set above. Just animate IN.
    document.querySelectorAll('section').forEach(function(section){
      if (section === heroSection) return; // hero handled by dedicated timelines
      var items = section.querySelectorAll('.reveal, .reveal-s');
      if (!items.length) return;
      gsap.to(items, {
        autoAlpha: 1, y: 0, scale: 1, duration: 0.9, ease: 'power3.out',
        stagger: { each: 0.08, from: 'start' },
        scrollTrigger: { trigger: section, start: 'top 85%' },
      });
    });

    // ---------- 7. BENTO GRID — extra rotation flourish on top ----------
    // The section reveal already handles autoAlpha/y. We add a small rotation
    // pass that runs in parallel for a richer feel.
    document.querySelectorAll('section#features .bento').forEach(function(card, i){
      gsap.fromTo(card, { rotation: i % 2 ? 1.5 : -1.5 }, {
        rotation: 0, duration: 0.85, ease: 'power3.out',
        scrollTrigger: { trigger: card, start: 'top 88%' },
      });
    });

    // ---------- 8. PRICING CARDS — overshoot scale entrance ----------
    document.querySelectorAll('#planes .plan-card').forEach(function(card, i){
      // The section loop already handled autoAlpha. Add an extra scale pop.
      gsap.fromTo(card, { scale: 0.92 }, {
        scale: 1, duration: 1.0, delay: i * 0.1, ease: 'back.out(1.4)',
        scrollTrigger: { trigger: card, start: 'top 85%' },
      });
    });

    // ---------- 9. SECTION HEADINGS — parallax intentionally REMOVED ----------
    // Applying yPercent: -5 to EVERY section h2 stacked on top of the hero
    // shot parallax + plan card scale + bento rotation produced a "shaky
    // scroll" effect where multiple elements drifted simultaneously. The
    // page now has one parallax moment (the hero shot) and the rest of the
    // motion is reveal-on-enter only. This is the bigger taste move.

    // ---------- 10. EMERGENCY FALLBACK ----------
    // After all timelines are queued, force a refresh so ScrollTrigger
    // re-evaluates which elements are already in viewport at load time.
    requestAnimationFrame(function(){ ScrollTrigger.refresh(); });

    bindScrollProgress();
    bindSmoothAnchors();

    // Refresh after fonts/images load — measurements need to be re-taken.
    window.addEventListener('load', function(){ ScrollTrigger.refresh(); });
  }

  function bindScrollProgress(){
    // Top progress bar — bound to actual document scroll.
    var bar = document.getElementById('kprogress');
    if (!bar) return;
    function upd(){
      var h = document.documentElement;
      var p = h.scrollTop / ((h.scrollHeight - h.clientHeight) || 1);
      bar.style.transform = 'scaleX(' + Math.min(1, Math.max(0, p)) + ')';
    }
    window.addEventListener('scroll', upd, { passive: true });
    upd();
  }

  function bindSmoothAnchors(){
    // Smooth-scroll for in-page anchors (CSS scroll-behavior is unreliable
    // when there's a sticky header — do it manually).
    document.querySelectorAll('a[href^="#"]').forEach(function(a){
      a.addEventListener('click', function(e){
        var id = a.getAttribute('href').slice(1);
        if (!id) return;
        var t = document.getElementById(id);
        if (!t) return;
        e.preventDefault();
        var headerOffset = 80;
        var y = t.getBoundingClientRect().top + window.pageYOffset - headerOffset;
        window.scrollTo({ top: y, behavior: 'smooth' });
        history.replaceState(null, '', '#' + id);
      });
    });
  }
})();
</script>
<?= $pageScripts ?? '' ?>
<?= View::stack('scripts') ?>
</body>
</html>
