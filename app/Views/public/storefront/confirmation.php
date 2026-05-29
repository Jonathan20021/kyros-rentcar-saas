<?php
use App\Core\View;
echo View::renderPartial('public/storefront/_nav', ['tenant' => $tenant]);
$primary = $tenant['primary_color'];
$waMsg = "Hola {$tenant['name']}, acabo de realizar la reserva {$reservation['code']} del {$reservation['vehicle']}. Quiero confirmar disponibilidad.";
?>
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-16 text-center">
  <div class="w-20 h-20 rounded-full bg-emerald-100 grid place-items-center mx-auto" data-aos="zoom-in">
    <i data-lucide="check" class="w-10 h-10 text-emerald-600"></i>
  </div>
  <h1 class="text-3xl font-extrabold text-slate-900 mt-6">¡Reserva recibida!</h1>
  <p class="text-slate-500 mt-2">Gracias <?= e($reservation['name']) ?>. Tu solicitud fue registrada con el codigo:</p>
  <p class="text-2xl font-mono font-bold mt-2" style="color:<?= e($primary) ?>"><?= e($reservation['code']) ?></p>

  <div class="bg-white rounded-2xl border hairline shadow-card p-6 mt-8 text-left">
    <div class="grid grid-cols-2 gap-4 text-sm">
      <div><p class="text-slate-400">Vehiculo</p><p class="font-semibold text-slate-900"><?= e($reservation['vehicle']) ?></p></div>
      <div><p class="text-slate-400">Dias</p><p class="font-semibold text-slate-900"><?= e($reservation['days']) ?></p></div>
      <div><p class="text-slate-400">Recogida</p><p class="font-semibold text-slate-900"><?= format_datetime($reservation['start']) ?></p></div>
      <div><p class="text-slate-400">Devolucion</p><p class="font-semibold text-slate-900"><?= format_datetime($reservation['end']) ?></p></div>
      <div class="col-span-2 pt-3 border-t border-slate-100"><p class="text-slate-400">Total estimado</p><p class="text-xl font-extrabold" style="color:<?= e($primary) ?>"><?= money($reservation['total']) ?></p></div>
    </div>
  </div>

  <div class="flex flex-col sm:flex-row gap-3 justify-center mt-8">
    <?php if (!empty($tenant['whatsapp'])): ?>
    <a href="<?= e(whatsapp_link($tenant['whatsapp'], $waMsg)) ?>" target="_blank" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-white font-semibold" style="background:#25D366">
      <i data-lucide="message-circle" class="w-5 h-5"></i> Confirmar por WhatsApp
    </a>
    <?php endif; ?>
    <a href="<?= url('/r/' . $tenant['slug']) ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border border-slate-200 font-medium">Volver al inicio</a>
  </div>
</section>

<?php echo View::renderPartial('public/storefront/_footer', ['tenant' => $tenant]); ?>
