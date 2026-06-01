<?php
/** Storefront top nav — Lux (dark). Expects $tenant. Renders the design system once. */
use App\Core\View;
echo View::renderPartial('public/storefront/_theme', ['tenant' => $tenant]);
$base = url('/r/' . $tenant['slug']);
$links = [
  ['#inicio', 'Inicio'],
  ['#marcas', 'Marcas'],
  ['#flota', 'Flota'],
  ['#servicio', 'Servicio'],
  ['#contacto', 'Contacto'],
];
?>
<header x-data="{ open:false, scrolled:false }"
        @scroll.window="scrolled = window.scrollY > 24"
        class="sticky top-0 z-40 transition-colors duration-300"
        :class="scrolled ? 'border-b border-[#262626]' : 'border-b border-transparent'"
        style="background:rgba(10,10,10,.78); backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px)">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 h-[76px] flex items-center justify-between gap-4">
    <a href="<?= e($base) ?>" class="flex min-w-0 items-center gap-3">
      <?php if (!empty($tenant['logo'])): ?>
        <span class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-xl bg-white">
          <img src="<?= e(media($tenant['logo'])) ?>" alt="<?= e($tenant['name']) ?>" class="max-h-9 w-auto">
        </span>
      <?php else: ?>
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl font-black text-lg" style="background:var(--brand); color:var(--lux-ink)"><?= e(mb_substr($tenant['name'],0,1)) ?></span>
      <?php endif; ?>
      <span class="leading-tight min-w-0 hidden sm:block">
        <span class="font-display font-extrabold tracking-[-.03em] text-white text-[17px] block truncate"><?= e($tenant['name']) ?></span>
        <span class="text-[10px] uppercase tracking-[.22em] text-[var(--lux-dim)]">Renta de vehículos</span>
      </span>
    </a>

    <nav class="hidden lg:flex items-center gap-9 text-[12px] font-bold uppercase tracking-[.16em] text-[var(--lux-muted)]">
      <?php foreach ($links as $l): ?>
        <a href="<?= e($base . $l[0]) ?>" class="hover:text-white transition-colors"><?= e($l[1]) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="flex items-center gap-2.5">
      <?php if (!empty($tenant['phone'])): ?>
      <a href="tel:<?= e(preg_replace('/\s+/','',$tenant['phone'])) ?>" class="hidden md:inline-flex lux-btn lux-btn-outline lux-btn-sm">
        <i data-lucide="phone" class="w-4 h-4"></i> Llamar
      </a>
      <?php endif; ?>
      <a href="<?= e($base) ?>#flota" class="hidden sm:inline-flex lux-btn lux-btn-brand lux-btn-sm">
        Reservar <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
      </a>
      <button @click="open = !open" class="lg:hidden grid h-11 w-11 place-items-center rounded-xl border border-[#363636] text-white" aria-label="Menú">
        <i data-lucide="menu" class="w-5 h-5" x-show="!open"></i>
        <i data-lucide="x" class="w-5 h-5" x-show="open" x-cloak></i>
      </button>
    </div>
  </div>

  <!-- Mobile drawer -->
  <div x-show="open" x-cloak x-transition.opacity
       class="lg:hidden border-t border-[#262626]" style="background:rgba(10,10,10,.98)">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex flex-col">
      <?php foreach ($links as $l): ?>
        <a href="<?= e($base . $l[0]) ?>" @click="open=false" class="py-3 text-sm font-bold uppercase tracking-[.14em] text-[var(--lux-muted)] hover:text-white border-b border-[#1c1c1c]"><?= e($l[1]) ?></a>
      <?php endforeach; ?>
      <div class="mt-4 grid grid-cols-2 gap-2.5">
        <?php if (!empty($tenant['phone'])): ?>
        <a href="tel:<?= e(preg_replace('/\s+/','',$tenant['phone'])) ?>" class="lux-btn lux-btn-outline lux-btn-sm"><i data-lucide="phone" class="w-4 h-4"></i> Llamar</a>
        <?php endif; ?>
        <?php if (!empty($tenant['whatsapp'])): ?>
        <a href="<?= e(whatsapp_link($tenant['whatsapp'], 'Hola ' . $tenant['name'] . ', quiero información sobre alquiler de vehículos.')) ?>" target="_blank" class="lux-btn lux-btn-wa lux-btn-sm"><i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp</a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>
