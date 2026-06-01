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

// Space-separated RGB channels for the self-hosted Tailwind build, which
// resolves brand utilities as rgb(var(--brand-rgb) / <alpha>) so opacity
// modifiers (bg-brand/10, ring-brand/30, ...) keep working with the tenant color.
$hexToRgb = static function (string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
    if (strlen($hex) !== 6 || !ctype_xdigit($hex)) { return '242 54 69'; } // fallback: brand red
    return hexdec(substr($hex, 0, 2)) . ' ' . hexdec(substr($hex, 2, 2)) . ' ' . hexdec(substr($hex, 4, 2));
};
$accentRgb  = $hexToRgb($accent);
$accent2Rgb = $hexToRgb($accent2);
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?= e($title ?? 'Kyros Rent Car') ?></title>
<meta name="description" content="<?= e($metaDescription ?? 'Kyros Rent Car — software para administrar tu negocio de alquiler de vehiculos.') ?>">
<meta name="theme-color" content="#0E1422">
<?php
// ---- Open Graph / Twitter cards: rich link previews on WhatsApp, Instagram,
// Facebook, Telegram, X, etc. og:image MUST be an absolute URL. Crawlers cache
// by URL, so a ?v=<mtime> lets a re-scrape pick up a new image. ----
$ogTitle = $title ?? 'Kyros Rent Car — El sistema operativo de tu rent car';
$ogDesc  = $metaDescription ?? 'Flotilla, reservas online, contratos, pagos y tu pagina publica con tu marca — en una sola plataforma. Empieza gratis, sin tarjeta.';
$ogImgFile = rtrim((string) \App\Core\Config::get('app.root_path', ''), '/\\') . '/public/assets/img/og-image.png';
$ogImage   = abs_url('/assets/img/og-image.png') . (is_file($ogImgFile) ? '?v=' . filemtime($ogImgFile) : '');
$ogPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$ogBase = (string) \App\Core\Config::get('app.base_path', '');
if ($ogBase !== '' && str_starts_with($ogPath, $ogBase)) { $ogPath = substr($ogPath, strlen($ogBase)); }
$ogUrl = abs_url($ogPath);
?>
<link rel="canonical" href="<?= e($ogUrl) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Kyros Rent Car">
<meta property="og:locale" content="es_ES">
<meta property="og:title" content="<?= e($ogTitle) ?>">
<meta property="og:description" content="<?= e($ogDesc) ?>">
<meta property="og:url" content="<?= e($ogUrl) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:image:secure_url" content="<?= e($ogImage) ?>">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Kyros Rent Car — software para rent cars">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($ogTitle) ?>">
<meta name="twitter:description" content="<?= e($ogDesc) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<?php
// Inline SVG favicon — uses the resolved accent color so tenant-themed pages
// inherit the brand. Falls back to the Kyros red for marketing pages.
$faviconColor = ltrim($accent, '#');
$faviconSvg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#' . $faviconColor . '"/><path fill="#fff" d="M19 17h8.6l7 11.2L41.7 17H50L37.6 35.6 50.4 47H42l-7.6-12L26.7 47H19l13-19.2L19 17z"/></svg>');
?>
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<?= $faviconSvg ?>">
<link rel="apple-touch-icon" href="<?= asset('img/apple-touch-icon.png') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= asset('img/apple-touch-icon.png') ?>">

<?php /* ---- PWA: installable across the whole SaaS (admin, storefront, auth, marketing) ---- */ ?>
<link rel="manifest" href="<?= url('/manifest.webmanifest') ?>">
<meta name="application-name" content="Kyros Rent Car">
<meta name="apple-mobile-web-app-title" content="Kyros">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="msapplication-TileColor" content="#0E1422">
<meta name="msapplication-TileImage" content="<?= asset('img/icon-192.png') ?>">
<?php
// Self-hosted, pre-compiled Tailwind + variable fonts (Inter, Plus Jakarta
// Sans). Replaces the Tailwind Play CDN and Google Fonts so pages always load
// fully styled with no runtime CDN dependency. The brand accent stays dynamic
// via the CSS variables in the inline <style> below (--brand / --brand2).
// Rebuild after changing classes:  see build/README in repo notes.
?>
<link rel="stylesheet" href="<?= asset('css/tailwind.css') ?>">
<style>
  :root{ --brand:<?= e($accent) ?>; --brand2:<?= e($accent2) ?>; --brand-rgb:<?= $accentRgb ?>; --brand2-rgb:<?= $accent2Rgb ?>; --navy:<?= e($navy) ?>; --grad:linear-gradient(120deg,var(--brand),var(--brand2)); }
  *{ -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility; }
  html{ scroll-behavior:smooth; }
  body{ font-family:'Inter',sans-serif; letter-spacing:-.011em; }
  h1,h2,h3,h4,.font-display{ font-family:'Plus Jakarta Sans','Inter',sans-serif; letter-spacing:-.022em; text-wrap:balance; }
  .display-xl{ letter-spacing:-.035em; line-height:1.02; }
  /* Pretty wrap for body paragraphs prevents last-line orphans without affecting headlines */
  main p{ text-wrap:pretty; }
  [x-cloak]{ display:none !important; }
  .tnum{ font-variant-numeric:tabular-nums; font-feature-settings:'tnum'; }
  main table td, main table th{ font-variant-numeric:tabular-nums; }
  svg.lucide{ stroke-width:1.75; }

  /* ===== PWA safe-areas — when installed (standalone) the page extends under
     the status bar / Dynamic Island / home indicator. These keep fixed bars
     clear of the unsafe zones. env() resolves to 0 outside standalone, so the
     rules are inert in normal browsers. Hand-written (not Tailwind) so they
     work regardless of the compiled stylesheet. */
  .pwa-safe-top{ top: calc(env(safe-area-inset-top)) !important; }
  .pwa-safe-header{ padding-top: env(safe-area-inset-top);
                    height: calc(4rem + env(safe-area-inset-top)) !important; }
  .pwa-safe-pt{ padding-top: env(safe-area-inset-top); }

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

  /* =================== Responsive tables =====================
     Use: <table class="k-table"> with <td data-label="Columna">.
     Desktop = standard table. Mobile = each row becomes a card.
  */
  .k-table{ width:100%; border-collapse:collapse; font-size:14px; }
  .k-table th{ font-weight:500; color:#94a3b8; text-align:left; padding:.75rem 1.5rem; background:#F8FAFC; }
  .k-table td{ padding:.85rem 1.5rem; border-top:1px solid #EAEEF4; vertical-align:middle; }
  .k-table tbody tr:hover{ background:#F8FAFC; }
  .dark .panel-shell .k-table th{ background:rgba(255,255,255,.03); color:#7a849b; }
  .dark .panel-shell .k-table td{ border-color:rgba(255,255,255,.06); }
  .dark .panel-shell .k-table tbody tr:hover{ background:rgba(255,255,255,.025); }
  @media (max-width: 640px){
    .k-table{ display:block; }
    .k-table thead{ display:none; }
    .k-table tbody, .k-table tfoot{ display:block; }
    .k-table tr{ display:flex; flex-direction:column; padding:1rem 1.1rem; border-top:1px solid #EAEEF4; gap:.55rem; }
    .dark .panel-shell .k-table tr{ border-color:rgba(255,255,255,.06); }
    .k-table tr:hover{ background:transparent; }
    .k-table tr:first-child{ border-top:0; }
    .k-table td{ display:flex; justify-content:space-between; align-items:center; gap:1rem;
                 padding:0; border:0; min-height:24px; }
    .k-table td[data-label]::before{ content:attr(data-label); color:#94a3b8; font-size:11px;
                 font-weight:700; text-transform:uppercase; letter-spacing:.04em; flex-shrink:0; }
    .k-table td.k-td-actions{ justify-content:flex-end; padding-top:.5rem;
                 border-top:1px dashed #EAEEF4; margin-top:.25rem; }
    .dark .panel-shell .k-table td.k-td-actions{ border-color:rgba(255,255,255,.06); }
    .k-table td.k-td-actions::before{ display:none; }
    .k-table td.k-td-primary{ flex-direction:column; align-items:flex-start; gap:.15rem; }
    .k-table td.k-td-primary::before{ display:none; }
  }

  /* Sticky-actions bar — wrap form footers in <div class="k-sticky"> for
     a docked save/cancel toolbar on mobile that hovers above the bottom nav. */
  @media (max-width: 768px){
    .k-sticky{ position:sticky; bottom:0; z-index:20;
               background:rgba(255,255,255,.92); backdrop-filter:blur(12px);
               padding:.85rem 1rem; margin:0 -1rem -1rem;
               border-top:1px solid #EAEEF4; }
    .dark .panel-shell .k-sticky{ background:rgba(11,17,32,.92); border-color:rgba(255,255,255,.08); }
    .k-sticky .k-btn{ flex:1; min-height:46px; }
  }

  /* =================== Mobile bottom dock =====================
     5 quick actions visible only on mobile. */
  .mob-dock{ display:none; }
  @media (max-width: 1023px){
    .mob-dock{ display:grid; position:fixed; bottom:0; left:0; right:0; z-index:30;
               grid-template-columns:repeat(5, 1fr); gap:2px;
               background:rgba(255,255,255,.96); backdrop-filter:blur(20px);
               border-top:1px solid #EAEEF4; padding:.45rem .25rem calc(.45rem + env(safe-area-inset-bottom));
               box-shadow:0 -10px 30px -18px rgba(28,36,51,.25); }
    .dark .panel-shell .mob-dock{ background:rgba(11,17,32,.95); border-color:rgba(255,255,255,.08); }
    .mob-dock a, .mob-dock button{ display:flex; flex-direction:column; align-items:center;
               gap:2px; padding:.4rem .25rem; border-radius:.65rem; color:#5a6377;
               font-size:10.5px; font-weight:600; transition:.15s; min-height:48px; justify-content:center;
               background:transparent; border:0; cursor:pointer; }
    .mob-dock a.is-active{ color:var(--brand); background:color-mix(in srgb,var(--brand) 9%,transparent); }
    .mob-dock a:active, .mob-dock button:active{ transform:scale(.94); }
    .mob-dock svg{ width:20px; height:20px; }
    .mob-dock .mob-dock-fab{ position:relative; }
    .mob-dock .mob-dock-fab > span{ position:absolute; top:-22px; left:50%; transform:translateX(-50%);
               width:48px; height:48px; border-radius:50%; background:var(--brand); color:#fff;
               display:grid; place-items:center;
               box-shadow:0 10px 24px -8px color-mix(in srgb,var(--brand) 50%, transparent); }
    .mob-dock .mob-dock-fab > span svg{ width:22px; height:22px; }
    .mob-dock .mob-dock-fab > b{ visibility:hidden; }
    /* Pad main so it doesn't get hidden behind the dock */
    body.panel-shell main{ padding-bottom:5.5rem !important; }
    .dark .panel-shell .mob-dock a.is-active{ color:var(--brand); background:color-mix(in srgb,var(--brand) 16%,transparent); }
  }

  /* Tighter spacing on small screens */
  @media (max-width: 640px){
    body.panel-shell main{ padding-left:.85rem !important; padding-right:.85rem !important; padding-top:.85rem !important; }
    .card{ border-radius:.95rem; }
    .card.\!p-6, .card.p-6{ padding:1rem !important; }
    .card.\!p-5, .card.p-5{ padding:.9rem !important; }
  }

  /* Bigger touch targets on mobile */
  @media (max-width: 640px){
    .k-btn{ min-height:44px; padding:0 1rem; }
    .icon-btn{ min-width:42px; min-height:42px; }
    .fld{ min-height:44px; }
  }

  /* =====================================================================
     GLOBAL RESPONSIVE SAFETY NETS
     Cascading rules so EVERY page (admin / superadmin / storefront /
     auth / public) gets baseline responsive behavior without touching
     individual files. These do not override deliberate per-page choices
     — they catch the common breakage patterns:
       1. rogue wide elements pushing horizontal scroll
       2. raw <table> without .k-table responsive wrapping
       3. <pre>/<code> blocks overflowing
       4. images without responsive constraints
       5. action-button rows that don't stack on mobile
       6. sticky containers that overlap content under 768px
       7. modals that don't go full-screen on phones
       8. flex rows of buttons that don't wrap
     ===================================================================== */

  /* (1) Horizontal-scroll safety: any element wider than viewport gets
     clipped at body level. Catches absolute children, wide images, long
     unbreakable tokens, etc. We use overflow-x:clip (modern, no scroll)
     with overflow-x:hidden fallback. */
  html, body{ overflow-x:clip; max-width:100vw; }
  @supports not (overflow-x:clip){ html, body{ overflow-x:hidden; } }

  /* (2) Images & media: never overflow their container */
  img, video, picture, svg{ max-width:100%; height:auto; }
  img[width][height]{ height:auto; } /* respect explicit width but auto-scale height */

  /* (3) Code / pre: scrollable horizontally if longer than container */
  pre, code{ max-width:100%; overflow-x:auto; word-wrap:break-word; }
  pre{ white-space:pre-wrap; word-break:break-word; }

  /* (4) Raw <table> safety: when a page uses a vanilla table (not the
     responsive .k-table), wrap-style overflow so it scrolls horizontally
     instead of pushing the body wide. Pages that wrap tables in their
     own .overflow-x-auto container are unaffected. */
  table:not(.k-table){ max-width:100%; }
  .table-wrap, .overflow-x-auto{ -webkit-overflow-scrolling:touch; }

  /* (5) Word-break safety for unbreakable strings (URLs, codes, JSON) */
  .break-anywhere{ overflow-wrap:anywhere; word-break:break-word; }

  /* (6) Form action footers: when a `.card` contains a row of buttons at
     the bottom, make them wrap and stretch on mobile so a "Guardar /
     Cancelar" pair doesn't squeeze into 60px each. Detection: a flex
     container that is a direct child of .card OR sits right after the
     card. */
  @media (max-width: 640px){
    .card .flex.items-center.gap-3 > .k-btn,
    .card .flex.gap-3 > .k-btn,
    .card .flex.items-center.gap-2 > .k-btn,
    .card .flex.gap-2 > .k-btn,
    form .flex.items-center.gap-3 > .k-btn,
    form .flex.gap-3 > .k-btn{
      flex:1 1 auto; min-width:140px;
    }
    /* Stack buttons vertically when there are more than 2 to avoid wrap chaos */
    .card .flex.items-center.gap-3:has(> .k-btn:nth-child(3)),
    form .flex.items-center.gap-3:has(> .k-btn:nth-child(3)){
      flex-direction:column; align-items:stretch;
    }
  }

  /* (7) Sticky containers become static on mobile so they don't
     overlap content or block the viewport. The reservation form's
     "Resumen" sidebar is the canonical case. */
  @media (max-width: 1023px){
    .sticky{ position:static !important; top:auto !important; }
  }

  /* (8) Generic modals/dialogs: ensure full-width on phones, with
     respected safe-area padding. Targets common Alpine modal patterns. */
  @media (max-width: 640px){
    [x-show][class*="fixed"][class*="left-1/2"][class*="top-1/2"]{
      width:calc(100vw - 1rem) !important;
      max-width:none !important;
      max-height:calc(100dvh - 2rem) !important;
      overflow-y:auto;
    }
  }

  /* (9) Long form labels never wrap awkwardly */
  label{ overflow-wrap:break-word; }

  /* (10) Number/currency cells never overflow on narrow mobile cards */
  .tnum{ overflow-wrap:normal; word-break:keep-all; }

  /* (11) Page heading rows: when a page has H1 + action-buttons in a
     flex-row, ensure the buttons wrap below the title on mobile if the
     page didn't already use flex-col sm:flex-row. */
  @media (max-width: 640px){
    .panel-shell main > .flex.items-center.justify-between:first-child,
    .panel-shell main > div > .flex.items-center.justify-between:first-child{
      flex-wrap:wrap; gap:.75rem;
    }
  }

  /* (12) Inputs with type=date/time on iOS Safari: force consistent height */
  input[type="date"], input[type="time"], input[type="datetime-local"]{
    -webkit-appearance:none; appearance:none;
    min-height:42px;
  }

  /* (13) Select on mobile: prevent zoom-on-focus by forcing 16px min */
  @media (max-width: 640px){
    select.fld, input.fld, textarea.fld{ font-size:16px; }
  }

  /* (14) Card grids: any grid with 3+ columns falls back gracefully */
  @media (max-width: 640px){
    .grid.grid-cols-3:not(.sm\:grid-cols-3):not(.md\:grid-cols-3),
    .grid.grid-cols-4:not(.sm\:grid-cols-4):not(.md\:grid-cols-4){
      grid-template-columns:repeat(2, minmax(0, 1fr));
    }
  }

  /* (15) Floating chips/pills row: always allow horizontal scroll if
     they would overflow */
  .chip-row{ display:flex; gap:.5rem; overflow-x:auto; padding-bottom:.25rem;
             scrollbar-width:thin; -webkit-overflow-scrolling:touch; }
  .chip-row::-webkit-scrollbar{ height:4px; }

  /* Truncation utility for long single-line content */
  .truncate-2{ display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

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
    // The marketing layout opts into a GSAP+ScrollTrigger driven system. When
    // it does, this legacy IntersectionObserver stands down so the two don't
    // double-animate. Admin/auth pages keep using this lightweight fallback.
    if (window.__USE_GSAP__) return;
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
<?php /* ---- PWA runtime: register the service worker + expose a global install API ---- */ ?>
<script>
(function(){
  // window.KyrosPWA — single source of truth for the install UI (landing button,
  // in-app banners). Emits DOM events so any page can react without coupling.
  var api = {
    deferred: null,
    canInstall: false,
    isStandalone: window.matchMedia('(display-mode: standalone)').matches
                  || window.matchMedia('(display-mode: window-controls-overlay)').matches
                  || window.navigator.standalone === true,
    isIOS: /iphone|ipad|ipod/i.test(window.navigator.userAgent) && !window.MSStream,
    // Returns a promise resolving to 'accepted' | 'dismissed' | 'unavailable'.
    promptInstall: function(){
      if (!api.deferred) return Promise.resolve('unavailable');
      var d = api.deferred;
      api.deferred = null; api.canInstall = false;
      d.prompt();
      return d.userChoice.then(function(c){ return (c && c.outcome) || 'dismissed'; });
    }
  };
  api.isIOSInstallable = api.isIOS && !api.isStandalone;
  window.KyrosPWA = api;

  function emit(name){ window.dispatchEvent(new CustomEvent(name)); }

  window.addEventListener('beforeinstallprompt', function(e){
    e.preventDefault();              // suppress the mini-infobar; we drive the UI
    api.deferred = e;
    api.canInstall = true;
    emit('kyros:installable');
  });

  window.addEventListener('appinstalled', function(){
    api.deferred = null;
    api.canInstall = false;
    emit('kyros:installed');
  });

  // Register the service worker (secure context only: https or localhost).
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function(){
      var scope = <?= json_encode(rtrim(url('/'), '/') . '/') ?>;
      navigator.serviceWorker.register(<?= json_encode(url('/sw.js')) ?>, { scope: scope })
        .then(function(reg){
          // Surface waiting updates so a future enhancement can show "refresh".
          reg.addEventListener('updatefound', function(){
            var sw = reg.installing;
            if (!sw) return;
            sw.addEventListener('statechange', function(){
              if (sw.state === 'installed' && navigator.serviceWorker.controller) {
                emit('kyros:update-available');
              }
            });
          });
        })
        .catch(function(){ /* SW optional — app works without it */ });
    });
  }
})();
</script>
