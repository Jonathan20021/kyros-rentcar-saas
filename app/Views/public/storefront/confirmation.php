<?php
use App\Core\View;
echo View::renderPartial('public/storefront/_nav', ['tenant' => $tenant]);
$waMsg = "Hola {$tenant['name']}, acabo de realizar la reserva {$reservation['code']} del {$reservation['vehicle']}. Quiero confirmar disponibilidad.";
?>
<section class="relative overflow-hidden">
  <div class="lux-orb" style="width:480px;height:480px;left:50%;top:-220px;transform:translateX(-50%);opacity:.16"></div>
  <div class="relative max-w-2xl mx-auto px-4 sm:px-6 pt-32 pb-20 text-center">
    <div class="w-20 h-20 rounded-full grid place-items-center mx-auto" style="background:color-mix(in srgb,var(--brand) 16%,transparent);border:1px solid color-mix(in srgb,var(--brand) 40%,transparent)" data-aos="zoom-in">
      <i data-lucide="check" class="w-10 h-10" style="color:var(--lux-brand-text)"></i>
    </div>
    <h1 class="font-display text-4xl font-extrabold tracking-[-.04em] text-white mt-7">¡Reserva recibida!</h1>
    <p class="text-[var(--lux-muted)] mt-3">Gracias <?= e($reservation['name']) ?>. Tu solicitud fue registrada con el código:</p>
    <p class="font-mono text-2xl font-bold mt-2" style="color:var(--lux-brand-text)"><?= e($reservation['code']) ?></p>

    <div class="lux-surface rounded-2xl p-6 mt-9 text-left">
      <div class="grid grid-cols-2 gap-5 text-sm">
        <div><p class="text-[var(--lux-dim)]">Vehículo</p><p class="font-semibold text-white mt-0.5"><?= e($reservation['vehicle']) ?></p></div>
        <div><p class="text-[var(--lux-dim)]">Días</p><p class="font-semibold text-white mt-0.5"><?= e($reservation['days']) ?></p></div>
        <div><p class="text-[var(--lux-dim)]">Recogida</p><p class="font-semibold text-white mt-0.5"><?= format_datetime($reservation['start']) ?></p></div>
        <div><p class="text-[var(--lux-dim)]">Devolución</p><p class="font-semibold text-white mt-0.5"><?= format_datetime($reservation['end']) ?></p></div>
        <div class="col-span-2 pt-4 border-t border-[#262626]"><p class="text-[var(--lux-dim)]">Total estimado</p><p class="font-display text-2xl font-extrabold mt-0.5" style="color:var(--lux-brand-text)"><?= money($reservation['total']) ?></p></div>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center mt-9">
      <?php if (!empty($tenant['whatsapp'])): ?>
      <a href="<?= e(whatsapp_link($tenant['whatsapp'], $waMsg)) ?>" target="_blank" class="lux-btn lux-btn-wa px-7 py-4">
        <i data-lucide="message-circle" class="w-5 h-5"></i> Confirmar por WhatsApp
      </a>
      <?php endif; ?>
      <a href="<?= url('/r/' . $tenant['slug']) ?>" class="lux-btn lux-btn-outline px-7 py-4">Volver al inicio</a>
    </div>
  </div>
</section>

<?php echo View::renderPartial('public/storefront/_footer', ['tenant' => $tenant]); ?>
