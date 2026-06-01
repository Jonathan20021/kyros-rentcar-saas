<?php
/** Dark cinematic marketing layout (landing, plans). Expects $title, $content. */
use App\Core\View;
$flashes = $_flashes ?? [];
?>
<!DOCTYPE html>
<html lang="es" class="dark" x-data="{scrolled:false,mobile:false}" @scroll.window="scrolled = window.scrollY > 20" x-cloak>
<head>
<?= View::renderPartial('layouts/_assets', ['title' => $title ?? 'Kyros Rent Car', 'metaDescription' => $metaDescription ?? null]) ?>
<script src="<?= asset('js/lucide.min.js') ?>"></script>
<script defer src="<?= asset('js/alpine.min.js') ?>"></script>
<!-- GSAP + ScrollTrigger for the landing page — loads only here, NOT in the admin panel -->
<script src="<?= asset('js/gsap.min.js') ?>"></script>
<script src="<?= asset('js/ScrollTrigger.min.js') ?>"></script>
<!-- Mark the page so the legacy IntersectionObserver in _assets.php stands down -->
<script>window.__USE_GSAP__ = true;</script>
<style>
  .marketing-nav-inner,
  .marketing-logo,
  .marketing-actions{ min-width:0; }
  .marketing-login-link{ display:none !important; }
  .brand-short,
  .nav-cta-short{ display:none; }

  @media (max-width: 640px){
    .marketing-nav-wrap{ padding-left:.75rem !important; padding-right:.75rem !important; }
    .marketing-nav-inner{ padding-left:.65rem !important; padding-right:.5rem !important; gap:.5rem; }
    .marketing-logo{ gap:.6rem; }
    .marketing-logo-mark{ flex:0 0 auto; width:2rem; height:2rem; }
    .marketing-logo-text{ max-width:7.1rem; line-height:1.05; white-space:normal; }
    .marketing-actions{ gap:.4rem; }
    .marketing-primary-cta{ flex:0 0 auto; padding-left:.8rem !important; padding-right:.8rem !important; }
  }

  @media (min-width: 1024px){
    .marketing-login-link{ display:inline-flex !important; }
  }

  @media (max-width: 430px){
    .nav-cta-full{ display:none; }
    .nav-cta-short{ display:inline; }
    .marketing-logo-text{ max-width:5.5rem; font-size:16px; }
  }

  @media (max-width: 350px){
    .brand-full{ display:none; }
    .brand-short{ display:inline; }
    .marketing-logo-text{ max-width:3.2rem; }
    .marketing-primary-cta{ display:none !important; }
  }
</style>
</head>
<body class="bg-[#0E1422] text-white antialiased selection:bg-brand/40">

<!-- Scroll progress -->
<div class="fixed top-0 left-0 right-0 h-[3px] z-[60]"><div id="kprogress" class="h-full origin-left grad-bg" style="transform:scaleX(0)"></div></div>

<!-- Nav -->
<header class="fixed top-0 inset-x-0 z-50 transition-all duration-300" :class="scrolled ? 'py-2' : 'py-4'"
        x-data="{sec:''}" x-init="(() => {
          const ids=['features','showcase','planes','faq'];
          const io=new IntersectionObserver((es)=>es.forEach(e=>{if(e.isIntersecting) sec=e.target.id;}),{rootMargin:'-45% 0px -50% 0px'});
          ids.forEach(i=>{const el=document.getElementById(i); if(el) io.observe(el);});
        })()">
  <div class="marketing-nav-wrap max-w-6xl mx-auto px-4 sm:px-6">
    <div class="marketing-nav-inner flex items-center justify-between rounded-xl px-3 sm:px-4 h-14 transition-all duration-300"
         :class="scrolled ? 'bg-[#141E30]/85 backdrop-blur-xl border border-white/[0.08] shadow-soft' : ''">
      <a href="<?= url('/') ?>" class="marketing-logo flex items-center gap-2.5">
        <div class="marketing-logo-mark w-8 h-8 rounded-lg grad-bg grid place-items-center font-black text-white text-sm">K</div>
        <span class="marketing-logo-text font-display font-extrabold text-[17px] tracking-tight">
          <span class="brand-full">Kyros Rent Car</span>
          <span class="brand-short">Kyros</span>
        </span>
      </a>
      <nav class="hidden md:flex items-center gap-1 text-[14px] font-medium">
        <a href="<?= url('/#showcase') ?>" :class="sec==='showcase'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Producto</a>
        <a href="<?= url('/#modulos') ?>" :class="sec==='modulos'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Módulos</a>
        <a href="<?= url('/#storefront') ?>" :class="sec==='storefront'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Tu página</a>
        <a href="<?= url('/#planes') ?>" :class="sec==='planes'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Planes</a>
        <a href="#faq" :class="sec==='faq'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">FAQ</a>
      </nav>
      <div class="marketing-actions flex items-center gap-2">
        <a href="<?= url('/login') ?>" class="marketing-login-link k-btn k-btn-ghost !h-9 !px-3 !text-[14px] !text-white/70 hover:!bg-white/10 hover:!text-white">Iniciar sesión</a>
        <a href="<?= url('/register') ?>" class="marketing-primary-cta k-btn k-btn-light !h-9 !px-4 !text-[14px]">
          <span class="nav-cta-full">Crear mi rent car</span>
          <span class="nav-cta-short">Crear</span>
        </a>
        <!-- Mobile menu toggle -->
        <button type="button" @click="mobile = !mobile" :aria-expanded="mobile" aria-label="Menú"
                class="md:hidden inline-grid place-items-center w-9 h-9 rounded-lg text-white/80 hover:bg-white/10 transition">
          <span x-show="!mobile" class="contents"><i data-lucide="menu" class="w-5 h-5"></i></span>
          <span x-show="mobile" x-cloak class="contents"><i data-lucide="x" class="w-5 h-5"></i></span>
        </button>
      </div>
    </div>

    <!-- Mobile menu panel -->
    <div x-show="mobile" x-cloak x-transition.opacity.duration.200ms @click.outside="mobile = false"
         class="md:hidden mt-2 rounded-2xl bg-[#141E30]/95 backdrop-blur-xl border border-white/[0.08] shadow-soft p-2">
      <nav class="flex flex-col text-[15px] font-medium">
        <a href="<?= url('/#showcase') ?>" @click="mobile=false" class="px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/[0.06] transition">Producto</a>
        <a href="<?= url('/#modulos') ?>"  @click="mobile=false" class="px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/[0.06] transition">Módulos</a>
        <a href="<?= url('/#storefront') ?>" @click="mobile=false" class="px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/[0.06] transition">Tu página</a>
        <a href="<?= url('/#planes') ?>"   @click="mobile=false" class="px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/[0.06] transition">Planes</a>
        <a href="#faq"      @click="mobile=false" class="px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/[0.06] transition">FAQ</a>
        <a href="<?= url('/#instalar') ?>" @click="mobile=false" class="px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/[0.06] transition flex items-center gap-2">Instalar app <i data-lucide="download" class="w-3.5 h-3.5"></i></a>
      </nav>
      <div class="h-px bg-white/[0.08] my-2"></div>
      <a href="<?= url('/login') ?>" class="block px-4 py-3 rounded-xl text-white/70 hover:text-white hover:bg-white/[0.06] transition text-[15px] font-medium">Iniciar sesión</a>
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
      <a href="<?= url('/seguridad') ?>" class="inline-flex items-center gap-2 mt-6 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[12.5px] text-emerald-400 hover:bg-emerald-500/15 transition">
        <span class="relative w-2 h-2 rounded-full bg-emerald-400">
          <span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-60"></span>
        </span>
        Todos los sistemas operativos
      </a>
    </div>

    <div class="md:col-span-2">
      <p class="font-semibold text-[13px] text-white/85 mb-4 uppercase tracking-wider">Producto</p>
      <ul class="space-y-2.5 text-[14px] text-white/55">
        <li><a href="<?= url('/producto') ?>" class="hover:text-white transition">Recorrido</a></li>
        <li><a href="<?= url('/producto') ?>" class="hover:text-white transition">Módulos</a></li>
        <li><a href="<?= url('/producto') ?>" class="hover:text-white transition">Tu página pública</a></li>
        <li><a href="<?= url('/planes') ?>" class="hover:text-white transition">Planes</a></li>
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
        <a href="mailto:soporte@kyrosrd.com" class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.07] grid place-items-center text-white/55 hover:text-white transition" aria-label="Enviar correo"><i data-lucide="mail" class="w-4 h-4"></i></a>
        <a href="<?= url('/contacto') ?>" class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.07] grid place-items-center text-white/55 hover:text-white transition" aria-label="Contacto"><i data-lucide="message-circle" class="w-4 h-4"></i></a>
        <a href="<?= url('/seguridad') ?>" class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.07] grid place-items-center text-white/55 hover:text-white transition" aria-label="Seguridad"><i data-lucide="shield-check" class="w-4 h-4"></i></a>
      </div>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="border-t border-white/[0.06]">
    <div class="max-w-7xl mx-auto px-5 sm:px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-[12.5px] text-white/45">
      <p>&copy; <?= date('Y') ?> Kyros Rent Car · Todos los derechos reservados.</p>
      <div class="flex items-center gap-5">
        <a href="<?= url('/terminos') ?>" class="hover:text-white transition">Términos</a>
        <a href="<?= url('/privacidad') ?>" class="hover:text-white transition">Privacidad</a>
        <a href="<?= url('/seguridad') ?>" class="hover:text-white transition">Seguridad</a>
      </div>
    </div>
  </div>
</footer>

<div class="fixed bottom-5 right-5 z-50 space-y-2.5" id="toasts"></div>

<!-- ============================================================
     FLOATING INSTALL WIDGET — appears when the SaaS is installable.
     Positioning + visuals are hand-written CSS (NOT Tailwind utilities)
     so the widget renders correctly even if the precompiled stylesheet
     is out of date. Entrance uses Alpine's built-in (inline) transition.
     ============================================================ -->
<style>
  #kpwa-float{ position:fixed; z-index:60; left:12px; right:12px;
    bottom:calc(14px + env(safe-area-inset-bottom)); pointer-events:none; }
  #kpwa-float .kpwa-card{ pointer-events:auto; position:relative; overflow:hidden;
    border-radius:20px; padding:16px;
    background:linear-gradient(160deg,rgba(22,32,52,.95),rgba(11,17,32,.97));
    -webkit-backdrop-filter:blur(20px); backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,.11);
    box-shadow:0 26px 64px -22px rgba(0,0,0,.78), 0 0 0 1px rgba(255,255,255,.04); }
  #kpwa-float .kpwa-sheen{ position:absolute; left:0; right:0; top:0; height:1px;
    background:linear-gradient(90deg,transparent,rgba(242,54,69,.75),transparent); }
  #kpwa-float .kpwa-glow{ position:absolute; top:-64px; left:-40px; width:160px; height:160px;
    border-radius:50%; pointer-events:none;
    background:radial-gradient(circle,rgba(242,54,69,.28),transparent 65%); }
  #kpwa-float .kpwa-close{ position:absolute; top:10px; right:10px; width:28px; height:28px;
    display:grid; place-items:center; border-radius:9px; color:rgba(255,255,255,.45);
    background:transparent; border:0; cursor:pointer; transition:.15s; z-index:2; }
  #kpwa-float .kpwa-close:hover{ color:#fff; background:rgba(255,255,255,.09); }
  #kpwa-float .kpwa-head{ display:flex; align-items:flex-start; gap:14px; }
  #kpwa-float .kpwa-iconwrap{ position:relative; flex:0 0 auto; }
  #kpwa-float .kpwa-iconglow{ position:absolute; inset:0; border-radius:16px;
    background:var(--grad); opacity:.45; filter:blur(10px); animation:kpwaPulse 2.4s ease-in-out infinite; }
  #kpwa-float .kpwa-icon{ position:relative; width:48px; height:48px; border-radius:16px;
    background:var(--grad); display:grid; place-items:center; color:#fff; font-weight:800;
    font-size:21px; box-shadow:0 10px 24px -10px rgba(242,54,69,.7); box-shadow:0 0 0 1px rgba(255,255,255,.15) inset; }
  @keyframes kpwaPulse{ 0%,100%{ opacity:.35 } 50%{ opacity:.6 } }
  #kpwa-float .kpwa-title{ display:flex; align-items:center; gap:7px; font-size:14.5px;
    font-weight:700; color:#fff; line-height:1.15; }
  #kpwa-float .kpwa-badge{ font-size:9.5px; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
    padding:2px 6px; border-radius:6px; color:#FF5C72;
    background:rgba(242,54,69,.14); border:1px solid rgba(242,54,69,.3); }
  #kpwa-float .kpwa-sub{ font-size:12.5px; color:rgba(255,255,255,.56); line-height:1.4; margin-top:4px; padding-right:18px; }
  #kpwa-float .kpwa-actions{ display:flex; align-items:center; gap:8px; margin-top:14px; }
  #kpwa-float .kpwa-install{ flex:1; display:inline-flex; align-items:center; justify-content:center; gap:8px;
    height:44px; border-radius:13px; border:0; cursor:pointer; color:#fff; font-weight:700; font-size:14px;
    background:var(--grad); box-shadow:0 12px 28px -12px rgba(242,54,69,.7); transition:filter .15s, transform .1s; }
  #kpwa-float .kpwa-install:hover{ filter:brightness(1.08); }
  #kpwa-float .kpwa-install:active{ transform:translateY(1px); }
  #kpwa-float .kpwa-install:disabled{ opacity:.6; cursor:wait; }
  #kpwa-float .kpwa-later{ height:44px; padding:0 14px; border-radius:13px; border:0; cursor:pointer;
    color:rgba(255,255,255,.55); font-weight:600; font-size:13px; background:transparent; transition:.15s; }
  #kpwa-float .kpwa-later:hover{ color:#fff; background:rgba(255,255,255,.07); }
  #kpwa-float .kpwa-ios{ margin-top:13px; padding-top:13px; border-top:1px solid rgba(255,255,255,.08); }
  #kpwa-float .kpwa-ios p{ font-size:11px; font-weight:600; color:rgba(255,255,255,.7);
    margin-bottom:10px; display:flex; align-items:center; gap:6px; }
  #kpwa-float .kpwa-ios ol{ list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:9px; }
  #kpwa-float .kpwa-ios li{ display:flex; align-items:center; gap:10px; font-size:12.5px; color:rgba(255,255,255,.62); }
  #kpwa-float .kpwa-ios li b{ color:rgba(255,255,255,.88); }
  #kpwa-float .kpwa-step{ flex:0 0 auto; width:20px; height:20px; border-radius:50%; background:var(--grad);
    display:grid; place-items:center; color:#fff; font-size:10px; font-weight:700; }
  @media (min-width:640px){ #kpwa-float{ left:20px; right:auto; bottom:20px; width:374px; } }
</style>
<div id="kpwa-float" x-data="installFloat()" x-init="init()" x-cloak>
  <div x-show="show" x-transition.opacity.scale.duration.450ms class="kpwa-card">
    <div class="kpwa-sheen"></div>
    <div class="kpwa-glow"></div>
    <button type="button" class="kpwa-close" @click="dismiss()" aria-label="Cerrar"><i data-lucide="x" style="width:16px;height:16px"></i></button>

    <div class="kpwa-head">
      <div class="kpwa-iconwrap">
        <span class="kpwa-iconglow"></span>
        <div class="kpwa-icon">K</div>
      </div>
      <div style="min-width:0">
        <div class="kpwa-title">Instala la app de Kyros <span class="kpwa-badge">PWA</span></div>
        <div class="kpwa-sub">Acceso directo, pantalla completa y uso offline. Sin tienda de apps.</div>
      </div>
    </div>

    <div class="kpwa-actions">
      <button type="button" class="kpwa-install" @click="install()" :disabled="busy">
        <i :data-lucide="isIOS ? 'share' : 'download'" style="width:17px;height:17px"></i>
        <span x-text="busy ? 'Instalando…' : (isIOS ? 'Cómo instalar' : 'Instalar ahora')"></span>
      </button>
      <button type="button" class="kpwa-later" @click="dismiss()">Ahora no</button>
    </div>

    <div class="kpwa-ios" x-show="expanded" x-collapse x-cloak>
      <p><i data-lucide="apple" style="width:14px;height:14px"></i> En iPhone / iPad con Safari</p>
      <ol>
        <li><span class="kpwa-step">1</span> Toca <b>Compartir</b> <i data-lucide="share" style="width:12px;height:12px;display:inline"></i></li>
        <li><span class="kpwa-step">2</span> <b>Añadir a pantalla de inicio</b></li>
        <li><span class="kpwa-step">3</span> Confirma con <b>Añadir</b></li>
      </ol>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', function(){
  window.Alpine.data('installFloat', function(){
    return {
      show:false, expanded:false, isIOS:false, busy:false, dismissed:false,
      init: function(){
        try { this.dismissed = localStorage.getItem('kyros_install_dismissed') === '1'; } catch(e){}
        var p = window.KyrosPWA || {};
        this.isIOS = !!p.isIOSInstallable;
        var self = this;
        var reveal = function(){
          var pwa = window.KyrosPWA || {};
          if (self.dismissed || pwa.isStandalone) return;
          self.isIOS = !!pwa.isIOSInstallable;
          if (pwa.canInstall || pwa.isIOSInstallable){
            setTimeout(function(){ if(!self.dismissed) self.show = true; }, 1400);
          }
        };
        reveal();
        window.addEventListener('kyros:installable', reveal);
        window.addEventListener('kyros:installed', function(){ self.show = false; });
      },
      dismiss: function(){
        this.show = false; this.expanded = false; this.dismissed = true;
        try { localStorage.setItem('kyros_install_dismissed', '1'); } catch(e){}
      },
      install: function(){
        var p = window.KyrosPWA;
        if (this.isIOS){ this.expanded = !this.expanded; return; }
        if (!p || !p.canInstall){ this.expanded = !!(p && p.isIOSInstallable); return; }
        var self = this; this.busy = true;
        p.promptInstall().then(function(r){
          self.busy = false;
          if (r === 'accepted') self.show = false;
        });
      }
    };
  });
});
</script>

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
