<?php
/**
 * Kyros design system — "Wheelzie" edition (red + navy, light app).
 * Params: $title, $accent (hex), $accent2 (hex), $metaDescription.
 *  - accent  : primary brand (default red #F23645)
 *  - accent2 : gradient companion (default warm red #FF5C72)
 */
$accent  = $accent  ?? '#F23645';
$accent2 = $accent2 ?? '#FF5C72';
$navy    = '#1C2433';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?= e($title ?? 'Kyros Rent Car') ?></title>
<meta name="description" content="<?= e($metaDescription ?? 'Kyros Rent Car — software para administrar tu negocio de alquiler de vehiculos.') ?>">
<meta name="theme-color" content="#0E1422">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  darkMode: 'class',
  theme: { extend: {
    colors: {
      brand: '<?= e($accent) ?>', brand2: '<?= e($accent2) ?>',
      ink: '<?= e($navy) ?>', navy: '<?= e($navy) ?>',
      paper: '#EEF2F8', line: '#E6EAF1',
    },
    fontFamily: {
      sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      display: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
    },
    boxShadow: {
      xs: '0 1px 2px rgba(28,36,51,.06)',
      card: '0 1px 3px rgba(28,36,51,.05), 0 6px 16px -10px rgba(28,36,51,.12)',
      soft: '0 12px 36px -16px rgba(28,36,51,.20)',
      lift: '0 26px 64px -24px rgba(28,36,51,.34)',
    },
    borderRadius: { lg:'.65rem', xl:'.85rem', '2xl':'1.1rem', '3xl':'1.4rem', '4xl':'1.8rem' },
  }}
}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;450;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{ --brand:<?= e($accent) ?>; --brand2:<?= e($accent2) ?>; --navy:<?= e($navy) ?>; --grad:linear-gradient(120deg,var(--brand),var(--brand2)); }
  *{ -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility; }
  html{ scroll-behavior:smooth; }
  body{ font-family:'Inter',sans-serif; letter-spacing:-.011em; }
  h1,h2,h3,h4,.font-display{ font-family:'Plus Jakarta Sans','Inter',sans-serif; letter-spacing:-.022em; }
  .display-xl{ letter-spacing:-.035em; line-height:1.02; }
  [x-cloak]{ display:none !important; }
  .tnum{ font-variant-numeric:tabular-nums; font-feature-settings:'tnum'; }
  main table td, main table th{ font-variant-numeric:tabular-nums; }
  svg.lucide{ stroke-width:1.75; }

  .hairline{ border-color:#E6EAF1; } .dark .hairline{ border-color:rgba(255,255,255,.08); }

  ::-webkit-scrollbar{ width:10px; height:10px; }
  ::-webkit-scrollbar-thumb{ background:#CFD6E2; border-radius:8px; border:3px solid transparent; background-clip:content-box; }
  ::-webkit-scrollbar-thumb:hover{ background:#B6BFCF; background-clip:content-box; }
  .dark ::-webkit-scrollbar-thumb{ background:#2A3142; background-clip:content-box; }

  .text-grad{ background:var(--grad); -webkit-background-clip:text; background-clip:text; color:transparent; }
  .grad-bg{ background:var(--grad); }

  /* Buttons */
  .k-btn{ display:inline-flex; align-items:center; justify-content:center; gap:.5rem; height:40px; padding:0 1.05rem; font-weight:600; font-size:14px; line-height:1; border-radius:.75rem; transition:.16s ease; white-space:nowrap; cursor:pointer; border:1px solid transparent; }
  .k-btn:active{ transform:translateY(.5px); }
  .k-btn-grad{ background:var(--brand); color:#fff; box-shadow:0 6px 16px -8px color-mix(in srgb,var(--brand) 70%, transparent); }
  .k-btn-grad:hover{ background:color-mix(in srgb,var(--brand) 88%, #000); }
  .k-btn-dark{ background:var(--navy); color:#fff; } .k-btn-dark:hover{ background:#2c3550; }
  .k-btn-light{ background:#fff; color:var(--navy); box-shadow:0 1px 2px rgba(28,36,51,.14); } .k-btn-light:hover{ background:#f2f4f8; }
  .k-btn-outline{ border-color:#E2E6EE; color:#2b3346; background:#fff; } .k-btn-outline:hover{ background:#F7F9FC; border-color:#D3D9E4; }
  .k-btn-ghost{ color:#5a6377; } .k-btn-ghost:hover{ background:#EEF1F6; color:var(--navy); }
  .k-btn-glass{ background:rgba(255,255,255,.07); color:#fff; border-color:rgba(255,255,255,.16); }
  .k-btn-glass:hover{ background:rgba(255,255,255,.12); border-color:rgba(255,255,255,.26); }

  /* Light surfaces */
  .card{ background:#fff; border:1px solid #EAEEF4; border-radius:1.1rem; box-shadow:0 1px 3px rgba(28,36,51,.04), 0 8px 20px -16px rgba(28,36,51,.18); }
  .icon-btn{ display:inline-grid; place-items:center; width:42px; height:42px; border-radius:.8rem; background:#fff; border:1px solid #EAEEF4; color:#5a6377; transition:.15s; box-shadow:0 1px 2px rgba(28,36,51,.04); }
  .icon-btn:hover{ color:var(--navy); border-color:#D7DDE8; }
  .fld{ width:100%; height:42px; padding:0 .9rem; font-size:14px; border-radius:.7rem; border:1px solid #E2E6EE; background:#fff; outline:none; transition:.14s; color:var(--navy); }
  textarea.fld{ height:auto; padding:.6rem .9rem; }
  select.fld{ padding-right:2rem; }
  .fld:focus{ border-color:var(--brand); box-shadow:0 0 0 3px color-mix(in srgb,var(--brand) 16%, transparent); }
  .fld::placeholder{ color:#97a0b2; }

  /* Trend pills */
  .trend{ display:inline-flex; align-items:center; gap:3px; font-size:11px; font-weight:700; padding:3px 8px; border-radius:99px; }
  .trend-up{ background:#E6F7EE; color:#0E9F6E; }
  .trend-down{ background:#FDECEE; color:#E11D48; }

  /* Progress */
  .progress{ height:7px; border-radius:99px; background:#EEF1F6; overflow:hidden; }
  .progress > i{ display:block; height:100%; border-radius:99px; background:var(--navy); }

  /* Dark surfaces (landing/auth) */
  .surface{ background:#141A28; border:1px solid rgba(255,255,255,.07); border-radius:1.1rem; }
  .glass{ background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09); border-radius:1.1rem; backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); }
  .fld-dark{ width:100%; height:46px; padding:0 1rem; font-size:14px; border-radius:.7rem; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.05); outline:none; transition:.14s; color:#fff; }
  .fld-dark:focus{ border-color:var(--brand); box-shadow:0 0 0 3px color-mix(in srgb,var(--brand) 26%, transparent); }
  .fld-dark::placeholder{ color:rgba(255,255,255,.4); }

  /* Backdrops */
  .mesh-dark{ position:relative; background:#0E1422; }
  .mesh-dark::before{ content:""; position:absolute; inset:0; pointer-events:none;
    background:radial-gradient(50rem 32rem at 50% -6%, color-mix(in srgb,var(--brand) 28%, transparent), transparent 60%); }
  .grid-dark{ background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px); background-size:60px 60px; -webkit-mask-image:radial-gradient(64% 56% at 50% 22%,#000,transparent); mask-image:radial-gradient(64% 56% at 50% 22%,#000,transparent); }
  .orb{ position:absolute; border-radius:50%; filter:blur(90px); opacity:.30; pointer-events:none; }
  .aurora{ position:absolute; inset:-20%; pointer-events:none; opacity:.5;
    background:conic-gradient(from 180deg at 50% 50%, color-mix(in srgb,var(--brand) 50%,transparent), transparent 30%, color-mix(in srgb,var(--brand2) 50%,transparent) 60%, transparent 80%, color-mix(in srgb,var(--brand) 50%,transparent));
    filter:blur(70px); animation:spin 24s linear infinite; }
  @keyframes spin{ to{ transform:rotate(360deg) } }

  /* Motion */
  .reveal{ opacity:0; transform:translateY(16px); transition:opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1); }
  .reveal.in{ opacity:1; transform:none; }
  .reveal-s{ opacity:0; transform:scale(.96); transition:opacity .7s cubic-bezier(.16,1,.3,1), transform .7s cubic-bezier(.16,1,.3,1); }
  .reveal-s.in{ opacity:1; transform:none; }
  .floaty{ animation:floaty 7s ease-in-out infinite; } @keyframes floaty{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
  .marquee{ display:flex; gap:2.5rem; animation:marq 34s linear infinite; }
  @keyframes marq{ to{ transform:translateX(-50%) } }
  .tilt{ transition:transform .25s cubic-bezier(.16,1,.3,1); transform-style:preserve-3d; }
  @media (prefers-reduced-motion:reduce){ *{ animation:none !important } .reveal,.reveal-s{ opacity:1; transform:none; transition:none } }

  /* ---- Dark mode (admin panel only; scoped to .panel-shell so the
        always-dark landing/auth pages are never affected) ---- */
  .dark .panel-shell{ background:#0B1120; }
  .dark .panel-shell .card{ background:#121A2B; border-color:rgba(255,255,255,.07); box-shadow:0 1px 3px rgba(0,0,0,.3), 0 10px 24px -18px rgba(0,0,0,.6); }
  .dark .panel-shell .icon-btn{ background:#121A2B; border-color:rgba(255,255,255,.08); color:#9aa6bd; }
  .dark .panel-shell .icon-btn:hover{ color:#fff; border-color:rgba(255,255,255,.18); }
  .dark .panel-shell .fld{ background:#0F1726; border-color:rgba(255,255,255,.10); color:#e8edf6; }
  .dark .panel-shell .fld::placeholder{ color:#5c6781; }
  .dark .panel-shell .fld:focus{ border-color:var(--brand); box-shadow:0 0 0 3px color-mix(in srgb,var(--brand) 24%, transparent); }
  .dark .panel-shell select.fld option{ background:#0F1726; color:#e8edf6; }
  .dark .panel-shell .k-btn-outline{ background:#121A2B; border-color:rgba(255,255,255,.12); color:#cdd5e4; }
  .dark .panel-shell .k-btn-outline:hover{ background:#1a2336; border-color:rgba(255,255,255,.2); }
  .dark .panel-shell .k-btn-light{ background:#1a2336; color:#fff; box-shadow:none; }
  .dark .panel-shell .k-btn-light:hover{ background:#222c43; }
  .dark .panel-shell .k-btn-ghost{ color:#9aa6bd; } .dark .panel-shell .k-btn-ghost:hover{ background:rgba(255,255,255,.06); color:#fff; }
  .dark .panel-shell .k-btn-dark{ background:#27314a; } .dark .panel-shell .k-btn-dark:hover{ background:#313c5a; }
  .dark .panel-shell .progress{ background:rgba(255,255,255,.08); }
  .dark .panel-shell .progress > i{ background:var(--brand); }
  .dark .panel-shell .seg{ background:rgba(255,255,255,.06); }
  .dark .panel-shell .seg label{ color:#9aa6bd; }
  .dark .panel-shell .seg input:checked + label{ background:#1a2336; color:#fff; box-shadow:none; }
  .dark .panel-shell .bg-paper{ background:#0F1726 !important; }
  .dark .panel-shell .bg-white:not(.dark\:bg-slate-900):not(.dark\:bg-slate-800){ background:#121A2B !important; }
  .dark .panel-shell .text-navy{ color:#e8edf6 !important; }
  .dark .panel-shell input[type=color]{ background:#0F1726; }
  .dark .panel-shell .hairline{ border-color:rgba(255,255,255,.08); }

  /* Segmented + dual range */
  .seg{ display:inline-flex; padding:3px; gap:2px; background:#EEF1F6; border-radius:.7rem; }
  .seg input{ position:absolute; opacity:0; pointer-events:none; }
  .seg label{ cursor:pointer; padding:6px 13px; border-radius:.55rem; font-size:13px; font-weight:600; color:#6b7385; transition:.15s; }
  .seg input:checked + label{ background:#fff; color:var(--navy); box-shadow:0 1px 2px rgba(28,36,51,.12); }
  .range-wrap{ position:relative; height:28px; }
  .range-track{ position:absolute; top:12px; left:0; right:0; height:4px; background:#E2E6EE; border-radius:99px; }
  .range-fill{ position:absolute; top:12px; height:4px; background:var(--brand); border-radius:99px; }
  .range-wrap input[type=range]{ position:absolute; top:0; left:0; width:100%; height:28px; margin:0; background:none; pointer-events:none; -webkit-appearance:none; appearance:none; }
  .range-wrap input[type=range]::-webkit-slider-thumb{ -webkit-appearance:none; pointer-events:auto; width:16px; height:16px; border-radius:50%; background:#fff; border:2px solid var(--brand); box-shadow:0 1px 3px rgba(28,36,51,.25); cursor:pointer; }
  .range-wrap input[type=range]::-moz-range-thumb{ pointer-events:auto; width:16px; height:16px; border-radius:50%; background:#fff; border:2px solid var(--brand); cursor:pointer; }
</style>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    var els=document.querySelectorAll('.reveal,.reveal-s');
    if(!('IntersectionObserver' in window)){ els.forEach(e=>e.classList.add('in')); return; }
    var io=new IntersectionObserver(function(es){ es.forEach(function(en){ if(en.isIntersecting){ en.target.classList.add('in'); io.unobserve(en.target);} }); },{threshold:.12});
    els.forEach(function(e,i){ e.style.transitionDelay=(Math.min(i,6)*55)+'ms'; io.observe(e); });
    // count-up
    document.querySelectorAll('[data-count]').forEach(function(el){
      var io2=new IntersectionObserver(function(es){ es.forEach(function(en){ if(!en.isIntersecting) return; io2.unobserve(el);
        var end=parseFloat(el.getAttribute('data-count')), pre=el.getAttribute('data-pre')||'', suf=el.getAttribute('data-suf')||'', dec=parseInt(el.getAttribute('data-dec')||'0'), t0=null, dur=1400;
        function step(ts){ if(!t0)t0=ts; var p=Math.min((ts-t0)/dur,1); var e=1-Math.pow(1-p,3); var v=end*e; el.textContent=pre+v.toLocaleString('en-US',{minimumFractionDigits:dec,maximumFractionDigits:dec})+suf; if(p<1)requestAnimationFrame(step);} requestAnimationFrame(step);
      }); },{threshold:.5}); io2.observe(el);
    });
  });
</script>
