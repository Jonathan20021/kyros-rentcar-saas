<?php /** Storefront footer. Expects $tenant. */ ?>
<footer class="bg-[#0B1018] text-slate-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 lg:py-16">
    <div class="grid gap-10 lg:grid-cols-[1.1fr_.8fr_.8fr]">
      <div>
        <div class="flex items-center gap-3 mb-5">
          <?php if (!empty($tenant['logo'])): ?>
            <span class="grid h-11 w-11 place-items-center overflow-hidden bg-white">
              <img src="<?= e(media($tenant['logo'])) ?>" alt="<?= e($tenant['name']) ?>" class="max-h-9 w-auto">
            </span>
          <?php else: ?>
            <span class="grid h-11 w-11 place-items-center text-white font-black" style="background:var(--brand)"><?= e(mb_substr($tenant['name'],0,1)) ?></span>
          <?php endif; ?>
          <span class="font-display font-black tracking-[-.03em] text-white text-xl"><?= e($tenant['name']) ?></span>
        </div>
        <p class="max-w-md text-sm leading-relaxed text-slate-400"><?= e($tenant['description'] ?? 'Renta de vehiculos con reserva rapida y asistencia directa.') ?></p>
        <a href="<?= url('/r/'.$tenant['slug'].'#catalogo') ?>" class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-white hover:text-brand">
          Ver catalogo <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
        </a>
      </div>

      <div>
        <h4 class="mb-4 text-[11px] font-black uppercase tracking-[.24em] text-white/45">Contacto</h4>
        <ul class="space-y-3 text-sm text-slate-400">
          <?php if (!empty($tenant['phone'])): ?><li class="flex items-center gap-2.5"><i data-lucide="phone" class="w-4 h-4 text-slate-500"></i> <?= e($tenant['phone']) ?></li><?php endif; ?>
          <?php if (!empty($tenant['email'])): ?><li class="flex items-center gap-2.5"><i data-lucide="mail" class="w-4 h-4 text-slate-500"></i> <?= e($tenant['email']) ?></li><?php endif; ?>
          <?php if (!empty($tenant['address'])): ?><li class="flex items-start gap-2.5"><i data-lucide="map-pin" class="mt-0.5 w-4 h-4 text-slate-500"></i> <span><?= e($tenant['address']) ?></span></li><?php endif; ?>
        </ul>
      </div>

      <div>
        <h4 class="mb-4 text-[11px] font-black uppercase tracking-[.24em] text-white/45">Reserva facil</h4>
        <p class="text-sm leading-relaxed text-slate-400">Elige el vehiculo, selecciona las fechas y envia la solicitud. El equipo confirma disponibilidad y condiciones.</p>
        <?php if (!empty($tenant['whatsapp'])): ?>
        <a href="<?= e(whatsapp_link($tenant['whatsapp'])) ?>" target="_blank" class="mt-5 inline-flex items-center gap-2 bg-white px-4 py-2.5 text-sm font-bold text-ink transition hover:bg-slate-100"><i data-lucide="message-circle" class="w-4 h-4"></i> Escribenos</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
      <p>&copy; <?= date('Y') ?> <?= e($tenant['name']) ?>. Todos los derechos reservados.</p>
      <p class="flex items-center gap-1.5">Powered by <span class="font-display font-bold text-slate-300">Kyros Rent Car</span></p>
    </div>
  </div>
</footer>
