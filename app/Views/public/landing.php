<?php
use App\Core\View;
$demoOffers = $demoOffers ?? [];
?>
<style>
  /* ========================================================
     Landing — "Kyros Atlas" edition
     Premium, confident, restrained. Stripe / Linear feel.
  ======================================================== */
  @keyframes kShimmer { 0%{background-position:0% 50%} 100%{background-position:200% 50%} }
  @keyframes kFloat   { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
  @keyframes kPulse   { 0%,100%{opacity:.55} 50%{opacity:1} }
  @keyframes kMarq    { to{ transform:translateX(-50%) } }

  /* Type system */
  .display-hero { letter-spacing:-.045em; line-height:.96; }
  .display-xl   { letter-spacing:-.038em; line-height:1.02; }
  .display-lg   { letter-spacing:-.028em; line-height:1.08; }
  .eyebrow      { font-size:11.5px; font-weight:700; letter-spacing:.22em; text-transform:uppercase; }
  section[id]{ scroll-margin-top:96px; }

  /* Refined gradient text — slower, more luxurious */
  .text-grad-pro {
    background:linear-gradient(110deg, #FFF 0%, #FF8A9B 35%, var(--brand) 55%, #FF8A9B 75%, #FFF 100%);
    background-size:220% auto; -webkit-background-clip:text; background-clip:text; color:transparent;
    animation:kShimmer 8s linear infinite;
  }

  /* Atmospheric backdrop */
  .scene { position:relative; overflow:hidden; isolation:isolate; }
  /* Brand-dominant atmosphere: one strong red glow at the top, one warmer
     coral wash low-right. Removed the indigo/emerald spill so the accent
     never competes with itself. */
  .scene::before{
    content:""; position:absolute; inset:0; z-index:-1;
    background:
      radial-gradient(72rem 46rem at 50% -12%, color-mix(in srgb,var(--brand) 26%, transparent), transparent 62%),
      radial-gradient(54rem 38rem at 92% 78%, color-mix(in srgb,var(--brand2) 14%, transparent), transparent 65%);
  }
  .grid-floor{
    position:absolute; inset:0; background-image:
      linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size:50px 50px;
    mask-image:radial-gradient(70% 60% at 50% 28%, #000 30%, transparent 80%);
    -webkit-mask-image:radial-gradient(70% 60% at 50% 28%, #000 30%, transparent 80%);
  }

  /* Magnetic shine CTA */
  .magnetic{ position:relative; overflow:hidden; will-change:transform; }
  .magnetic::after{ content:""; position:absolute; inset:0;
    background:linear-gradient(120deg,transparent 30%, rgba(255,255,255,.32) 50%, transparent 70%);
    transform:translateX(-100%); transition:none; }
  .magnetic:hover::after{ transform:translateX(100%); transition:transform .9s ease; }

  /* Pill / badge — softer corners (not full pill) for a more editorial feel,
     since pure rounded-full badges are the default-AI signature. */
  .pill{ display:inline-flex; align-items:center; gap:.55rem;
    padding:.4rem .8rem; border-radius:.55rem;
    background:rgba(255,255,255,.045); border:1px solid rgba(255,255,255,.09);
    font-size:12.5px; font-weight:500; color:rgba(255,255,255,.78);
    backdrop-filter:blur(8px);
  }
  .pill .dot{ position:relative; width:6px; height:6px; border-radius:99px; background:var(--brand); }
  .pill .dot::after{ content:""; position:absolute; inset:-4px; border-radius:99px; background:var(--brand); opacity:.4; animation:kPulse 2.4s ease-in-out infinite; }

  /* Bento card */
  .bento { position:relative; border-radius:1.5rem; background:rgba(255,255,255,.024); border:1px solid rgba(255,255,255,.07); transition:border-color .3s ease, transform .35s cubic-bezier(.2,1,.3,1), background .3s ease; overflow:hidden; }
  .bento:hover{ border-color:rgba(255,255,255,.16); transform:translateY(-3px); background:rgba(255,255,255,.035); }
  .bento::before{ content:""; position:absolute; inset:0; background:radial-gradient(720px circle at var(--mx,50%) var(--my,50%), color-mix(in srgb, var(--brand) 14%, transparent), transparent 40%); opacity:0; transition:opacity .35s ease; pointer-events:none; }
  .bento:hover::before{ opacity:1; }

  /* Stat ticker */
  .ticker{ font-variant-numeric:tabular-nums; }

  /* Showcase progress bar */
  .kbar{ height:2px; border-radius:99px; background:rgba(255,255,255,.08); overflow:hidden; }
  .kbar > i{ display:block; height:100%; background:var(--brand); width:0; }

  /* Plan card */
  .plan-card{ transition:transform .35s cubic-bezier(.2,1,.3,1), box-shadow .35s ease, border-color .25s ease; }
  .plan-card:hover{ transform:translateY(-4px); }
  .plan-popular{ box-shadow:0 30px 60px -28px color-mix(in srgb,var(--brand) 50%, transparent); }

  /* Soft section divider */
  .div-soft{ height:1px; background:linear-gradient(90deg, transparent, rgba(255,255,255,.12), transparent); }

  /* Logo strip lockups (synthetic monochrome marks) */
  .logo-mark{ display:inline-flex; align-items:center; gap:.6rem; opacity:.55; transition:opacity .25s; }
  .logo-mark:hover{ opacity:.95; }
  .logo-mark .glyph{ width:24px; height:24px; border-radius:6px; background:rgba(255,255,255,.85); color:#0E1422; display:grid; place-items:center; font-weight:900; font-size:11px; letter-spacing:.02em; }
  .logo-mark .name{ font-family:'Plus Jakarta Sans','Inter',sans-serif; font-weight:700; letter-spacing:-.018em; font-size:15px; color:#fff; }

  /* Persona card */
  .persona{ position:relative; overflow:hidden; border-radius:1.4rem;
    background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
    border:1px solid rgba(255,255,255,.07);
    transition:transform .35s ease, border-color .25s ease; }
  .persona:hover{ transform:translateY(-3px); border-color:rgba(255,255,255,.14); }

  /* Marquee */
  .marquee-track{ display:flex; gap:3rem; animation:kMarq 40s linear infinite; will-change:transform; }

  /* Compare table */
  .ctbl{ width:100%; border-collapse:separate; border-spacing:0; font-size:14px; }
  .ctbl th, .ctbl td{ padding:14px 18px; text-align:left; border-bottom:1px solid rgba(255,255,255,.06); }
  .ctbl thead th{ color:rgba(255,255,255,.5); font-weight:600; font-size:12px; letter-spacing:.05em; text-transform:uppercase; }
  .ctbl tbody td{ color:rgba(255,255,255,.75); }
  .ctbl tbody tr:hover td{ background:rgba(255,255,255,.02); }
  .ctbl td.feat{ color:rgba(255,255,255,.85); font-weight:500; }
  .ctbl th.col-pop, .ctbl td.col-pop{ background:color-mix(in srgb, var(--brand) 6%, transparent); }
  .ctbl td .yes{ color:#10B981; font-weight:600; }
  .ctbl td .no { color:rgba(255,255,255,.25); }
  @media (max-width: 768px){
    .ctbl thead{ display:none; }
    .ctbl, .ctbl tbody, .ctbl tr, .ctbl td{ display:block; width:100%; }
    .ctbl tr{ padding:1rem; border-bottom:1px solid rgba(255,255,255,.06); }
    .ctbl td{ display:flex; justify-content:space-between; gap:1rem; padding:.28rem 0; border:0; }
    .ctbl td.feat{ font-weight:600; color:#fff; padding-bottom:.5rem; }
    .ctbl td:not(.feat)::before{ content:attr(data-label); color:rgba(255,255,255,.45); font-weight:600; text-align:left; }
    .ctbl td:not(.feat){ align-items:center; }
  }

  /* Mobile polish: keep the marketing page compact without clipped type,
     oversized fixed rows, or CTAs wider than the viewport. */
  .landing-code-url{ overflow-wrap:anywhere; }
  @media (max-width: 640px){
    section[id]{ scroll-margin-top:112px; }
    .landing-hero{ padding-top:7.25rem !important; padding-bottom:5.75rem !important; }
    .landing-hero-title{ font-size:clamp(2.35rem, 10.8vw, 2.8rem) !important; line-height:1.02; letter-spacing:-.038em; }
    .landing-hero-copy{ font-size:15.5px !important; line-height:1.65; }
    .pill{ max-width:100%; align-items:flex-start; text-align:left; line-height:1.35; }
    .pill i{ flex-shrink:0; margin-top:.08rem; }
    .hero-actions .k-cta,
    .mobile-cta-row .k-cta{
      width:100%; max-width:100%; height:54px; justify-content:space-between;
      padding-left:1.15rem; padding-right:.45rem;
    }
    .hero-actions .k-cta span:first-child,
    .mobile-cta-row .k-cta span:first-child{ min-width:0; white-space:normal; text-align:left; }
    .k-cta{ min-width:0; }
    .bento{ border-radius:1.1rem; }
    .bento-mobile-grid{ grid-auto-rows:auto; }
    .landing-code-url{ display:inline-block; max-width:100%; white-space:normal; vertical-align:baseline; }
    .showcase-panel{ min-height:auto !important; padding:1.25rem !important; }
    .storefront-cards{ grid-template-columns:1fr; }
  }
  @media (min-width: 421px) and (max-width: 640px){
    .storefront-cards{ grid-template-columns:repeat(2,minmax(0,1fr)); }
  }
  @media (max-width: 380px){
    .landing-hero-title{ font-size:2.2rem !important; }
    .hero-actions .k-cta{ height:52px; font-size:14px; }
  }

  /* =====================================================================
     HIGH-END VISUAL DESIGN UPGRADES
     Double-Bezel architecture, Button-in-Button trailing icon, cinematic
     full-bleed photography frame, narrative-step vertical structure.
     ===================================================================== */

  /* Double-Bezel: outer shell (machined aluminum tray) + inner core (glass plate).
     Use on premium cards like the featured pricing tier or final CTA. */
  .bezel-outer{
    padding:.45rem; border-radius:1.75rem;
    background:linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.02));
    border:1px solid rgba(255,255,255,.10);
    box-shadow:0 30px 80px -40px rgba(0,0,0,.6);
  }
  .bezel-inner{
    position:relative; border-radius:calc(1.75rem - .45rem); overflow:hidden;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.10), inset 0 -1px 0 rgba(0,0,0,.25);
  }
  /* Light variant for featured pricing card on white inner */
  .bezel-outer-brand{
    padding:.45rem; border-radius:1.75rem;
    background:linear-gradient(180deg, color-mix(in srgb,var(--brand) 35%, transparent), color-mix(in srgb,var(--brand) 12%, transparent));
    border:1px solid color-mix(in srgb,var(--brand) 40%, transparent);
    box-shadow:0 40px 90px -35px color-mix(in srgb,var(--brand) 55%, transparent);
  }

  /* Button-in-Button trailing icon. Use on primary CTAs.
     The icon circle is nested INSIDE the pill, flush right. */
  .k-cta{
    display:inline-flex; align-items:center; gap:.85rem;
    height:56px; padding:0 .5rem 0 1.5rem;
    font-weight:600; font-size:15px; letter-spacing:-.005em;
    border-radius:99px; color:#fff; background:var(--brand);
    transition:transform .35s cubic-bezier(.32,.72,0,1), box-shadow .35s cubic-bezier(.32,.72,0,1);
    box-shadow:0 18px 48px -18px color-mix(in srgb,var(--brand) 60%, transparent);
    will-change:transform;
  }
  .k-cta:hover{ transform:translateY(-1px); box-shadow:0 24px 60px -16px color-mix(in srgb,var(--brand) 70%, transparent); }
  .k-cta:active{ transform:scale(.98); }
  .k-cta .k-cta-arrow{
    display:grid; place-items:center;
    width:44px; height:44px; border-radius:50%;
    background:rgba(255,255,255,.18);
    transition:transform .35s cubic-bezier(.32,.72,0,1), background .35s ease;
  }
  .k-cta:hover .k-cta-arrow{
    transform:translate(2px,-1px) scale(1.05);
    background:rgba(255,255,255,.28);
  }
  .k-cta-light{ background:#fff; color:var(--navy); box-shadow:0 18px 40px -18px rgba(0,0,0,.35); }
  .k-cta-light .k-cta-arrow{ background:rgba(28,36,51,.10); color:var(--navy); }
  .k-cta-light:hover .k-cta-arrow{ background:rgba(28,36,51,.18); }
  .k-cta-ghost{
    background:rgba(255,255,255,.06); color:#fff;
    border:1px solid rgba(255,255,255,.14); box-shadow:none;
  }
  .k-cta-ghost .k-cta-arrow{ background:rgba(255,255,255,.10); }
  .k-cta-ghost:hover{ background:rgba(255,255,255,.10); border-color:rgba(255,255,255,.22); }

  /* Cinematic media frame: full-bleed photography with controlled inner radius
     and a tinted overlay that lets text remain legible on any photo. */
  .cinema-frame{
    position:relative; overflow:hidden; border-radius:2rem;
    isolation:isolate;
  }
  .cinema-frame .cinema-media{
    position:absolute; inset:0; width:100%; height:100%;
    object-fit:cover; z-index:-3;
    transition:transform 1.4s cubic-bezier(.32,.72,0,1);
  }
  /* Photo sits ABOVE the fallback gradient so when it loads, it dominates. */
  .cinema-frame .cinema-photo{ z-index:-2; }
  .cinema-frame:hover .cinema-photo,
  .cinema-frame:hover .cinema-fallback{ transform:scale(1.04); }
  .cinema-frame::after{
    content:""; position:absolute; inset:0; z-index:-1; pointer-events:none;
    background:
      linear-gradient(180deg, rgba(11,17,32,.10) 0%, rgba(11,17,32,.70) 70%, rgba(11,17,32,.92) 100%),
      radial-gradient(120% 80% at 0% 100%, color-mix(in srgb,var(--brand) 22%, transparent), transparent 60%);
  }
  /* Lightweight CSS painted "scene" used as a real-photography fallback so we
     never depend on external photo CDNs that could 404 in offline demos. */
  .cinema-fallback{
    background:
      radial-gradient(60% 80% at 80% 30%, rgba(255,255,255,.08), transparent 60%),
      radial-gradient(40% 60% at 20% 70%, color-mix(in srgb,var(--brand) 30%, transparent), transparent 60%),
      linear-gradient(135deg, #0F1A2E 0%, #1B2745 35%, #2A1320 70%, #3A0E1B 100%);
  }

  /* Narrative step (How it works vertical scroll). Each step has a number
     glyph in its own left gutter and content/detail to the right.
     The badge lives in each STEP's padding zone (not the rail's), so it
     never overlaps column-1 content. The rail's vertical line passes
     through the center of every step badge. */
  .narrative-rail{ position:relative; }
  @media (min-width: 768px){
    .narrative-rail::before{
      content:""; position:absolute; left:1.75rem; top:.5rem; bottom:.5rem;
      width:1px; background:linear-gradient(180deg,
        transparent 0%,
        color-mix(in srgb,var(--brand) 40%, transparent) 8%,
        rgba(255,255,255,.12) 50%,
        color-mix(in srgb,var(--brand) 40%, transparent) 92%,
        transparent 100%);
    }
  }
  .narrative-step{ position:relative; }
  @media (min-width: 768px){
    /* Reserve a 5rem gutter on the left of each step for the badge.
       Without this, position:absolute left:0 lands on top of column-1. */
    .narrative-step{ padding-left:5rem; }
  }
  .narrative-step .step-num{
    position:absolute; left:0; top:0;
    width:3.5rem; height:3.5rem; border-radius:50%;
    display:grid; place-items:center;
    font-family:'Plus Jakarta Sans','Inter',sans-serif;
    font-weight:800; font-size:14px; color:#fff;
    background:var(--brand);
    box-shadow:0 8px 24px -6px color-mix(in srgb,var(--brand) 55%, transparent),
               inset 0 1px 0 rgba(255,255,255,.25);
    z-index:1;
  }
  @media (max-width: 767px){
    .narrative-step .step-num{ position:relative; margin-bottom:1rem; }
  }

  /* Persona feature row — asymmetric layout that breaks the "3 equal cards"
     repetition. Hero persona stretches wider, supporting personas stack. */
  .persona-hero{
    position:relative; overflow:hidden;
    background:
      linear-gradient(140deg, rgba(255,255,255,.06) 0%, rgba(255,255,255,.012) 60%),
      radial-gradient(60% 80% at 100% 0%, color-mix(in srgb,var(--brand) 18%, transparent), transparent 60%);
    border:1px solid rgba(255,255,255,.10); border-radius:1.75rem;
    transition:transform .45s cubic-bezier(.32,.72,0,1), border-color .25s ease;
  }
  .persona-hero:hover{ transform:translateY(-3px); border-color:rgba(255,255,255,.18); }
  .persona-hero::before{
    content:""; position:absolute; right:-3rem; top:-3rem;
    width:18rem; height:18rem; border-radius:50%;
    background:radial-gradient(circle, color-mix(in srgb,var(--brand) 22%, transparent), transparent 60%);
    pointer-events:none; filter:blur(40px);
  }

  /* =====================================================================
     EMIL-TIER MOTION ENHANCEMENTS
     All native CSS / View Transitions / Scroll-Driven Animations.
     Progressive enhancement — falls back gracefully on unsupported
     browsers. Hardware-accelerated, off-main-thread, never breaks scroll.
     ===================================================================== */

  /* Tactile spring-back on .k-cta press. The :active state pulls the
     button in slightly; releasing relies on the transition's overshoot
     spring (custom cubic-bezier > 1 at the end) for the bounce. */
  .k-cta{
    transition:transform .42s cubic-bezier(.18,.84,.18,1.16),
               box-shadow .35s cubic-bezier(.32,.72,0,1),
               background .35s ease;
  }
  .k-cta:active{ transform:scale(.965) translateZ(0); transition-duration:.14s; }
  .k-cta:active .k-cta-arrow{ transform:scale(.92); transition:transform .14s ease-out; }

  /* CSS Scroll-Driven Animations (Chrome 115+, Edge, Opera, Safari 26+).
     Replaces the heavier GSAP scroll-bound transforms when supported.
     Falls back silently on Firefox / older browsers, which still get
     the GSAP entry animations. */
  @supports (animation-timeline: view()) {
    /* Cinema photo subtle parallax — moves through the view range.
       Hardware-accelerated, runs off the main thread, no JS scroll
       listener. The previous attempt at GSAP yPercent was killed
       because it ran on the main thread and competed with other
       scroll triggers; this CSS-only version cannot break scroll. */
    .cinema-frame .cinema-photo,
    .cinema-frame .cinema-fallback{
      animation:cinemaPan linear both;
      animation-timeline:view();
      animation-range:cover 0% cover 100%;
    }
    @keyframes cinemaPan{
      from{ transform:translateY(-4%) scale(1.06); }
      to  { transform:translateY( 4%) scale(1.06); }
    }

    /* Stat counters: gentle scale-in tied to viewport entry */
    section[style*="grad"] .ticker{
      animation:statPop linear both;
      animation-timeline:view();
      animation-range:entry 0% entry 80%;
    }
    @keyframes statPop{
      from{ transform:scale(.92); opacity:.4; }
      to  { transform:scale(1);    opacity:1;  }
    }

    /* Narrative step badges pulse-grow as their step crosses the viewport
       center, drawing the eye down the timeline */
    .narrative-step .step-num{
      animation:badgeFocus linear both;
      animation-timeline:view();
      animation-range:cover 20% cover 80%;
    }
    @keyframes badgeFocus{
      from{ transform:scale(.85); box-shadow:0 4px 12px -2px color-mix(in srgb,var(--brand) 30%, transparent); }
      40%, 60%{ transform:scale(1.06); box-shadow:0 14px 36px -4px color-mix(in srgb,var(--brand) 70%, transparent), inset 0 1px 0 rgba(255,255,255,.35); }
      to{ transform:scale(.85); box-shadow:0 4px 12px -2px color-mix(in srgb,var(--brand) 30%, transparent); }
    }
  }

  /* View Transition naming on the showcase panel so the cross-fade
     between tabs is captured as a shared element by the browser. */
  #showcase .showcase-panel{ view-transition-name:showcase-panel; }

  /* Floating cards: visual affordance that they are interactive.
     The transition value here is overridden by the JS during drag
     and restored on release for the spring-back. */
  section.scene .shadow-lift{
    will-change:transform;
    transition:transform .55s cubic-bezier(.18,.84,.18,1.16);
  }
  section.scene .shadow-lift:hover{ filter:brightness(1.05); }

  /* Reduce motion — overrides every Emil-tier enhancement above */
  @media (prefers-reduced-motion:reduce){
    *{ animation:none !important; transition:none !important; }
    .cinema-frame:hover .cinema-photo,
    .cinema-frame:hover .cinema-fallback,
    .k-cta:hover .k-cta-arrow,
    .k-cta:hover,
    .k-cta:active,
    section.scene .shadow-lift{ transform:none !important; }
  }
</style>

<!-- ==============================================================
     HERO
     ============================================================== -->
<section class="landing-hero scene pt-32 pb-28 sm:pt-36 sm:pb-32">
  <div class="grid-floor"></div>

  <div class="relative max-w-7xl mx-auto px-5 sm:px-6">
    <div class="text-center max-w-[1080px] mx-auto">

      <a href="#planes" class="pill mb-7 reveal">
        <span class="dot"></span>
        <span>Software de alquiler de vehículos para LATAM</span>
        <i data-lucide="arrow-right" class="w-3.5 h-3.5 opacity-60"></i>
      </a>

      <h1 class="landing-hero-title font-display display-hero text-[44px] sm:text-[72px] lg:text-[96px] font-extrabold reveal">
        El sistema operativo<br>
        <span class="text-grad-pro">de tu rent car</span>
      </h1>

      <p class="landing-hero-copy mt-7 sm:mt-9 text-[17px] sm:text-[20px] text-white/55 max-w-[640px] mx-auto leading-[1.55] reveal">
        Flotilla, reservas, contratos, pagos y tu página pública de alquiler en una sola plataforma.
        <span class="text-white/80">Veloz, segura y hecha para vender.</span>
      </p>

      <div class="hero-actions flex flex-col sm:flex-row gap-3 justify-center mt-10 reveal">
        <a href="<?= url('/register') ?>" class="k-cta magnetic group">
          <span>Crear mi rent car gratis</span>
          <span class="k-cta-arrow"><i data-lucide="arrow-right" class="w-4 h-4"></i></span>
        </a>
        <a href="<?= url('/login#demo') ?>" class="k-cta k-cta-ghost group">
          <span>Probar demo · 5h</span>
          <span class="k-cta-arrow"><i data-lucide="play" class="w-4 h-4"></i></span>
        </a>
      </div>
    </div>

    <!-- Product mockup -->
    <div class="relative max-w-[1180px] mx-auto mt-16 sm:mt-20" x-data="{px:0, py:0}" @mousemove.window="if($event.target.closest('#heroShot')){let r=$event.target.closest('#heroShot').getBoundingClientRect(); px=(($event.clientX-r.left)/r.width-.5)*6; py=(($event.clientY-r.top)/r.height-.5)*6;}">
      <div class="absolute -inset-x-10 -top-10 h-44 grad-bg opacity-25 blur-3xl rounded-full"></div>

      <!-- Floating notification cards -->
      <div class="hidden lg:flex absolute -left-14 top-24 z-20 items-center gap-3 p-3.5 rounded-2xl shadow-lift reveal-s"
           style="background:rgba(20,30,48,.78); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,.1); animation:kFloat 7s ease-in-out infinite">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 grid place-items-center"><i data-lucide="calendar-check" class="w-5 h-5"></i></div>
        <div>
          <p class="text-[12.5px] font-semibold text-white">Nueva reserva</p>
          <p class="text-[11px] text-white/55">Honda Civic · 3 días</p>
        </div>
      </div>

      <div class="hidden lg:block absolute -right-12 top-44 z-20 p-4 rounded-2xl shadow-lift reveal-s"
           style="background:rgba(20,30,48,.78); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,.1); animation:kFloat 9s ease-in-out infinite reverse; animation-delay:.4s">
        <p class="text-[11px] text-white/55 font-medium">Ingreso recibido · Tarjeta</p>
        <p class="text-xl font-extrabold ticker text-white mt-0.5">RD$ 18,880</p>
        <p class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1 mt-0.5">
          <i data-lucide="trending-up" class="w-3 h-3"></i> +24% vs. mes anterior
        </p>
      </div>

      <div class="hidden lg:flex absolute -left-8 bottom-12 z-20 items-center gap-3 p-3.5 rounded-2xl shadow-lift reveal-s"
           style="background:rgba(20,30,48,.78); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,.1); animation:kFloat 11s ease-in-out infinite">
        <div class="w-10 h-10 rounded-xl bg-brand/20 text-brand grid place-items-center"><i data-lucide="car" class="w-5 h-5"></i></div>
        <div>
          <p class="text-[12.5px] font-semibold text-white">Flotilla activa</p>
          <p class="text-[11px] text-white/55">23 / 30 vehículos</p>
        </div>
      </div>

      <!-- Dashboard frame -->
      <div id="heroShot" class="relative will-change-transform reveal-s"
           :style="'transform: perspective(2000px) rotateX(' + (6 - py) + 'deg) rotateY(' + px + 'deg)'">
        <div class="rounded-3xl overflow-hidden border border-white/10 bg-[#0B1120] shadow-lift">
          <!-- macOS-style chrome -->
          <div class="h-10 flex items-center gap-1.5 px-4 border-b border-white/[0.05] bg-gradient-to-b from-white/[0.025] to-transparent">
            <span class="w-2.5 h-2.5 rounded-full bg-[#FF5F57]"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-[#FEBC2E]"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-[#28C840]"></span>
            <span class="mx-auto text-[11px] text-white/30 tnum">rentcar.kyrosrd.com/admin/dashboard</span>
            <i data-lucide="circle-user" class="w-4 h-4 text-white/30"></i>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-[220px_1fr]">
            <!-- sidebar -->
            <div class="border-r border-white/[0.05] py-4 px-3 space-y-0.5 hidden sm:block bg-white/[0.012]">
              <div class="flex items-center gap-2 mb-4 px-2">
                <div class="w-6 h-6 rounded-md grad-bg"></div>
                <div class="h-2.5 w-14 rounded bg-white/15"></div>
              </div>
              <?php foreach ([
                ['Dashboard',true],['Reservas',false],['Flotilla',false],['Clientes',false],
                ['Contratos',false],['Pagos',false],['Facturas',false],['Reportes',false],['Configuración',false],
              ] as $row): [$lbl,$on] = $row; ?>
              <div class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg <?= $on ? 'bg-brand/15' : '' ?>">
                <div class="w-3.5 h-3.5 rounded <?= $on ? 'bg-brand' : 'bg-white/15' ?>"></div>
                <div class="h-2 w-16 rounded <?= $on ? 'bg-brand/60' : 'bg-white/10' ?>"></div>
              </div>
              <?php endforeach; ?>
            </div>
            <!-- main -->
            <div class="p-5 sm:p-6">
              <div class="flex items-center justify-between mb-5">
                <div class="h-3 w-40 rounded bg-white/15"></div>
                <div class="flex gap-2">
                  <div class="h-7 w-7 rounded-lg bg-white/[0.06]"></div>
                  <div class="h-7 w-20 rounded-lg grad-bg"></div>
                </div>
              </div>
              <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                <?php
                  $tints = ['bg-brand/15 text-brand','bg-indigo-500/15 text-indigo-300','bg-emerald-500/15 text-emerald-300','bg-amber-500/15 text-amber-300'];
                  $values = ['184k','42','12','98%'];
                ?>
                <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="rounded-xl border border-white/[0.06] p-3 bg-white/[0.018]">
                  <div class="flex items-center gap-2 mb-1.5">
                    <div class="w-6 h-6 rounded-md <?= $tints[$i] ?> grid place-items-center text-[10px] font-bold">★</div>
                    <div class="h-2 w-10 rounded bg-white/15"></div>
                  </div>
                  <div class="text-white font-bold text-base ticker"><?= $values[$i] ?></div>
                  <div class="h-2 w-14 rounded bg-white/10 mt-1.5"></div>
                </div>
                <?php endfor; ?>
              </div>
              <div class="rounded-xl border border-white/[0.06] p-4 h-40 flex items-end gap-1.5 bg-gradient-to-b from-white/[0.018] to-transparent">
                <?php foreach ([42, 58, 48, 72, 55, 82, 66, 90, 68, 96, 80, 64, 86, 72, 88, 95] as $h):
                  $isPeak = $h >= 95;
                ?>
                <div class="flex-1 rounded-t transition-all duration-500"
                     style="height:<?= $h ?>%; background: <?= $isPeak ? 'linear-gradient(180deg, #F23645, #FF5C72)' : 'rgba(255,255,255,.10)' ?>;
                            box-shadow: <?= $isPeak ? '0 0 26px rgba(242,54,69,.4)' : 'none' ?>"></div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==============================================================
     TRUSTED BY / LOGO STRIP
     ============================================================== -->
<section class="border-y border-white/[0.06] py-10 bg-[#0B1120]">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <p class="text-center eyebrow text-white/35 mb-6">Empresas que confían en Kyros</p>
    <div class="overflow-hidden [mask-image:linear-gradient(90deg,transparent,#000_10%,#000_90%,transparent)]">
      <div class="marquee-track shrink-0">
        <?php
        $brands = [
          ['SR','SpeedRent'],['LX','LuxDrive'],['CC','CaribeCars'],
          ['MV','MoveMobility'],['AC','AutoCaribe'],['PR','PuntaRent'],
          ['NX','NexoCars'],['VR','VistaRentals'],
        ];
        for ($k = 0; $k < 2; $k++): foreach ($brands as $b): ?>
          <span class="logo-mark">
            <span class="glyph"><?= e($b[0]) ?></span>
            <span class="name"><?= e($b[1]) ?></span>
          </span>
        <?php endforeach; endfor; ?>
      </div>
    </div>
  </div>
</section>

<!-- ==============================================================
     STATS BAND (animated counters)
     ============================================================== -->
<section class="relative overflow-hidden" style="background:var(--grad)">
  <div class="absolute inset-0 grid-dark opacity-25"></div>
  <div class="relative max-w-7xl mx-auto px-5 sm:px-6 py-14 sm:py-16 grid grid-cols-2 md:grid-cols-4 gap-y-8 gap-x-4 text-white text-center">
    <?php
    $metrics = [
      ['1240','+','Reservas gestionadas'],
      ['98','%','Uptime de plataforma'],
      ['4.9','','Calificación promedio'],
      ['3',' min','Para publicar'],
    ];
    foreach ($metrics as $m): ?>
    <div class="reveal">
      <p class="font-display text-[44px] sm:text-[60px] font-extrabold ticker display-xl" data-count="<?= $m[0] ?>" data-suf="<?= $m[1] ?>" data-dec="<?= strpos($m[0],'.')!==false?'1':'0' ?>">0</p>
      <p class="text-[12.5px] text-white/85 mt-1.5 font-medium"><?= e($m[2]) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ==============================================================
     CINEMATIC MOMENT — full-bleed editorial photography section that
     breaks the dashboard-centric rhythm with a real-world car image.
     Uses cinema-fallback gradient as a guaranteed-rendering background
     so the section never appears empty even if external photos 404.
     ============================================================== -->
<section class="bg-[#0B1120] py-24 sm:py-32">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <div class="cinema-frame reveal-s min-h-[420px] sm:min-h-[520px] lg:min-h-[600px] flex">
      <!-- Fallback paints FIRST so it's at the bottom of the stacking order.
           If the photo loads, it renders on top of the fallback. If it 404s,
           onerror removes the img and the fallback stays visible. -->
      <div class="cinema-media cinema-fallback" aria-hidden="true"></div>
      <img
        class="cinema-media cinema-photo"
        src="<?= url('/assets/demo/vehicles/landing-hero-fleet.jpg') ?>"
        width="1920" height="1080"
        alt=""
        loading="lazy"
        decoding="async"
        onerror="this.remove()"
      >

      <!-- Overlay content: editorial composition, left-aligned, deeply spaced -->
      <div class="relative w-full p-8 sm:p-12 lg:p-16 flex flex-col justify-end">
        <div class="max-w-2xl">
          <h2 class="font-display display-xl text-white text-[34px] sm:text-[52px] lg:text-[68px] font-extrabold leading-[1.02]">
            Tu flotilla.<br>Tu marca.<br>Tu negocio en piloto automático.
          </h2>
          <p class="text-white/75 mt-6 max-w-xl text-[15px] sm:text-[17px] leading-relaxed">
            Kyros se queda detrás de cámara para que tu rent car siga siendo el protagonista. Sin licencias por vehículo, sin sorpresas técnicas.
          </p>
          <div class="flex flex-wrap items-center gap-3 mt-8">
            <a href="<?= url('/register') ?>" class="k-cta k-cta-light group">
              <span>Empezar gratis</span>
              <span class="k-cta-arrow"><i data-lucide="arrow-right" class="w-4 h-4"></i></span>
            </a>
            <a href="#planes" class="k-cta k-cta-ghost group">
              <span>Ver planes</span>
              <span class="k-cta-arrow"><i data-lucide="arrow-down" class="w-4 h-4"></i></span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==============================================================
     PRODUCT SHOWCASE (auto-rotating tabs)
     ============================================================== -->
<section id="showcase" class="bg-[#0B1120] py-24 sm:py-32"
         x-data="{
           t:0, p:0, paused:true, visible:false, _id:null,
           go(i){ this.t=i; this.p=0; },
           tick(){ if(this.paused || !this.visible) return; this.p+=1.5; if(this.p>=100){ this.p=0; this.t=(this.t+1)%4; } }
         }"
         x-init="(() => {
           const io = new IntersectionObserver((es)=>{ visible = es[0].isIntersecting; },{threshold:0.2});
           io.observe($el);
           _id = setInterval(()=>tick(),100);
           paused = false;
         })()"
         @mouseenter="paused=true" @mouseleave="paused=false">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <p class="eyebrow text-brand mb-3">Recorre el producto</p>
      <h2 class="font-display display-lg text-[34px] sm:text-[52px] font-extrabold">Todo lo que necesitas,<br>en un solo clic</h2>
    </div>

    <div class="grid lg:grid-cols-[400px_1fr] gap-6 lg:gap-10">
      <!-- Tabs -->
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
                 :class="t===<?= $i ?>?'grad-bg text-white scale-105':'bg-white/[0.05] text-white/55'">
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

      <!-- Panel — view-transition-name lets the browser cross-fade content -->
      <div class="showcase-panel rounded-3xl p-6 lg:p-10 relative overflow-hidden reveal-s min-h-[420px]"
           style="background:linear-gradient(180deg, rgba(255,255,255,.025), rgba(255,255,255,.01)); border:1px solid rgba(255,255,255,.07);">
        <div class="absolute -top-12 -right-12 w-56 h-56 grad-bg opacity-15 blur-3xl rounded-full"></div>
        <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-indigo-500/20 blur-3xl rounded-full"></div>

        <!-- Reservas -->
        <div x-show="t===0" x-transition.opacity.duration.300ms class="relative">
          <div class="flex items-center justify-between mb-5">
            <p class="font-display font-bold text-lg text-white">Reservas de la semana</p>
            <span class="text-[11px] px-2.5 py-1 rounded-lg bg-white/5 text-white/55 font-medium">7 días</span>
          </div>
          <div class="space-y-2.5">
            <?php foreach ([
              ['Honda Civic 2022','RSV-0042','Confirmada','bg-emerald-500/15 text-emerald-400','15 Jun → 18 Jun','RD$ 7,788'],
              ['Tesla Model 3','RSV-0041','Pendiente',  'bg-amber-500/15 text-amber-400','12 Jun → 14 Jun','RD$ 12,400'],
              ['Mercedes C-Class', 'RSV-0040','En proceso', 'bg-indigo-500/15 text-indigo-300','10 Jun → 16 Jun','RD$ 51,000'],
              ['Toyota Corolla','RSV-0039','Confirmada','bg-emerald-500/15 text-emerald-400','08 Jun → 11 Jun','RD$ 6,600'],
            ] as $r): ?>
            <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:bg-white/[0.05] transition">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-lg bg-white/5 grid place-items-center shrink-0"><i data-lucide="car" class="w-4 h-4 text-white/50"></i></div>
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
            <p class="font-display font-bold text-lg text-white">Flotilla en tiempo real</p>
            <span class="text-[11px] px-2.5 py-1 rounded-lg bg-white/5 text-white/55 font-medium">23 vehículos</span>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <?php
            $states = ['Disponible','Rentado','Mantenimiento','Disponible','Limpieza','Disponible'];
            $cars = ['Honda Civic','Hyundai Tucson','Kia Sportage','Toyota Hilux','Mercedes C-Class','Tesla Model 3'];
            $fleetPhotos = [
              'honda-civic.jpg',
              'hyundai-tucson.jpg',
              'kia-sportage.jpg',
              'toyota-hilux-real.jpg',
              'mercedes-c-class.jpg',
              'tesla-model-3.jpg',
            ];
            foreach ($states as $i => $st):
              $cm = ['Disponible'=>['#10B981','bg-emerald-500/10'],'Rentado'=>['#6366F1','bg-indigo-500/10'],'Mantenimiento'=>['#F59E0B','bg-amber-500/10'],'Limpieza'=>['#06B6D4','bg-cyan-500/10']][$st];
            ?>
            <div class="rounded-xl bg-white/[0.03] border border-white/[0.06] p-4 hover:bg-white/[0.05] transition group">
              <div class="h-20 rounded-lg <?= $cm[1] ?> mb-3 overflow-hidden group-hover:scale-105 transition-transform">
                <img src="<?= url('/assets/demo/vehicles/' . $fleetPhotos[$i]) ?>" class="w-full h-full object-cover opacity-90" alt="<?= e($cars[$i]) ?>">
              </div>
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

<!-- ==============================================================
     PERSONAS — Made for
     ============================================================== -->
<section class="bg-[#0B1120] pb-24 sm:pb-32">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <h2 class="font-display display-lg text-[34px] sm:text-[52px] font-extrabold">Cada rol, su panel</h2>
      <p class="mt-4 text-white/55 leading-relaxed">Roles y permisos granulares: cada miembro de tu equipo ve solo lo que necesita.</p>
    </div>

    <?php
    // Asymmetric persona layout: the Owner card stretches full-width as the
    // hero persona, the two supporting personas sit in a 2-col grid below.
    // This breaks the 3-equal-cards repetition from how-it-works / testimonials.
    $personaHero = ['crown', 'Dueño', 'Decisiones con visibilidad total: dashboard, reportes financieros, gestión de planes, control multi-sucursal y vista completa de toda la operación.',
      ['Reportes en vivo','Multi-sucursal','Planes y facturación','Auditoría completa']];
    $personaSubs = [
      ['users-round', 'Equipo operativo', 'Tu staff atiende reservas, cobra, genera contratos y cierra caja sin tocar lo que no debe.',
        ['Reservas + clientes','Contratos con firma','Cierre de caja diario']],
      ['wrench', 'Flotilla y taller', 'Tu equipo de mantenimiento programa servicios, registra vencimientos y mantiene cada vehículo listo para rentar.',
        ['Estados de vehículo','Mantenimiento','Vencimientos automáticos']],
    ];
    ?>

    <!-- Hero persona: full-width feature card with side-by-side composition -->
    <div class="persona-hero p-7 sm:p-10 reveal-s mb-4 sm:mb-5">
      <div class="grid lg:grid-cols-[1fr_auto] gap-8 lg:gap-12 items-center relative">
        <div>
          <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-2xl bg-brand/15 text-brand grid place-items-center ring-1 ring-brand/25">
              <i data-lucide="<?= e($personaHero[0]) ?>" class="w-6 h-6"></i>
            </div>
            <span class="text-[10.5px] font-mono uppercase tracking-[0.2em] text-white/45">Para el dueño</span>
          </div>
          <h3 class="font-display font-extrabold text-white text-[26px] sm:text-[32px] leading-tight"><?= e($personaHero[1]) ?></h3>
          <p class="text-white/65 text-[15.5px] leading-relaxed mt-3 max-w-xl"><?= e($personaHero[2]) ?></p>
          <div class="grid grid-cols-2 gap-x-6 gap-y-2.5 mt-6 max-w-md">
            <?php foreach ($personaHero[3] as $f): ?>
            <p class="flex items-center gap-2 text-[13.5px] text-white/75">
              <i data-lucide="check" class="w-3.5 h-3.5 text-brand shrink-0"></i><?= e($f) ?>
            </p>
            <?php endforeach; ?>
          </div>
        </div>
        <!-- Right-side stat tile cluster reinforces "visibility" with real KPI shapes -->
        <div class="lg:w-72 grid grid-cols-2 gap-2.5">
          <div class="rounded-2xl p-4 bg-white/[0.04] border border-white/[0.07]">
            <p class="text-[10.5px] font-mono uppercase tracking-wider text-white/40">Ingresos / mes</p>
            <p class="font-display font-extrabold text-white text-2xl tnum mt-1">184k</p>
            <p class="text-[11px] text-emerald-400 mt-1 flex items-center gap-1"><i data-lucide="trending-up" class="w-3 h-3"></i> +24%</p>
          </div>
          <div class="rounded-2xl p-4 bg-white/[0.04] border border-white/[0.07]">
            <p class="text-[10.5px] font-mono uppercase tracking-wider text-white/40">Ocupación</p>
            <p class="font-display font-extrabold text-white text-2xl tnum mt-1">87%</p>
            <p class="text-[11px] text-white/45 mt-1">23 / 26 unid.</p>
          </div>
          <div class="col-span-2 rounded-2xl p-4 bg-white/[0.04] border border-white/[0.07]">
            <div class="flex items-center justify-between">
              <p class="text-[10.5px] font-mono uppercase tracking-wider text-white/40">P&amp;L últimos 6 meses</p>
              <span class="text-[10px] text-white/40 tnum">jun</span>
            </div>
            <div class="flex items-end gap-1 mt-3 h-10">
              <?php foreach ([42, 58, 48, 72, 85, 96] as $j => $h):
                $isLast = $j === 5;
              ?>
              <div class="flex-1 rounded-t" style="height:<?= $h ?>%;background:<?= $isLast ? 'var(--brand)' : 'rgba(255,255,255,.14)' ?>"></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Supporting personas: 2-col grid below the hero -->
    <div class="grid md:grid-cols-2 gap-4 sm:gap-5">
      <?php foreach ($personaSubs as $p): ?>
      <div class="persona p-7 sm:p-8 reveal">
        <div class="flex items-center gap-3 mb-5">
          <div class="w-11 h-11 rounded-xl bg-white/[0.05] text-white/80 border border-white/[0.09] grid place-items-center">
            <i data-lucide="<?= e($p[0]) ?>" class="w-5 h-5"></i>
          </div>
          <h3 class="font-display font-extrabold text-white text-xl"><?= e($p[1]) ?></h3>
        </div>
        <p class="text-white/55 text-[14.5px] leading-relaxed mb-5"><?= e($p[2]) ?></p>
        <div class="space-y-2">
          <?php foreach ($p[3] as $f): ?>
          <p class="flex items-center gap-2 text-[13.5px] text-white/70">
            <i data-lucide="check" class="w-3.5 h-3.5 text-brand shrink-0"></i><?= e($f) ?>
          </p>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ==============================================================
     BENTO FEATURES
     ============================================================== -->
<section id="features" class="bg-[#0B1120] pb-24 sm:pb-32">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <h2 class="font-display display-lg text-[34px] sm:text-[52px] font-extrabold">Una plataforma para toda tu operación</h2>
    </div>

    <div class="bento-mobile-grid grid grid-cols-1 md:grid-cols-6 gap-4 auto-rows-[160px]">
      <!-- Storefront -->
      <div class="bento md:col-span-4 md:row-span-2 p-7 lg:p-9 reveal-s flex flex-col justify-between"
           onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div class="relative z-10">
          <h3 class="font-display font-extrabold text-white text-2xl lg:text-3xl display-lg">Página pública con tu propio slug</h3>
          <p class="text-white/55 mt-3 max-w-md leading-relaxed">Buscador, filtros, histograma de precios, galería de vehículos y reservas online — listo sin escribir una línea de código.</p>
          <div class="landing-code-url mt-6 inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-sm text-white/75 tnum">
            <i data-lucide="link-2" class="w-4 h-4 text-white/40"></i> rentcar.kyrosrd.com/r/tu-rentcar
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

      <div class="bento md:col-span-2 p-6 reveal" onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div class="w-10 h-10 rounded-xl bg-white/[0.05] text-white/80 border border-white/[0.09] grid place-items-center mb-3"><i data-lucide="lock-keyhole" class="w-5 h-5"></i></div>
        <h3 class="font-display font-bold text-white text-lg">Seguridad real</h3>
        <p class="text-sm text-white/55 mt-1.5 leading-relaxed">Multi-tenant aislado, CSRF, prepared statements y roles.</p>
      </div>

      <div class="bento md:col-span-2 p-6 reveal" onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand grid place-items-center mb-3"><i data-lucide="activity" class="w-5 h-5"></i></div>
        <h3 class="font-display font-bold text-white text-lg">Dashboard en vivo</h3>
        <p class="text-sm text-white/55 mt-1.5 leading-relaxed">KPIs, ingresos, ocupación de flotilla y alertas al instante.</p>
      </div>

      <div class="bento md:col-span-3 p-7 reveal-s flex flex-col justify-between"
           onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div>
          <h3 class="font-display text-xl font-extrabold text-white">API REST</h3>
          <p class="text-[12.5px] text-white/45 mt-1">Disponible en Premium</p>
        </div>
        <div class="font-mono text-[11px] leading-relaxed bg-[#06090F] rounded-xl border border-white/[0.06] p-3.5 mt-3">
          <p class="text-white/40"><span class="text-emerald-400">GET</span> /api/v1/vehicles</p>
          <p class="text-white/30 mt-1">Authorization: Bearer kyro_***</p>
        </div>
      </div>

      <div class="bento md:col-span-3 p-7 reveal-s flex flex-col justify-between"
           onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div>
          <h3 class="font-display text-xl font-extrabold text-white">Multi-sucursal, multi-moneda</h3>
          <p class="text-sm text-white/55 mt-2 leading-relaxed">DOP por defecto, ITBIS configurable, múltiples sucursales con stock independiente.</p>
        </div>
        <div class="flex items-center gap-2 mt-3">
          <?php foreach (['🇩🇴','🇲🇽','🇨🇴','🇵🇦','🇨🇷','🇵🇷'] as $flag): ?>
            <span class="text-xl"><?= $flag ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Storage tile: full width, shows quota + extras model -->
      <div class="bento md:col-span-6 p-7 lg:p-9 reveal-s"
           onmousemove="this.style.setProperty('--mx', (event.offsetX)+'px'); this.style.setProperty('--my', (event.offsetY)+'px')">
        <div class="grid lg:grid-cols-[1fr_auto] gap-6 items-center">
          <div>
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-400 grid place-items-center"><i data-lucide="hard-drive" class="w-5 h-5"></i></div>
              <h3 class="font-display text-xl lg:text-2xl font-extrabold text-white">Almacenamiento incluido y flexible</h3>
            </div>
            <p class="text-white/65 text-[15px] leading-relaxed max-w-2xl">
              Fotos, contratos firmados y evidencia — todo en tu cuota.
              <span class="text-white/85">¿Necesitas más?</span>
              Pides una ampliación desde el panel y la activamos en horas, sin migrar ni tocar tus datos.
              Aviso al 80 % y bloqueo limpio al 100 % para evitar sorpresas.
            </p>
          </div>
          <!-- Mini quota visualization -->
          <div class="flex flex-col sm:flex-row lg:flex-col gap-2.5 lg:w-56">
            <?php foreach ([
              ['Starter',  '500 MB', '25%',  'bg-slate-200/60'],
              ['Business', '5 GB',   '60%',  'bg-indigo-400/60'],
              ['Premium',  '25 GB',  '100%', 'grad-bg'],
            ] as $tier): ?>
            <div class="flex-1 lg:flex-none rounded-xl p-3 border border-white/[0.07] bg-white/[0.03]">
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-semibold text-white/65"><?= $tier[0] ?></span>
                <span class="text-[11px] font-mono text-white tnum"><?= $tier[1] ?></span>
              </div>
              <div class="h-1.5 rounded-full bg-white/[0.06] overflow-hidden">
                <div class="h-full <?= $tier[3] ?>" style="width:<?= $tier[2] ?>"></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==============================================================
     PLATAFORMA COMPLETA — every module the SaaS ships with
     ============================================================== -->
<section id="modulos" class="bg-[#0B1120] pb-24 sm:pb-32">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <p class="eyebrow text-brand mb-3">Plataforma completa</p>
      <h2 class="font-display display-lg text-[34px] sm:text-[52px] font-extrabold">Más de 20 módulos<br>listos para usar</h2>
      <p class="mt-4 text-white/55 leading-relaxed">No tienes que armar tu stack — todo viene integrado y conectado entre sí.</p>
    </div>

    <?php
    // Each module: [icon, name, description, badge (Starter/Business/Premium), accent]
    $moduleGroups = [
      ['Operación diaria', 'calendar-check', [
        ['layout-dashboard', 'Dashboard en vivo',  'KPIs de ingresos, ocupación, reservas, alertas y vencimientos en tiempo real.', 'Starter'],
        ['calendar-check',   'Reservas',          'Reservas online + internas, calendario visual, conversión a contrato 1-clic.',  'Starter'],
        ['car',              'Flotilla',          'Vehículos con fotos, VIN, kilometraje, características, estados y galería.',   'Starter'],
        ['id-card',          'Choferes',          'Equipo de chóferes con licencia, tarifa diaria/hora, rating y asignación.',     'Business'],
        ['users',            'Clientes',          'Ficha completa, historial de reservas, documentos, licencias y notas.',         'Starter'],
        ['file-text',        'Contratos',         'Generación automática, firma digital, fotos de entrega y PDF profesional.',     'Business'],
      ]],
      ['Mantenimiento & control', 'wrench', [
        ['wrench',           'Mantenimiento',     'Programa servicios por vehículo: aceite, llantas, frenos, alineación.',         'Business'],
        ['shield-alert',     'Incidencias',       'Multas de tránsito, daños, combustible, llaves perdidas — con evidencia.',      'Business'],
        ['calendar-clock',   'Vencimientos',      'Alertas automáticas de seguro, marbete, placa e inspección por vehículo.',      'Business'],
        ['map-pin',          'Multi-sucursal',    'Stock independiente por sucursal, manager y datos de contacto.',                'Business'],
      ]],
      ['Finanzas', 'wallet', [
        ['credit-card',      'Pagos',             'Efectivo, tarjeta, transferencia, Azul, Cardnet, Stripe — todo trazable.',      'Business'],
        ['receipt',          'Facturas',          'Emisión y seguimiento con estados (borrador, emitida, pagada, anulada).',       'Business'],
        ['trending-down',    'Gastos',            'Combustible, nómina, alquiler, marketing — por sucursal o vehículo.',           'Business'],
        ['calculator',       'Cierre de caja',    'Cierre diario con conteo de efectivo y conciliación de métodos.',               'Business'],
        ['bar-chart-3',      'Reportes & P&L',    'P&L mensual, ingresos vs. gastos, ocupación, top vehículos y clientes.',        'Business'],
      ]],
      ['Catálogo & ventas', 'sparkles', [
        ['globe',            'Página pública',    'Tu propio /r/slug con buscador, filtros, galería y reservas online.',           'Starter'],
        ['tags',              'Categorías',        'Sedán, SUV, Pickup, Van, Lujo — con sus iconos y filtros públicos.',           'Starter'],
        ['sparkles',         'Extras / Servicios','GPS, silla de bebé, WiFi, chofer adicional — facturados automáticamente.',      'Starter'],
        ['ticket-percent',   'Promociones',       'Códigos % o monto fijo, mínimos, máximos de uso y vigencia.',                   'Business'],
      ]],
      ['Sistema & equipo', 'settings', [
        ['users-round',      'Equipo & roles',    'Usuarios con permisos granulares: dueño, operador, taller, solo lectura.',      'Starter'],
        ['history',          'Actividad',         'Audit log de cada acción: quién, qué, cuándo. Búsqueda y filtro.',              'Starter'],
        ['mail',             'Plantillas email',  'Confirmación de reserva, recordatorios y avisos con marcadores dinámicos.',     'Business'],
        ['plug',             'API REST',          'Tokens por empresa, endpoints REST con aislamiento total entre tenants.',       'Premium'],
        ['settings',         'Branding',          'Logo, colores, datos legales, NCF, ITBIS y configuración por tenant.',         'Starter'],
      ]],
    ];
    $badgeColor = [
      'Starter'  => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20',
      'Business' => 'bg-indigo-500/10 text-indigo-300 border-indigo-500/20',
      'Premium'  => 'bg-brand/10 text-brand border-brand/20',
    ];
    foreach ($moduleGroups as $gi => $group):
      [$groupTitle, $groupIcon, $modules] = $group;
    ?>
    <div class="mt-10 first:mt-0 reveal">
      <div class="flex items-center gap-3 mb-5">
        <span class="w-9 h-9 rounded-xl bg-white/[0.04] border border-white/[0.08] grid place-items-center text-brand">
          <i data-lucide="<?= e($groupIcon) ?>" class="w-4 h-4"></i>
        </span>
        <h3 class="font-display font-bold text-white text-lg"><?= e($groupTitle) ?></h3>
        <span class="flex-1 h-px bg-gradient-to-r from-white/[0.07] to-transparent"></span>
        <span class="text-[11px] font-mono text-white/35 tnum"><?= count($modules) ?> módulos</span>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        <?php foreach ($modules as $m):
          [$icon, $name, $desc, $badge] = $m;
        ?>
        <div class="group relative rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5"
             style="background:linear-gradient(180deg,rgba(255,255,255,.025),rgba(255,255,255,.008));border:1px solid rgba(255,255,255,.06);">
          <div class="flex items-start justify-between gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-white/[0.04] border border-white/[0.06] grid place-items-center text-white/70 group-hover:text-brand group-hover:border-brand/30 transition">
              <i data-lucide="<?= e($icon) ?>" class="w-[18px] h-[18px]"></i>
            </div>
            <span class="text-[9.5px] font-bold tracking-wide uppercase px-2 py-1 rounded-full border <?= $badgeColor[$badge] ?>"><?= e($badge) ?></span>
          </div>
          <p class="font-display font-bold text-white text-[15px]"><?= e($name) ?></p>
          <p class="text-[12.5px] text-white/55 mt-1 leading-relaxed"><?= e($desc) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <div class="mt-14 text-center reveal">
      <p class="text-white/45 text-sm">¿Te falta algún módulo? <a href="mailto:soporte@kyrosrd.com" class="text-white font-medium hover:underline">Dínoslo</a> — escuchamos a nuestras rent cars.</p>
    </div>
  </div>
</section>

<!-- ==============================================================
     STOREFRONT PREVIEW — what the customer sees
     ============================================================== -->
<section id="storefront" class="bg-[#0B1120] pb-24 sm:pb-32">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <div class="grid lg:grid-cols-[1fr_1.4fr] gap-10 lg:gap-14 items-center">
      <div class="reveal order-2 lg:order-1">
        <h2 class="font-display display-lg text-[32px] sm:text-[44px] font-extrabold text-white">
          Una página pública<br>tan buena como un sitio dedicado
        </h2>
        <p class="mt-5 text-white/60 text-[15px] leading-relaxed max-w-xl">
          Sin código, sin diseñadores, sin esperar semanas. En el momento que creas tu rent car, ya tienes un <span class="landing-code-url font-mono text-white/85 text-[13.5px] bg-white/[0.05] px-1.5 py-0.5 rounded">rentcar.kyrosrd.com/r/tu-slug</span> con catálogo, filtros, galería y motor de reservas.
        </p>
        <div class="mt-7 space-y-3.5">
          <?php foreach ([
            ['search',          'Buscador con filtros', 'Categoría, precio, transmisión, pasajeros, sucursal.'],
            ['layout-grid',     'Catálogo con galería', 'Cada vehículo con fotos múltiples, precio, badges de disponibilidad.'],
            ['calendar-check',  'Reservas online 24/7', 'El cliente reserva sin llamarte. El lead llega a tu panel al instante.'],
            ['palette',         'Tu identidad de marca','Logo, colores y datos — el cliente nunca ve "Kyros", ve tu rent car.'],
          ] as $f): ?>
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand border border-brand/20 grid place-items-center shrink-0">
              <i data-lucide="<?= e($f[0]) ?>" class="w-4 h-4"></i>
            </div>
            <div>
              <p class="font-display font-bold text-white text-[14.5px]"><?= e($f[1]) ?></p>
              <p class="text-[13px] text-white/55 mt-0.5 leading-relaxed"><?= e($f[2]) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <a href="<?= url('/register') ?>" class="k-btn k-btn-grad magnetic mt-8 px-6 !rounded-xl">
          Crear mi página <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
      </div>

      <!-- Storefront mockup: browser frame -->
      <div class="order-1 lg:order-2 reveal-s">
        <div class="relative">
          <div class="absolute -inset-12 grad-bg opacity-20 blur-3xl rounded-full"></div>
          <div class="relative rounded-2xl overflow-hidden border border-white/[0.08] bg-[#0F172A] shadow-lift">
            <!-- Browser chrome -->
            <div class="h-9 flex items-center gap-1.5 px-4 border-b border-white/[0.05] bg-white/[0.018]">
              <span class="w-2 h-2 rounded-full bg-[#FF5F57]"></span>
              <span class="w-2 h-2 rounded-full bg-[#FEBC2E]"></span>
              <span class="w-2 h-2 rounded-full bg-[#28C840]"></span>
              <span class="mx-auto text-[10.5px] text-white/30 tnum truncate">rentcar.kyrosrd.com/r/luxdrive</span>
            </div>
            <!-- Storefront body -->
            <div class="p-5 sm:p-6">
              <!-- Top brand bar -->
              <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg grad-bg text-white grid place-items-center font-extrabold text-sm">L</div>
                  <div>
                    <p class="text-white font-display font-extrabold text-[13.5px] leading-none">LuxDrive</p>
                    <p class="text-[10px] text-white/40 mt-0.5">Santo Domingo · 4.9 ★</p>
                  </div>
                </div>
                <div class="hidden sm:flex items-center gap-1.5">
                  <span class="text-[10px] text-white/50 px-2 py-1 rounded-md bg-white/[0.04]">Inicio</span>
                  <span class="text-[10px] text-white/50 px-2 py-1 rounded-md bg-white/[0.04]">Catálogo</span>
                  <span class="text-[10px] text-white font-semibold px-2 py-1 rounded-md grad-bg">Reservar</span>
                </div>
              </div>
              <!-- Search bar -->
              <div class="rounded-xl border border-white/[0.07] bg-white/[0.025] p-2.5 flex items-center gap-2 mb-4">
                <i data-lucide="search" class="w-3.5 h-3.5 text-white/40 ml-1.5"></i>
                <div class="h-2 w-24 rounded bg-white/15"></div>
                <span class="ml-auto text-[10px] text-white/45 px-2 py-1 rounded bg-white/[0.04]">SUV ▾</span>
                <span class="text-[10px] text-white/45 px-2 py-1 rounded bg-white/[0.04]">Auto ▾</span>
              </div>
              <!-- Vehicle cards -->
              <div class="storefront-cards grid grid-cols-2 gap-2.5">
                <?php
                $cards = [
                  ['honda-civic.jpg',       'Honda Civic',       '2,400'],
                  ['hyundai-tucson.jpg',    'Hyundai Tucson',    '3,500'],
                  ['mercedes-c-class.jpg',  'Mercedes C-Class',  '8,500'],
                  ['toyota-hilux-real.jpg', 'Toyota Hilux',      '4,500'],
                ];
                foreach ($cards as $cd):
                ?>
                <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] overflow-hidden hover:border-white/[0.16] hover:-translate-y-0.5 transition-all">
                  <div class="h-20 sm:h-24 bg-white/[0.04]">
                    <img src="<?= url('/assets/demo/vehicles/' . $cd[0]) ?>" class="h-full w-full object-cover opacity-90" alt="<?= e($cd[1]) ?>">
                  </div>
                  <div class="p-2.5">
                    <p class="text-white font-semibold text-[11.5px] truncate"><?= e($cd[1]) ?></p>
                    <div class="flex items-center justify-between mt-1">
                      <p class="text-white tnum text-[12.5px] font-extrabold">RD$ <?= $cd[2] ?><span class="text-white/40 font-normal text-[9px]">/día</span></p>
                      <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-300 font-semibold">Libre</span>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <!-- Footer hint -->
              <p class="text-center text-[10px] text-white/35 mt-4 tnum">Vista previa · Tu catálogo real puede tener cientos de vehículos</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==============================================================
     HOW IT WORKS
     ============================================================== -->
<section class="border-y border-white/[0.06] py-24 sm:py-32" style="background:linear-gradient(180deg,#101828,#0B1120);">
  <div class="max-w-6xl mx-auto px-5 sm:px-6">
    <div class="max-w-2xl mb-16 reveal">
      <h2 class="font-display display-lg text-[34px] sm:text-[52px] font-extrabold">En línea en tres pasos.</h2>
      <p class="mt-4 text-white/60 leading-relaxed text-[15.5px]">Sin ingenieros, sin diseñadores, sin esperar semanas. La primera reserva entra el mismo día que firmas.</p>
    </div>

    <?php
    // Vertical narrative — breaks the 3-equal-cards repetition by giving each
    // step its own row with a connecting timeline rail, a tactile detail block
    // on the right, and asymmetric weight that tells a sequential story.
    $steps = [
      [
        'kicker' => 'Minuto 0',
        'title'  => 'Crea tu rent car.',
        'body'   => 'Registra tu empresa, sube tu logo y elige los colores de tu marca. Recibes tu página pública en un slug propio: <span class="landing-code-url font-mono text-white/85 text-[13px] bg-white/[0.05] px-1.5 py-0.5 rounded">rentcar.kyrosrd.com/r/tu-rentcar</span>.',
        'detail' => ['Empresa registrada', 'rent car · plan demo', 'check-circle-2', 'emerald'],
      ],
      [
        'kicker' => 'Minuto 8',
        'title'  => 'Carga tu flotilla.',
        'body'   => 'Vehículos con fotos, precios por día, categorías, sucursales y disponibilidad. Importa desde Excel o crea uno a uno desde el panel.',
        'detail' => ['12 vehículos cargados', 'Honda Civic · Tesla Model 3 · +10', 'car-front', 'brand'],
      ],
      [
        'kicker' => 'Misma tarde',
        'title'  => 'Recibe reservas.',
        'body'   => 'Tus clientes buscan, filtran y reservan online sin llamarte. El lead aparece en tu panel al instante con cliente, vehículo, fechas y monto.',
        'detail' => ['Nueva reserva · RSV-0042', 'Honda Civic · 3 días · RD$ 7,788', 'calendar-check', 'indigo'],
      ],
    ];
    $detailTints = [
      'emerald' => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-300', 'ring' => 'ring-emerald-500/20'],
      'brand'   => ['bg' => 'bg-brand/15',        'text' => 'text-brand',         'ring' => 'ring-brand/25'],
      'indigo'  => ['bg' => 'bg-indigo-500/15',  'text' => 'text-indigo-300',    'ring' => 'ring-indigo-500/20'],
    ];
    ?>
    <div class="narrative-rail space-y-14 sm:space-y-20">
      <?php foreach ($steps as $i => $s):
        $tint = $detailTints[$s['detail'][3]];
      ?>
      <div class="narrative-step grid md:grid-cols-[1.1fr_1fr] gap-8 lg:gap-12 items-center reveal">
        <span class="step-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        <div>
          <p class="eyebrow text-brand/80 mb-3 sr-only"><?= e($s['kicker']) ?></p>
          <p class="text-[12px] font-mono uppercase tracking-[0.18em] text-white/35 mb-3"><?= e($s['kicker']) ?></p>
          <h3 class="font-display font-extrabold text-white text-[26px] sm:text-[34px] leading-[1.1]"><?= e($s['title']) ?></h3>
          <p class="mt-4 text-white/65 leading-relaxed text-[15.5px] max-w-xl"><?= $s['body'] /* contains safe inline span */ ?></p>
        </div>
        <!-- Tactile detail card: simulates the in-product event the user just triggered -->
        <div class="relative">
          <div class="absolute -inset-2 rounded-3xl bg-white/[0.02] blur-2xl"></div>
          <div class="relative rounded-2xl p-5 ring-1 <?= e($tint['ring']) ?>" style="background:linear-gradient(160deg,rgba(255,255,255,.06),rgba(255,255,255,.012));">
            <div class="flex items-start gap-3">
              <div class="w-11 h-11 rounded-xl grid place-items-center shrink-0 <?= e($tint['bg']) ?> <?= e($tint['text']) ?>">
                <i data-lucide="<?= e($s['detail'][2]) ?>" class="w-5 h-5"></i>
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-display font-bold text-white text-[15px] truncate"><?= e($s['detail'][0]) ?></p>
                <p class="text-[12.5px] text-white/55 mt-0.5 truncate"><?= e($s['detail'][1]) ?></p>
              </div>
              <span class="text-[10px] font-mono uppercase tracking-wider text-white/35 tnum">hace 2s</span>
            </div>
            <div class="mt-4 grid grid-cols-4 gap-1">
              <?php for ($j = 0; $j < 4; $j++): ?>
                <span class="h-1 rounded-full <?= $j <= $i ? 'bg-brand' : 'bg-white/10' ?>"></span>
              <?php endfor; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ==============================================================
     DEMO LICENSE
     ============================================================== -->
<?php if (!empty($demoOffers)): ?>
<section class="bg-[#0B1120] py-20 sm:py-24">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <div class="rounded-3xl overflow-hidden relative reveal-s" style="background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.012));border:1px solid rgba(255,255,255,.08);">
      <div class="absolute inset-0 grid-dark opacity-25"></div>
      <div class="relative p-8 lg:p-12 grid lg:grid-cols-[1fr_auto] gap-8 items-center">
        <div class="max-w-xl">
          <span class="pill mb-3"><span class="dot"></span>Sin registro</span>
          <h2 class="font-display display-lg text-[28px] sm:text-[38px] font-extrabold text-white leading-tight">Pruébalo con un código demo de 5 horas</h2>
          <p class="text-white/55 mt-3 leading-relaxed text-[15px]">Cuenta nueva, datos seed cargados, todas las funciones del plan. Al expirar se borra automáticamente.</p>
          <a href="<?= url('/login#demo') ?>" class="k-btn k-btn-grad magnetic mt-6 px-6 !rounded-xl">
            Probar ahora <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
        </div>
        <div class="grid sm:grid-cols-3 lg:grid-cols-1 gap-2.5 w-full lg:w-80">
          <?php foreach ($demoOffers as $o): ?>
          <div class="rounded-xl p-3.5 hover:border-white/20 transition" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
            <p class="eyebrow text-white/45"><?= e($o['plan_name']) ?></p>
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

<!-- ==============================================================
     SOCIAL PROOF — testimonials
     ============================================================== -->
<section class="bg-[#0B1120] py-24 sm:py-32">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <div class="text-center max-w-xl mx-auto mb-14 reveal">
      <h2 class="font-display display-lg text-[34px] sm:text-[48px] font-extrabold">Lo que dicen las rent cars</h2>
    </div>

    <?php
    // Asymmetric layout breaks the "3 equal cards" repetition with the
    // personas and how-it-works sections. One headline quote (big) + two
    // supporting quotes (small) reads as editorial, not templated.
    $testimonials = [
      ['Montamos nuestra rent car online en una tarde. La página pública nos trae reservas todos los días.', 'Carlos Méndez', 'CEO',          'Speed Rent Car',  'Santo Domingo, RD', '#1f2937', 4.9],
      ['Los contratos con firma y las fotos de entrega nos ahorraron muchísimos problemas con clientes.',    'Ana Reyes',     'Fundadora',    'Luxury Drive',    'Santiago, RD',      '#4a5063', 5.0],
      ['Por fin veo mis ingresos y mi flotilla en tiempo real. Cambió cómo administramos el negocio.',        'José Paulino',  'Operations',   'Caribe Cars',     'Punta Cana, RD',    '#10B981', 4.8],
    ];
    $renderStars = function (float $rating): string {
      $full = (int) floor($rating);
      $hasHalf = ($rating - $full) >= 0.4;
      $out = '<div class="flex gap-0.5 text-amber-400">';
      for ($i = 0; $i < $full; $i++) $out .= '<i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>';
      if ($hasHalf) $out .= '<i data-lucide="star-half" class="w-4 h-4 fill-amber-400"></i>';
      for ($i = $full + ($hasHalf ? 1 : 0); $i < 5; $i++) $out .= '<i data-lucide="star" class="w-4 h-4 text-white/15"></i>';
      $out .= '</div>';
      return $out;
    };
    [$hero, $sub1, $sub2] = $testimonials;
    ?>
    <div class="grid lg:grid-cols-[1.6fr_1fr] gap-4 lg:gap-5">
      <!-- Headline quote -->
      <figure class="rounded-3xl p-8 lg:p-10 reveal relative overflow-hidden" style="background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.012));border:1px solid rgba(255,255,255,.08);">
        <div class="absolute top-7 right-8 text-[140px] leading-none font-display font-extrabold text-white/[0.04] select-none pointer-events-none">"</div>
        <div class="relative">
          <div class="flex items-center gap-2 mb-5">
            <?= $renderStars($hero[6]) ?>
            <span class="text-[12px] font-semibold text-white/70 tnum ml-1"><?= number_format($hero[6], 1) ?></span>
          </div>
          <blockquote class="text-white/90 leading-relaxed text-[20px] lg:text-[22px] font-display font-medium max-w-[42ch]">
            <?= e($hero[0]) ?>
          </blockquote>
          <figcaption class="flex items-center gap-3 mt-8 pt-6 border-t border-white/[0.06]">
            <div class="w-12 h-12 rounded-xl grid place-items-center text-white text-base font-bold font-display" style="background:<?= $hero[5] ?>"><?= e(mb_substr($hero[1], 0, 1)) ?></div>
            <div>
              <p class="text-[14.5px] font-semibold text-white"><?= e($hero[1]) ?></p>
              <p class="text-[12.5px] text-white/50"><?= e($hero[2]) ?>, <?= e($hero[3]) ?> · <?= e($hero[4]) ?></p>
            </div>
          </figcaption>
        </div>
      </figure>

      <!-- Two supporting quotes stacked -->
      <div class="grid grid-rows-2 gap-4 lg:gap-5">
        <?php foreach ([$sub1, $sub2] as $tm): ?>
        <figure class="rounded-3xl p-6 reveal flex flex-col" style="background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.008));border:1px solid rgba(255,255,255,.07);">
          <div class="flex items-center gap-1.5 mb-3">
            <?= $renderStars($tm[6]) ?>
            <span class="text-[11.5px] font-semibold text-white/65 tnum ml-1"><?= number_format($tm[6], 1) ?></span>
          </div>
          <blockquote class="text-white/80 leading-relaxed text-[14.5px] flex-1"><?= e($tm[0]) ?></blockquote>
          <figcaption class="flex items-center gap-2.5 mt-4 pt-4 border-t border-white/[0.06]">
            <div class="w-9 h-9 rounded-lg grid place-items-center text-white text-xs font-bold font-display" style="background:<?= $tm[5] ?>"><?= e(mb_substr($tm[1], 0, 1)) ?></div>
            <div class="min-w-0">
              <p class="text-[13px] font-semibold text-white truncate"><?= e($tm[1]) ?></p>
              <p class="text-[11.5px] text-white/45 truncate"><?= e($tm[2]) ?>, <?= e($tm[3]) ?></p>
            </div>
          </figcaption>
        </figure>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ==============================================================
     PRICING — toggle + plans + comparison table
     ============================================================== -->
<section id="planes" class="border-y border-white/[0.06] py-24 sm:py-32" style="background:linear-gradient(180deg,#101828,#0B1120);">
  <div class="max-w-7xl mx-auto px-5 sm:px-6">
    <div class="text-center max-w-2xl mx-auto mb-14 reveal">
      <p class="eyebrow text-brand mb-3">Precios</p>
      <h2 class="font-display display-lg text-[34px] sm:text-[52px] font-extrabold">Empieza gratis. Escala cuando quieras.</h2>
      <p class="mt-4 text-white/55 leading-relaxed">Sin tarjeta de crédito. Cancela o cambia de plan cuando quieras. Facturación mensual, sin permanencia.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-4 max-w-5xl mx-auto mb-16">
      <?php foreach ($plans as $i => $p):
        $featured = $i === 1;
        $feats = $p['features'] ? (json_decode($p['features'], true) ?: []) : [];

        // Featured plan gets the Double-Bezel architecture: outer machined
        // shell in brand tones + inner white plate. The "Más popular" badge
        // lives on the OUTER bezel (not inner) because bezel-inner clips
        // overflow, which would chop off the -top-3 negative offset.
        $cardClasses = $featured
          ? 'bezel-inner relative p-7 bg-white text-navy'
          : 'plan-card relative rounded-3xl p-7 reveal-s';
        $cardStyle = $featured
          ? ''
          : 'background:linear-gradient(180deg,rgba(255,255,255,.04),rgba(255,255,255,.012));border:1px solid rgba(255,255,255,.08);';
      ?>
      <?php if ($featured): ?>
      <div class="plan-card reveal-s bezel-outer-brand relative">
        <span class="absolute -top-3 left-1/2 -translate-x-1/2 z-10 px-3 py-1 rounded-md text-[10px] font-bold tracking-wide uppercase bg-brand text-white shadow-card">Más popular</span>
      <?php endif; ?>
      <div class="<?= $cardClasses ?>"<?= $cardStyle ? ' style="' . $cardStyle . '"' : '' ?>>
        <h3 class="font-display font-bold <?= $featured ? 'text-navy' : 'text-white' ?> text-lg"><?= e($p['name']) ?></h3>
        <p class="mt-4 tnum">
          <span class="font-display text-[48px] font-extrabold <?= $featured ? 'text-navy' : 'text-white' ?>"><?= money($p['price_monthly']) ?></span>
          <span class="<?= $featured ? 'text-slate-400' : 'text-white/35' ?> text-sm">/mes</span>
        </p>
        <p class="text-xs <?= $featured ? 'text-slate-500' : 'text-white/45' ?> mt-1">facturado mensualmente</p>

        <ul class="mt-6 space-y-3 text-sm <?= $featured ? 'text-slate-600' : 'text-white/65' ?>">
          <li class="flex items-center gap-2.5"><i data-lucide="car" class="w-4 h-4 text-brand"></i><?= (int)$p['max_vehicles'] < 0 ? 'Vehículos ilimitados' : $p['max_vehicles'] . ' vehículos' ?></li>
          <li class="flex items-center gap-2.5"><i data-lucide="users" class="w-4 h-4 text-brand"></i><?= (int)$p['max_users'] < 0 ? 'Usuarios ilimitados' : $p['max_users'] . ' usuarios' ?></li>
          <li class="flex items-center gap-2.5"><i data-lucide="hard-drive" class="w-4 h-4 text-brand"></i><?= (int)($p['storage_mb'] ?? 0) >= 1024 ? number_format((int)$p['storage_mb']/1024, 1) . ' GB' : (int)($p['storage_mb'] ?? 500) . ' MB' ?> de almacenamiento</li>
          <?php foreach ($feats as $f): ?>
            <li class="flex items-start gap-2.5"><i data-lucide="check" class="w-4 h-4 text-brand mt-0.5 shrink-0"></i><span><?= e($f) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($featured): ?>
        <a href="<?= url('/register?plan=' . urlencode($p['slug'])) ?>" class="k-cta magnetic group w-full mt-7 !justify-between">
          <span>Empezar con <?= e($p['name']) ?></span>
          <span class="k-cta-arrow"><i data-lucide="arrow-right" class="w-4 h-4"></i></span>
        </a>
        <?php else: ?>
        <a href="<?= url('/register?plan=' . urlencode($p['slug'])) ?>" class="k-btn k-btn-glass w-full mt-7">
          Empezar con <?= e($p['name']) ?> <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
        <?php endif; ?>
      </div>
      <?php if ($featured): ?></div><?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Comparison table -->
    <div class="rounded-3xl overflow-hidden reveal" style="background:rgba(255,255,255,.018);border:1px solid rgba(255,255,255,.07);">
      <div class="px-6 py-4 border-b border-white/[0.06]">
        <p class="font-display font-bold text-white text-lg">Comparativa completa</p>
      </div>
      <div class="overflow-x-auto">
        <table class="ctbl">
          <thead>
            <tr>
              <th>Función</th>
              <th class="text-center">Starter</th>
              <th class="text-center col-pop">Business</th>
              <th class="text-center">Premium</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $rows = [
              ['Página pública con slug',   '✓','✓','✓'],
              ['Reservas online',          '✓','✓','✓'],
              ['Flotilla y categorías',    '✓','✓','✓'],
              ['Contratos con firma + PDF','—','✓','✓'],
              ['Pagos y facturas',         '—','✓','✓'],
              ['Reportes y P&L',           '—','✓','✓'],
              ['Multi-sucursal',           '—','✓','✓'],
              ['Mantenimiento e incidencias','—','✓','✓'],
              ['Cierre de caja diario',    '—','✓','✓'],
              ['Promociones y choferes',   '—','✓','✓'],
              ['API REST',                 '—','—','✓'],
              ['Vehículos ilimitados',     '10','50','∞'],
              ['Usuarios ilimitados',      '2','10','∞'],
              ['Almacenamiento incluido',  '500 MB','5 GB','25 GB'],
              ['Storage extra a demanda',  '✓','✓','✓'],
              ['Soporte',                  'Email','Email','Prioritario'],
            ];
            foreach ($rows as $r):
              $renderCell = function ($v, $col = '') {
                if ($v === '✓') return '<span class="yes">✓</span>';
                if ($v === '—') return '<span class="no">—</span>';
                return '<span class="text-white">' . htmlspecialchars($v) . '</span>';
              };
            ?>
            <tr>
              <td class="feat" data-label="Función"><?= e($r[0]) ?></td>
              <td data-label="Starter" class="text-center"><?= $renderCell($r[1]) ?></td>
              <td data-label="Business" class="text-center col-pop"><?= $renderCell($r[2]) ?></td>
              <td data-label="Premium" class="text-center"><?= $renderCell($r[3]) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <p class="text-center text-white/45 text-sm mt-8">
      ¿Necesitas algo a medida? <a href="mailto:soporte@kyrosrd.com" class="text-white font-medium hover:underline">Escríbenos</a>.
    </p>
  </div>
</section>

<!-- ==============================================================
     FAQ
     ============================================================== -->
<section id="faq" class="bg-[#0B1120] py-24 sm:py-32" x-data="{open:0}">
  <div class="max-w-3xl mx-auto px-5 sm:px-6">
    <div class="text-center mb-12 reveal">
      <p class="eyebrow text-brand mb-3">FAQ</p>
      <h2 class="font-display display-lg text-[34px] sm:text-[48px] font-extrabold">Preguntas frecuentes</h2>
    </div>
    <div class="space-y-1">
      <?php foreach ([
        ['¿Necesito conocimientos técnicos?','No. Creas tu cuenta y empiezas a cargar tu flotilla en minutos. Todo es visual y guiado.'],
        ['¿Cómo funciona la demo de 5 horas?','En la página de login eliges un código de plan (Starter, Business o Premium). Te creamos una cuenta nueva con datos de ejemplo y la podrás usar 5 horas. Al expirar se elimina automáticamente — junto con todo lo que registres.'],
        ['¿Mis datos están seguros?','Sí. Cada empresa está aislada (multi-tenant), con prepared statements, CSRF en todas las acciones, control de roles y headers de seguridad endurecidos.'],
        ['¿Cómo funciona el almacenamiento?','Cada plan incluye espacio para fotos, contratos firmados, evidencia de incidencias y branding (500 MB Starter · 5 GB Business · 25 GB Premium). Verás tu uso en vivo desde el panel y puedes pedir más espacio en cualquier momento — un administrador revisa la solicitud y aprueba el extra.'],
        ['¿Qué pasa si me quedo sin almacenamiento?','Al 80 % de uso te avisamos en el dashboard. Al 100 % bloqueamos cargas nuevas (las existentes siguen intactas). En segundos puedes solicitar más espacio sin migrar nada.'],
        ['¿Puedo personalizar mi página pública?','Sí. Configuras logo, colores, descripción, datos de contacto y horario. Cada cliente reserva en `/r/tu-rentcar`.'],
        ['¿Puedo cambiar de plan?','Sí, en cualquier momento desde tu panel. Conservas todos tus datos.'],
        ['¿Tienen API REST?','Sí, en el plan Premium. Con tokens por empresa, aislamiento total entre tenants y respuestas JSON limpias.'],
        ['¿Quién aprueba las cuentas nuevas?','Toda registración pasa primero por una breve revisión del equipo Kyros — verificamos que la empresa sea real antes de activarla. Normalmente toma menos de 24 horas.'],
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

<!-- ==============================================================
     INSTALL PWA
     ============================================================== -->
<section id="instalar" class="bg-[#0B1120] pb-24 sm:pb-32" style="scroll-margin-top:84px;" x-data="installCard()" x-init="init()">
  <div class="max-w-6xl mx-auto px-5 sm:px-6">
    <div class="surface overflow-hidden reveal-s relative" style="border-radius:1.6rem;">
      <div class="absolute inset-0 grid-dark opacity-[0.18] pointer-events-none"></div>
      <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(242,54,69,.22),transparent 65%);"></div>

      <div class="relative grid lg:grid-cols-2 gap-9 lg:gap-6 p-6 sm:p-12 lg:p-16 items-center">
        <!-- Copy + actions -->
        <div>
          <p class="eyebrow text-brand mb-3 inline-flex items-center gap-2">
            <i data-lucide="smartphone" class="w-4 h-4"></i> App instalable
          </p>
          <h2 class="font-display display-lg text-[30px] sm:text-[44px] font-extrabold leading-[1.05]">
            Lleva Kyros<br>en tu bolsillo.
          </h2>
          <p class="mt-5 text-white/60 text-[15.5px] sm:text-base leading-relaxed max-w-md">
            Instala Kyros como una app nativa en tu teléfono o computadora. Se abre a pantalla completa, carga al instante y funciona incluso con conexión inestable &mdash; sin pasar por ninguna tienda de aplicaciones.
          </p>

          <!-- Benefit chips -->
          <div class="mt-7 flex flex-wrap gap-2.5">
            <?php foreach ([
              ['monitor-smartphone','Pantalla completa'],
              ['wifi-off','Funciona offline'],
              ['zap','Carga instantánea'],
              ['store','Sin tienda de apps'],
            ] as $chip): ?>
            <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/[0.05] border border-white/[0.09] text-[13px] text-white/70">
              <i data-lucide="<?= $chip[0] ?>" class="w-3.5 h-3.5 text-brand"></i><?= e($chip[1]) ?>
            </span>
            <?php endforeach; ?>
          </div>

          <!-- Actions -->
          <div class="mt-9">
            <!-- Already installed -->
            <div x-show="installed" x-cloak class="inline-flex items-center gap-2.5 px-5 py-3.5 rounded-2xl bg-emerald-500/12 border border-emerald-500/25 text-emerald-300 font-semibold text-[15px]">
              <i data-lucide="check-circle-2" class="w-5 h-5"></i> Ya tienes la app instalada
            </div>

            <!-- Install button (Android / desktop Chrome-Edge) + iOS trigger -->
            <div x-show="!installed" x-cloak class="flex flex-col sm:flex-row gap-2.5 max-w-md">
              <button type="button" @click="install()" :disabled="busy"
                      class="k-btn k-btn-grad disabled:opacity-60 disabled:cursor-wait"
                      style="flex:1; height:52px; border-radius:14px; font-size:15px;">
                <i :data-lucide="isIOS ? 'share' : 'download'" style="width:18px;height:18px"></i>
                <span x-text="busy ? 'Instalando…' : (isIOS ? 'Cómo instalar' : 'Instalar app')"></span>
              </button>
              <a href="<?= url('/login#demo') ?>" class="k-btn k-btn-glass"
                 style="flex:1; height:52px; border-radius:14px; font-size:15px;">
                <i data-lucide="play" style="width:17px;height:17px"></i>
                <span>Probar demo · 5h</span>
              </a>
            </div>

            <!-- Browser hint when install isn't available yet (e.g. desktop Firefox/Safari) -->
            <p x-show="!installed && !canInstall && !isIOS" x-cloak class="mt-4 text-[13.5px] text-white/45 flex items-start gap-2 max-w-md">
              <i data-lucide="info" class="w-4 h-4 mt-0.5 shrink-0 text-white/40"></i>
              <span>Para instalar, abre este sitio en <b class="text-white/70">Chrome</b> o <b class="text-white/70">Edge</b> (móvil o escritorio) y vuelve a tocar el botón, o usa el ícono de instalar en la barra de direcciones.</span>
            </p>

            <!-- iOS step-by-step -->
            <div x-show="showIOS" x-cloak x-transition class="mt-5 glass p-5 rounded-2xl max-w-md">
              <p class="text-[13px] font-semibold text-white/85 mb-3 flex items-center gap-2"><i data-lucide="apple" class="w-4 h-4"></i> En iPhone / iPad (Safari)</p>
              <ol class="space-y-3 text-[14px] text-white/65">
                <li class="flex items-center gap-3">
                  <span class="w-7 h-7 shrink-0 rounded-full grad-bg grid place-items-center text-white text-[12px] font-bold">1</span>
                  Toca <b class="text-white/85">Compartir</b> <i data-lucide="share" class="w-3.5 h-3.5 inline align-middle"></i> en la barra inferior.
                </li>
                <li class="flex items-center gap-3">
                  <span class="w-7 h-7 shrink-0 rounded-full grad-bg grid place-items-center text-white text-[12px] font-bold">2</span>
                  Elige <b class="text-white/85">Añadir a pantalla de inicio</b> <i data-lucide="plus-square" class="w-3.5 h-3.5 inline align-middle"></i>.
                </li>
                <li class="flex items-center gap-3">
                  <span class="w-7 h-7 shrink-0 rounded-full grad-bg grid place-items-center text-white text-[12px] font-bold">3</span>
                  Confirma con <b class="text-white/85">Añadir</b>. ¡Listo!
                </li>
              </ol>
            </div>
          </div>
        </div>

        <!-- Phone mockup -->
        <div class="relative flex justify-center lg:justify-end reveal-s">
          <div class="relative w-[230px] sm:w-[260px] floaty">
            <!-- device frame -->
            <div class="relative rounded-[2.5rem] bg-[#0b0f1a] border border-white/[0.12] p-2.5 shadow-2xl" style="box-shadow:0 40px 80px -30px rgba(0,0,0,.8),0 0 0 1px rgba(255,255,255,.04);">
              <div class="absolute top-3 left-1/2 -translate-x-1/2 w-20 h-[18px] bg-black rounded-full z-10"></div>
              <div class="rounded-[2rem] overflow-hidden bg-gradient-to-b from-[#101a2e] to-[#0b1120] aspect-[9/19] flex flex-col">
                <!-- app status row -->
                <div class="flex items-center justify-between px-5 pt-7 pb-2 text-[10px] text-white/45 font-semibold">
                  <span>9:41</span>
                  <span class="flex items-center gap-1"><i data-lucide="wifi" class="w-3 h-3"></i><i data-lucide="battery-full" class="w-3.5 h-3.5"></i></span>
                </div>
                <!-- app content -->
                <div class="flex-1 px-5 pt-2">
                  <div class="flex items-center gap-3 mb-5">
                    <div class="w-11 h-11 rounded-2xl grad-bg grid place-items-center font-black text-white text-lg shadow-lg">K</div>
                    <div>
                      <p class="text-white font-bold text-[14px] leading-tight">Kyros Rent Car</p>
                      <p class="text-white/40 text-[11px]">Dashboard</p>
                    </div>
                  </div>
                  <div class="grid grid-cols-2 gap-2.5 mb-3">
                    <div class="rounded-xl bg-white/[0.05] border border-white/[0.07] p-3">
                      <p class="text-[10px] text-white/40">Ingresos hoy</p>
                      <p class="text-[15px] font-extrabold text-white mt-1">RD$ 48.2K</p>
                    </div>
                    <div class="rounded-xl bg-white/[0.05] border border-white/[0.07] p-3">
                      <p class="text-[10px] text-white/40">Reservas</p>
                      <p class="text-[15px] font-extrabold text-white mt-1">12</p>
                    </div>
                  </div>
                  <div class="rounded-xl bg-white/[0.05] border border-white/[0.07] p-3 mb-2.5">
                    <div class="flex items-center justify-between mb-2">
                      <p class="text-[11px] text-white/55 font-medium">Flotilla activa</p>
                      <span class="text-[10px] text-emerald-300">82% uso</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-white/10 overflow-hidden"><div class="h-full grad-bg" style="width:82%"></div></div>
                  </div>
                  <div class="space-y-2">
                    <?php foreach (['Toyota Corolla · Rentado','Hyundai Tucson · Disponible'] as $row): ?>
                    <div class="flex items-center gap-2.5 rounded-xl bg-white/[0.04] border border-white/[0.06] px-3 py-2.5">
                      <i data-lucide="car" class="w-4 h-4 text-brand"></i>
                      <span class="text-[11.5px] text-white/70"><?= e($row) ?></span>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <!-- bottom nav -->
                <div class="mt-3 px-6 py-3 border-t border-white/[0.06] flex items-center justify-between text-white/35">
                  <i data-lucide="layout-dashboard" class="w-4 h-4 text-brand"></i>
                  <i data-lucide="calendar-check" class="w-4 h-4"></i>
                  <i data-lucide="car" class="w-4 h-4"></i>
                  <i data-lucide="user" class="w-4 h-4"></i>
                </div>
              </div>
            </div>
            <!-- floating install badge (hidden on phones to avoid clipping) -->
            <div class="hidden sm:flex absolute -left-8 top-1/3 glass px-3.5 py-2.5 rounded-xl items-center gap-2 shadow-xl">
              <span class="w-8 h-8 rounded-lg grad-bg grid place-items-center text-white"><i data-lucide="download" class="w-4 h-4"></i></span>
              <div>
                <p class="text-[10px] text-white/45 leading-none">Instalar</p>
                <p class="text-[12px] text-white font-bold leading-tight mt-0.5">Kyros</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php View::push('scripts', '<script>
document.addEventListener("alpine:init", function(){
  window.Alpine.data("installCard", function(){
    return {
      canInstall:false, installed:false, isIOS:false, showIOS:false, busy:false,
      init: function(){
        var p = window.KyrosPWA || {};
        this.installed  = !!p.isStandalone;
        this.isIOS      = !!p.isIOSInstallable;
        this.canInstall = !!p.canInstall;
        var self = this;
        window.addEventListener("kyros:installable", function(){ self.canInstall = true; });
        window.addEventListener("kyros:installed",  function(){ self.installed = true; self.canInstall = false; self.showIOS = false; });
      },
      install: function(){
        var p = window.KyrosPWA;
        if (this.isIOS){ this.showIOS = !this.showIOS; return; }
        if (!p || !p.canInstall){
          if (p && p.isIOSInstallable){ this.showIOS = true; }
          return;
        }
        var self = this; this.busy = true;
        p.promptInstall().then(function(r){
          self.busy = false;
          if (r === "accepted"){ self.installed = true; self.canInstall = false; }
        });
      }
    };
  });
});
</script>'); ?>

<!-- ==============================================================
     FINAL CTA
     ============================================================== -->
<section class="bg-[#0B1120] pb-24 sm:pb-32">
  <div class="max-w-5xl mx-auto px-5 sm:px-6">
    <!-- Double-Bezel: outer aluminum tray frames the brand-gradient inner plate -->
    <div class="bezel-outer reveal-s">
      <div class="bezel-inner p-7 sm:p-12 lg:p-20 text-center" style="background:var(--grad)">
        <div class="absolute inset-0 grid-dark opacity-30 pointer-events-none"></div>
        <div class="absolute -top-20 -left-20 w-80 h-80 bg-white/10 blur-3xl rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-white/10 blur-3xl rounded-full pointer-events-none"></div>
        <div class="relative">
          <h2 class="font-display display-xl text-[32px] sm:text-[56px] font-extrabold text-white">
            Lleva tu rent car<br>al siguiente nivel.
          </h2>
          <p class="mt-5 text-white/85 max-w-md mx-auto text-lg">Únete a las empresas que ya gestionan su negocio con Kyros.</p>
          <div class="mobile-cta-row flex flex-col sm:flex-row gap-3 justify-center mt-10">
            <a href="<?= url('/register') ?>" class="k-cta k-cta-light magnetic group">
              <span>Crear mi rent car</span>
              <span class="k-cta-arrow"><i data-lucide="arrow-right" class="w-4 h-4"></i></span>
            </a>
            <a href="<?= url('/login') ?>" class="k-cta k-cta-ghost group">
              <span>Iniciar sesión</span>
              <span class="k-cta-arrow"><i data-lucide="log-in" class="w-4 h-4"></i></span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php View::push('scripts', '<script>
(function(){
  var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  // ============================================================
  // 1. MAGNETIC CTA — Spring-lerp implementation (Emil-tier)
  // ------------------------------------------------------------
  // The original implementation wrote a new transform on every
  // mousemove event. On a 120Hz mouse this fires ~120 times/sec
  // and visibly jitters because the value jumps without smoothing.
  // This rewrite stores a target + a current value, and uses a
  // requestAnimationFrame loop to lerp current → target at 0.18
  // per frame. Result: cursor moves fast, button follows with a
  // smooth "weighted" feel. Stops the rAF loop when settled.
  // ============================================================
  if (!reduceMotion) document.querySelectorAll(".magnetic").forEach(function(el){
    var target = { x: 0, y: 0 };
    var current = { x: 0, y: 0 };
    var raf = null;
    var settled = true;

    function tick(){
      current.x += (target.x - current.x) * 0.18;
      current.y += (target.y - current.y) * 0.18;
      el.style.transform = "translate3d(" + current.x.toFixed(2) + "px," + current.y.toFixed(2) + "px,0)";
      var dx = target.x - current.x, dy = target.y - current.y;
      if (Math.abs(dx) > 0.1 || Math.abs(dy) > 0.1) {
        raf = requestAnimationFrame(tick);
      } else {
        raf = null;
        if (settled && target.x === 0 && target.y === 0) el.style.transform = "";
      }
    }
    function pulse(){ if (!raf) raf = requestAnimationFrame(tick); }

    el.addEventListener("pointermove", function(e){
      var r = el.getBoundingClientRect();
      target.x = (e.clientX - r.left - r.width / 2) * 0.20;
      target.y = (e.clientY - r.top - r.height / 2) * 0.20;
      settled = false;
      pulse();
    });
    el.addEventListener("pointerleave", function(){
      target.x = 0; target.y = 0;
      settled = true;
      pulse();
    });
  });

  // ============================================================
  // 2. DRAGGABLE FLOATING CARDS (Hero signature)
  // ------------------------------------------------------------
  // Grab the floating notification cards in the hero, make them
  // tactile: pointer-down → follow finger/cursor, pointer-up →
  // spring back to origin. Pure transform/translate, no layout
  // mutation, hardware accelerated. Touch + mouse + pen unified
  // via Pointer Events. Disabled under reduced-motion.
  // ============================================================
  if (!reduceMotion) {
    var floats = document.querySelectorAll("section.scene .shadow-lift");
    floats.forEach(function(card){
      if (card.closest("#heroShot")) return; // skip mockup itself
      card.style.cursor = "grab";
      card.style.touchAction = "none";
      card.style.transition = "transform .55s cubic-bezier(.18,.84,.18,1.16)";
      var dragging = false, startX = 0, startY = 0, currX = 0, currY = 0;

      card.addEventListener("pointerdown", function(e){
        dragging = true;
        startX = e.clientX; startY = e.clientY;
        card.style.transition = "none";
        card.style.cursor = "grabbing";
        card.style.zIndex = "50";
        try { card.setPointerCapture(e.pointerId); } catch(_){}
      });
      card.addEventListener("pointermove", function(e){
        if (!dragging) return;
        currX = e.clientX - startX;
        currY = e.clientY - startY;
        var rot = Math.max(-12, Math.min(12, currX * 0.05));
        card.style.transform = "translate3d(" + currX + "px," + currY + "px,0) rotate(" + rot + "deg)";
      });
      function release(e){
        if (!dragging) return;
        dragging = false;
        card.style.cursor = "grab";
        card.style.transition = "transform .55s cubic-bezier(.18,.84,.18,1.16)";
        card.style.transform = "";
        // Reset z after the spring-back so it does not snap visually
        setTimeout(function(){ card.style.zIndex = ""; }, 600);
        try { card.releasePointerCapture(e.pointerId); } catch(_){}
      }
      card.addEventListener("pointerup", release);
      card.addEventListener("pointercancel", release);
    });
  }

  // ============================================================
  // 3. VIEW TRANSITIONS on showcase tab switch (Chrome 111+)
  // ------------------------------------------------------------
  // Wrap the existing Alpine state mutation in a View Transition
  // so the panel content smoothly cross-fades instead of x-transition
  // hard-cut. Progressive enhancement: browsers without the API
  // ignore this entirely and use the Alpine transition fallback.
  // ============================================================
  if (document.startViewTransition) {
    var showcase = document.getElementById("showcase");
    if (showcase) {
      Array.prototype.forEach.call(showcase.querySelectorAll("button"), function(btn){
        var clickExpr = btn.getAttribute("@click") || btn.getAttribute("x-on:click") || "";
        if (clickExpr.indexOf("go(") !== 0) return;
        btn.addEventListener("click", function(){
          if (window.Alpine && typeof document.startViewTransition === "function") {
            document.startViewTransition(function(){
              // Alpine reactivity has already updated DOM by the time
              // this callback runs (microtask); the View Transition
              // captures the difference and animates between snapshots.
            });
          }
        }, true);
      });
    }
  }
})();
</script>'); ?>
