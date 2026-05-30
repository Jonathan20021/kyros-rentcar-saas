<?php /** Storefront top nav. Expects $tenant. */ ?>
<header class="sticky top-0 z-30 bg-white/85 backdrop-blur-xl border-b hairline">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 h-[68px] flex items-center justify-between">
    <a href="<?= url('/r/' . $tenant['slug']) ?>" class="flex items-center gap-3">
      <?php if (!empty($tenant['logo'])): ?>
        <img src="<?= e(media($tenant['logo'])) ?>" alt="<?= e($tenant['name']) ?>" class="h-9 w-auto">
      <?php else: ?>
        <div class="w-10 h-10 rounded-2xl grid place-items-center text-white font-black text-lg" style="background:linear-gradient(135deg,<?= e($tenant['primary_color']) ?>,<?= e($tenant['secondary_color']) ?>)"><?= e(mb_substr($tenant['name'],0,1)) ?></div>
      <?php endif; ?>
      <div class="leading-tight">
        <span class="font-display font-extrabold text-ink text-[17px] block"><?= e($tenant['name']) ?></span>
        <span class="text-[11px] text-slate-400">Renta de vehiculos</span>
      </div>
    </a>
    <div class="flex items-center gap-2">
      <?php if (!empty($tenant['phone'])): ?>
      <a href="tel:<?= e(preg_replace('/\s+/','',$tenant['phone'])) ?>" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border hairline text-sm font-semibold text-ink hover:bg-slate-50 transition">
        <i data-lucide="phone" class="w-4 h-4"></i> Llamar
      </a>
      <?php endif; ?>
      <?php if (!empty($tenant['whatsapp'])): ?>
      <a href="<?= e(whatsapp_link($tenant['whatsapp'], 'Hola ' . $tenant['name'] . ', quiero informacion sobre alquiler de vehiculos.')) ?>" target="_blank"
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-semibold shadow-card hover:opacity-90 transition" style="background:#0B1220">
        <i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>
