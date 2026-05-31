<?php /** Storefront top nav. Expects $tenant. */ ?>
<header class="sticky top-0 z-30 border-b border-white/10 text-white backdrop-blur-xl" style="background:rgba(16,22,32,.94)">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 h-[74px] flex items-center justify-between gap-4">
    <a href="<?= url('/r/' . $tenant['slug']) ?>" class="flex min-w-0 items-center gap-3">
      <?php if (!empty($tenant['logo'])): ?>
        <span class="grid h-11 w-11 place-items-center overflow-hidden bg-white">
          <img src="<?= e(media($tenant['logo'])) ?>" alt="<?= e($tenant['name']) ?>" class="max-h-9 w-auto">
        </span>
      <?php else: ?>
        <span class="grid h-11 w-11 place-items-center text-white font-black text-lg" style="background:var(--brand)"><?= e(mb_substr($tenant['name'],0,1)) ?></span>
      <?php endif; ?>
      <span class="leading-tight min-w-0 hidden sm:block">
        <span class="font-display font-black tracking-[-.03em] text-white text-[17px] block truncate"><?= e($tenant['name']) ?></span>
        <span class="text-[11px] uppercase tracking-[.18em] text-white/45">Renta de vehiculos</span>
      </span>
    </a>

    <nav class="hidden lg:flex items-center gap-7 text-[12px] font-bold uppercase tracking-[.16em] text-white/62">
      <a href="<?= url('/r/'.$tenant['slug'].'#inicio') ?>" class="hover:text-white transition">Inicio</a>
      <a href="<?= url('/r/'.$tenant['slug'].'#marcas') ?>" class="hover:text-white transition">Marcas</a>
      <a href="<?= url('/r/'.$tenant['slug'].'#catalogo') ?>" class="hover:text-white transition">Catalogo</a>
      <a href="<?= url('/r/'.$tenant['slug'].'#nosotros') ?>" class="hover:text-white transition">Servicio</a>
      <a href="<?= url('/r/'.$tenant['slug'].'#contacto') ?>" class="hover:text-white transition">Contacto</a>
    </nav>

    <div class="flex items-center gap-2">
      <?php if (!empty($tenant['phone'])): ?>
      <a href="tel:<?= e(preg_replace('/\s+/','',$tenant['phone'])) ?>" class="hidden sm:inline-flex items-center gap-2 border border-white/14 px-4 py-2.5 text-sm font-bold text-white/86 transition hover:bg-white/8 hover:text-white">
        <i data-lucide="phone" class="w-4 h-4"></i> Llamar
      </a>
      <?php endif; ?>
      <?php if (!empty($tenant['whatsapp'])): ?>
      <a href="<?= e(whatsapp_link($tenant['whatsapp'], 'Hola ' . $tenant['name'] . ', quiero informacion sobre alquiler de vehiculos.')) ?>" target="_blank"
         class="inline-flex items-center gap-2 px-3 sm:px-4 py-2.5 text-sm font-bold text-white shadow-card transition hover:-translate-y-0.5 hover:opacity-95" style="background:var(--brand)">
        <i data-lucide="message-circle" class="w-4 h-4"></i> <span class="hidden sm:inline">WhatsApp</span>
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>
