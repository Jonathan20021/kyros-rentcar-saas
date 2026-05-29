<?php /** Storefront footer. Expects $tenant. */ ?>
<footer class="bg-ink text-slate-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid md:grid-cols-3 gap-10">
    <div>
      <div class="flex items-center gap-2.5 mb-4">
        <div class="w-10 h-10 rounded-2xl grid place-items-center text-white font-black" style="background:linear-gradient(135deg,<?= e($tenant['primary_color']) ?>,<?= e($tenant['secondary_color']) ?>)"><?= e(mb_substr($tenant['name'],0,1)) ?></div>
        <span class="font-display font-extrabold text-white text-lg"><?= e($tenant['name']) ?></span>
      </div>
      <p class="text-sm text-slate-400 leading-relaxed max-w-xs"><?= e($tenant['description'] ?? 'Renta de vehiculos.') ?></p>
    </div>
    <div>
      <h4 class="font-semibold text-white mb-4 text-sm uppercase tracking-wider text-[12px]">Contacto</h4>
      <ul class="space-y-3 text-sm text-slate-400">
        <?php if (!empty($tenant['phone'])): ?><li class="flex items-center gap-2.5"><i data-lucide="phone" class="w-4 h-4 text-slate-500"></i> <?= e($tenant['phone']) ?></li><?php endif; ?>
        <?php if (!empty($tenant['email'])): ?><li class="flex items-center gap-2.5"><i data-lucide="mail" class="w-4 h-4 text-slate-500"></i> <?= e($tenant['email']) ?></li><?php endif; ?>
        <?php if (!empty($tenant['address'])): ?><li class="flex items-center gap-2.5"><i data-lucide="map-pin" class="w-4 h-4 text-slate-500"></i> <?= e($tenant['address']) ?></li><?php endif; ?>
      </ul>
    </div>
    <div>
      <h4 class="font-semibold text-white mb-4 text-[12px] uppercase tracking-wider">Reserva facil</h4>
      <p class="text-sm text-slate-400 leading-relaxed">Elige tu vehiculo, selecciona las fechas y confirma. Te contactamos al instante.</p>
      <?php if (!empty($tenant['whatsapp'])): ?>
      <a href="<?= e(whatsapp_link($tenant['whatsapp'])) ?>" target="_blank" class="inline-flex items-center gap-2 mt-4 px-4 py-2.5 rounded-xl bg-white text-ink text-sm font-semibold hover:bg-slate-100 transition"><i data-lucide="message-circle" class="w-4 h-4"></i> Escribenos</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
      <p>&copy; <?= date('Y') ?> <?= e($tenant['name']) ?>. Todos los derechos reservados.</p>
      <p class="flex items-center gap-1.5">Powered by <span class="font-display font-bold text-slate-300">Kyros Rent Car</span></p>
    </div>
  </div>
</footer>
