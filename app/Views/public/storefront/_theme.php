<?php
/**
 * Storefront "Lux" design system — dark, editorial, luxury rental aesthetic
 * inspired by premiumrentcar.com, but fully themed by the tenant's accent.
 * --brand / --brand2 are injected per-tenant in layouts/_assets.php, so this
 * sheet only defines the neutral dark chrome + components and lets the accent
 * flow through color-mix(). Everything is scoped to the .lux body class so the
 * marketing site and admin panel are never affected.
 *
 * Rendered ONCE per storefront page (from _nav.php). Expects $tenant.
 */
$primaryHex = $tenant['primary_color'] ?? '#4F46E5';
$h = ltrim((string) $primaryHex, '#');
if (strlen($h) === 3) { $h = $h[0].$h[0].$h[1].$h[1].$h[2].$h[2]; }
if (strlen($h) !== 6 || !ctype_xdigit($h)) { $h = '4F46E5'; }
$r = hexdec(substr($h, 0, 2)); $g = hexdec(substr($h, 2, 2)); $b = hexdec(substr($h, 4, 2));

// WCAG relative luminance + contrast helpers — so the storefront stays legible
// for ANY tenant accent (bright, dark, or low-saturation), not just vivid ones.
$srgb     = static fn($c) => ($c /= 255) <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
$lumOf    = static fn($rr, $gg, $bb) => 0.2126 * $srgb($rr) + 0.7152 * $srgb($gg) + 0.0722 * $srgb($bb);
$contrast = static fn($l1, $l2) => (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);

$L   = $lumOf($r, $g, $b);
$Lbg = $lumOf(10, 10, 10); // #0a0a0a — the storefront base

// (a) Text placed ON the accent fill (buttons/chips): pick the ink with higher contrast.
$brandInk = $contrast($L, 1.0) >= $contrast($L, $Lbg) ? '#ffffff' : '#0a0a0a';

// (b) Accent used as FOREGROUND text on the dark chrome (headlines, prices, codes, icons):
//     lighten the accent toward white until it clears ~AA (4.5:1) over #0a0a0a.
$mix = 0; $Lc = $L;
while ($contrast($Lc, $Lbg) < 4.5 && $mix < 100) {
    $mix += 5; $f = $mix / 100;
    $Lc = $lumOf($r + (255 - $r) * $f, $g + (255 - $g) * $f, $b + (255 - $b) * $f);
}
$brandText = $mix === 0 ? 'var(--brand)' : 'color-mix(in srgb, var(--brand) ' . (100 - $mix) . '%, #fff)';
?>
<style>
/* ============================ KYROS · LUX STOREFRONT ============================ */
.lux{
  --lux-bg:#0a0a0a; --lux-surface:#141414; --lux-surface-2:#1a1a1a; --lux-elevated:#1f1f1f;
  --lux-border:#262626; --lux-border-2:#363636;
  --lux-fg:#ffffff; --lux-muted:#a6a6a6; --lux-dim:#8f8f8f;
  --lux-ink:<?= $brandInk ?>; --lux-brand-text:<?= $brandText ?>;
  --lux-brand-soft:color-mix(in srgb, var(--brand) 14%, transparent);
  --lux-glow:0 0 60px -12px color-mix(in srgb, var(--brand) 60%, transparent);
  --lux-lift:0 40px 80px -40px rgba(0,0,0,.9);
  background:var(--lux-bg); color:var(--lux-fg);
  font-family:'Plus Jakarta Sans','Inter',sans-serif; letter-spacing:-.011em;
  -webkit-font-smoothing:antialiased;
}
.lux ::selection{ background:var(--brand); color:var(--lux-ink); }
.lux h1,.lux h2,.lux h3,.lux h4{ font-family:'Plus Jakarta Sans','Inter',sans-serif; text-wrap:balance; }
.lux a{ -webkit-tap-highlight-color:transparent; }
.lux .tnum{ font-variant-numeric:tabular-nums; }

/* --- Eyebrow label: "—— SECTION" --- */
.lux-eyebrow{ display:inline-flex; align-items:center; gap:.7rem; font-size:11px; font-weight:800;
  letter-spacing:.24em; text-transform:uppercase; color:var(--lux-brand-text); }
.lux-eyebrow::before{ content:""; width:30px; height:1.5px; background:var(--lux-brand-text); opacity:.85;
  border-radius:2px; }
.lux-eyebrow.is-muted{ color:var(--lux-muted); } .lux-eyebrow.is-muted::before{ background:var(--lux-muted); opacity:.5; }

/* --- Surfaces --- */
.lux-surface{ background:var(--lux-surface); border:1px solid var(--lux-border); }
.lux-card{ background:var(--lux-surface); border:1px solid var(--lux-border); border-radius:20px;
  transition:transform .35s cubic-bezier(.16,1,.3,1), border-color .35s, box-shadow .35s; }
.lux-card:hover{ transform:translateY(-5px);
  border-color:color-mix(in srgb, var(--brand) 55%, var(--lux-border));
  box-shadow:var(--lux-lift), 0 0 0 1px color-mix(in srgb, var(--brand) 20%, transparent); }
.lux-hairline{ border-color:var(--lux-border) !important; }

/* --- Buttons (pill) --- */
.lux-btn{ display:inline-flex; align-items:center; justify-content:center; gap:.55rem;
  font-weight:700; font-size:14px; line-height:1; border-radius:999px; padding:.95rem 1.5rem;
  border:1px solid transparent; cursor:pointer; white-space:nowrap;
  transition:transform .2s cubic-bezier(.16,1,.3,1), background .2s, color .2s, box-shadow .2s, border-color .2s; }
.lux-btn:active{ transform:translateY(1px) scale(.99); }
.lux-btn-brand{ background:var(--brand); color:var(--lux-ink); box-shadow:var(--lux-glow); }
.lux-btn-brand:hover{ transform:translateY(-2px); filter:brightness(1.07);
  box-shadow:0 0 80px -10px color-mix(in srgb, var(--brand) 75%, transparent); }
.lux-btn-light{ background:#fff; color:#0a0a0a; }
.lux-btn-light:hover{ transform:translateY(-2px); background:#ececec; }
.lux-btn-outline{ border-color:var(--lux-border-2); color:#fff; background:transparent; backdrop-filter:blur(8px); }
.lux-btn-outline:hover{ border-color:#5a5a5a; background:rgba(255,255,255,.05); }
.lux-btn-ghost{ color:var(--lux-muted); }
.lux-btn-ghost:hover{ color:#fff; }
.lux-btn-wa{ background:#25D366; color:#04210f; } .lux-btn-wa:hover{ transform:translateY(-2px); filter:brightness(1.05); }
.lux-btn-sm{ padding:.6rem 1.1rem; font-size:13px; }
/* Small accent buttons (per-card "Reservar") drop the hero-scale halo — the 60px glow
   stays reserved for the real primary CTAs so the grids don't read as a wall of glow. */
.lux-btn-sm.lux-btn-brand{ box-shadow:0 6px 18px -12px color-mix(in srgb, var(--brand) 50%, transparent); }
.lux-btn-sm.lux-btn-brand:hover{ box-shadow:0 10px 22px -12px color-mix(in srgb, var(--brand) 60%, transparent); }

/* Keyboard focus — visible brand ring on the dark shell (custom controls have no
   default-outline affordance that reads well on near-black). */
.lux a:focus-visible, .lux button:focus-visible, .lux .lux-btn:focus-visible, .lux [tabindex]:focus-visible{
  outline:2px solid var(--lux-brand-text); outline-offset:2px; border-radius:10px; }

/* --- Chips / pills --- */
.lux-chip{ display:inline-flex; align-items:center; gap:.45rem; padding:.4rem .8rem; border-radius:999px;
  font-size:12px; font-weight:600; background:var(--lux-surface-2); border:1px solid var(--lux-border);
  color:var(--lux-muted); }
.lux-chip-brand{ background:var(--lux-brand-soft); border-color:color-mix(in srgb,var(--brand) 35%,transparent);
  color:var(--lux-brand-text); }

/* --- Form fields (dark) --- */
.lux-field{ width:100%; height:48px; padding:0 .95rem; font-size:14px; border-radius:12px;
  background:var(--lux-surface-2); border:1px solid var(--lux-border-2); color:#fff; outline:none;
  transition:.16s; color-scheme:dark; }
.lux textarea.lux-field{ height:auto; padding:.7rem .95rem; }
.lux select.lux-field{ padding-right:2rem; }
.lux select.lux-field option{ background:#141414; color:#fff; }
.lux-field:focus{ border-color:var(--lux-brand-text);
  box-shadow:0 0 0 3px color-mix(in srgb, var(--lux-brand-text) 26%, transparent); }
.lux-field::placeholder{ color:var(--lux-dim); }
.lux input[type=date].lux-field, .lux input[type=time].lux-field{ color-scheme:dark; }

/* --- Segmented control (dark) --- */
.lux .seg{ background:var(--lux-surface-2); border:1px solid var(--lux-border); }
.lux .seg label{ color:var(--lux-muted); }
.lux .seg input:checked + label{ background:var(--brand); color:var(--lux-ink); box-shadow:none; }

/* --- Dual range (dark, brand fill) — ≥24px touch target with a small visible dot --- */
.lux .range-wrap{ height:36px; }
.lux .range-track, .lux .range-fill{ top:16px; }
.lux .range-wrap input[type=range]{ height:36px; }
.lux .range-wrap input[type=range]::-webkit-slider-thumb{ width:28px; height:28px; border:none; box-shadow:none;
  background:radial-gradient(circle, #fff 0 7px, color-mix(in srgb,var(--brand) 70%, #fff) 8px 9px, transparent 10px); }
.lux .range-wrap input[type=range]::-moz-range-thumb{ width:28px; height:28px; border:none; box-shadow:none;
  background:radial-gradient(circle, #fff 0 7px, color-mix(in srgb,var(--brand) 70%, #fff) 8px 9px, transparent 10px); }

/* --- Decorative glow orbs / grid --- */
.lux-orb{ position:absolute; border-radius:50%; filter:blur(110px); pointer-events:none;
  background:var(--brand); opacity:.16; }
.lux-grid{ background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),
  linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px); background-size:64px 64px;
  -webkit-mask-image:radial-gradient(70% 60% at 50% 0%,#000,transparent);
  mask-image:radial-gradient(70% 60% at 50% 0%,#000,transparent); }
.lux-noise{ background-image:radial-gradient(circle at 1px 1px, rgba(255,255,255,.04) 1px, transparent 0);
  background-size:4px 4px; }

/* --- Giant watermark numeral (spotlight) --- */
.lux-watermark{ font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; line-height:.8;
  color:transparent; -webkit-text-stroke:1.5px var(--lux-border-2);
  letter-spacing:-.04em; user-select:none; pointer-events:none; }

/* --- Link underline sweep --- */
.lux-link{ position:relative; color:#fff; font-weight:700; }
.lux-link::after{ content:""; position:absolute; left:0; bottom:-3px; height:1.5px; width:100%;
  background:var(--brand); transform:scaleX(0); transform-origin:left; transition:transform .3s ease; }
.lux-link:hover::after{ transform:scaleX(1); }

/* --- Reveal-on-scroll for AOS fallback already loaded; keep cards crisp --- */
.lux img{ -webkit-user-drag:none; }

/* --- Scrollbar inside the dark shell --- */
.lux ::-webkit-scrollbar{ width:10px; height:10px; }
.lux ::-webkit-scrollbar-thumb{ background:#2a2a2a; border-radius:8px; border:3px solid transparent; background-clip:content-box; }
.lux ::-webkit-scrollbar-thumb:hover{ background:#3a3a3a; background-clip:content-box; }

@media (prefers-reduced-motion:reduce){
  .lux-card:hover{ transform:none; } .lux-btn:hover{ transform:none; }
}
</style>
