<?php
/** Storefront footer — Lux (dark). Expects $tenant. */
$base = url('/r/' . $tenant['slug']);
$methods = [
  ['banknote', 'Efectivo'],
  ['credit-card', 'Tarjeta'],
  ['arrow-left-right', 'Transferencia'],
  ['message-circle', 'WhatsApp'],
];
?>
<footer id="contacto" class="relative overflow-hidden border-t border-[#1c1c1c]" style="background:#080808">
  <div class="lux-orb" style="width:520px;height:520px;left:-160px;bottom:-220px;opacity:.10"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-16 lg:py-20">
    <div class="grid gap-12 lg:grid-cols-[1.3fr_.8fr_.9fr]">
      <!-- Brand -->
      <div>
        <div class="flex items-center gap-3 mb-6">
          <?php if (!empty($tenant['logo'])): ?>
            <span class="grid h-12 w-12 place-items-center overflow-hidden rounded-xl bg-white">
              <img src="<?= e(media($tenant['logo'])) ?>" alt="<?= e($tenant['name']) ?>" class="max-h-9 w-auto">
            </span>
          <?php else: ?>
            <span class="grid h-12 w-12 place-items-center rounded-xl font-black text-lg" style="background:var(--brand); color:var(--lux-ink)"><?= e(mb_substr($tenant['name'],0,1)) ?></span>
          <?php endif; ?>
          <span class="font-display font-extrabold tracking-[-.03em] text-white text-xl"><?= e($tenant['name']) ?></span>
        </div>
        <p class="max-w-md text-[15px] leading-relaxed text-[var(--lux-muted)]"><?= e($tenant['description'] ?? 'Renta de vehículos con reserva rápida, flota inspeccionada y asistencia directa.') ?></p>
        <div class="mt-7 flex flex-wrap items-center gap-3">
          <a href="<?= e($base) ?>#flota" class="lux-btn lux-btn-light">Explorar flota <i data-lucide="arrow-up-right" class="h-4 w-4"></i></a>
          <?php if (!empty($tenant['whatsapp'])): ?>
          <a href="<?= e(whatsapp_link($tenant['whatsapp'])) ?>" target="_blank" class="lux-btn lux-btn-wa"><i data-lucide="message-circle" class="h-4 w-4"></i> Escríbenos</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Contact -->
      <div>
        <h4 class="mb-5 text-[11px] font-extrabold uppercase tracking-[.24em] text-[var(--lux-dim)]">Contacto</h4>
        <ul class="space-y-4 text-[15px] text-[var(--lux-muted)]">
          <?php if (!empty($tenant['phone'])): ?><li><a href="tel:<?= e(preg_replace('/\s+/','',$tenant['phone'])) ?>" class="flex items-center gap-3 hover:text-white transition-colors"><i data-lucide="phone" class="w-4 h-4 text-[var(--brand)]"></i> <?= e($tenant['phone']) ?></a></li><?php endif; ?>
          <?php if (!empty($tenant['email'])): ?><li><a href="mailto:<?= e($tenant['email']) ?>" class="flex items-center gap-3 hover:text-white transition-colors"><i data-lucide="mail" class="w-4 h-4 text-[var(--brand)]"></i> <?= e($tenant['email']) ?></a></li><?php endif; ?>
          <?php if (!empty($tenant['address'])): ?><li class="flex items-start gap-3"><i data-lucide="map-pin" class="mt-0.5 w-4 h-4 text-[var(--brand)]"></i> <span><?= e($tenant['address']) ?></span></li><?php endif; ?>
        </ul>
      </div>

      <!-- Methods + nav -->
      <div>
        <h4 class="mb-5 text-[11px] font-extrabold uppercase tracking-[.24em] text-[var(--lux-dim)]">Métodos de pago</h4>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($methods as $m): ?>
            <span class="lux-chip"><i data-lucide="<?= $m[0] ?>" class="w-3.5 h-3.5"></i><?= e($m[1]) ?></span>
          <?php endforeach; ?>
        </div>
        <h4 class="mt-8 mb-4 text-[11px] font-extrabold uppercase tracking-[.24em] text-[var(--lux-dim)]">Navegación</h4>
        <ul class="grid grid-cols-2 gap-2 text-[14px] text-[var(--lux-muted)]">
          <li><a href="<?= e($base) ?>#inicio" class="hover:text-white transition-colors">Inicio</a></li>
          <li><a href="<?= e($base) ?>#flota" class="hover:text-white transition-colors">Flota</a></li>
          <li><a href="<?= e($base) ?>#marcas" class="hover:text-white transition-colors">Marcas</a></li>
          <li><a href="<?= e($base) ?>#servicio" class="hover:text-white transition-colors">Servicio</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="border-t border-[#1c1c1c]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[var(--lux-dim)]">
      <p>&copy; <?= date('Y') ?> <?= e($tenant['name']) ?>. Todos los derechos reservados.</p>
      <div class="flex items-center gap-5">
        <a href="#top" class="flex items-center gap-1.5 hover:text-white transition-colors">Volver arriba <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i></a>
        <span class="flex items-center gap-1.5">Powered by <span class="font-display font-bold text-[var(--lux-muted)]">Kyros</span></span>
      </div>
    </div>
  </div>
</footer>
