<div class="max-w-2xl mx-auto">
  <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1">Configuración</h1>
  <p class="text-sm text-slate-500 mb-6">Integración de correo transaccional con Resend</p>

  <form method="POST" action="<?= url('/super-admin/settings') ?>" class="card p-6 space-y-5">
    <?= csrf_field() ?>

    <label class="flex items-center justify-between gap-3 p-4 rounded-xl bg-paper">
      <div>
        <p class="font-semibold text-navy">Habilitar envío de correos</p>
        <p class="text-xs text-slate-400 mt-0.5">Si está desactivado, los correos se registran en storage/logs/mail.log</p>
      </div>
      <input type="checkbox" name="mail_enabled" value="1" <?= ($s['mail_enabled'] ?? '0')==='1'?'checked':'' ?> class="w-5 h-5 rounded text-brand focus:ring-brand/30">
    </label>

    <div>
      <label class="block text-sm font-medium mb-1.5">Resend API Key</label>
      <input type="password" name="resend_api_key" autocomplete="off" placeholder="<?= $hasKey ? '•••••••••• (guardada — deja en blanco para conservar)' : 're_xxxxxxxxxxxxxxxx' ?>" class="fld">
      <p class="text-xs text-slate-400 mt-1.5">Obtén tu API key en <span class="font-medium text-navy">resend.com → API Keys</span>. <?= $hasKey ? '<span class="text-emerald-600 font-medium">Hay una key guardada.</span>' : '' ?></p>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Remitente (email)</label>
        <input name="mail_from_email" value="<?= e($s['mail_from_email'] ?? 'onboarding@resend.dev') ?>" class="fld">
        <p class="text-xs text-slate-400 mt-1.5">Debe ser un dominio verificado en Resend.</p>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Remitente (nombre)</label>
        <input name="mail_from_name" value="<?= e($s['mail_from_name'] ?? 'Kyros Rent Car') ?>" class="fld">
      </div>
    </div>

    <div class="flex items-center gap-2 pt-2">
      <button type="submit" class="k-btn k-btn-grad !px-6">Guardar</button>
    </div>
  </form>

  <div class="card p-6 mt-5 flex items-center justify-between gap-4">
    <div>
      <p class="font-semibold text-navy">Enviar correo de prueba</p>
      <p class="text-xs text-slate-400 mt-0.5">Se enviará a tu correo (<?= e(auth()['email'] ?? '') ?>).</p>
    </div>
    <form method="POST" action="<?= url('/super-admin/settings/test') ?>"><?= csrf_field() ?>
      <button type="submit" class="k-btn k-btn-outline"><i data-lucide="send" class="w-4 h-4"></i> Enviar prueba</button>
    </form>
  </div>

  <div class="mt-5 p-4 rounded-xl bg-paper text-xs text-slate-500 leading-relaxed">
    <p class="font-semibold text-navy mb-1">¿Cómo funciona?</p>
    Kyros usa la API de Resend para enviar: bienvenida al registrar una rent car, invitaciones de equipo, recuperación de contraseña y confirmaciones de reserva. Si el envío está deshabilitado, todo sigue funcionando y los correos quedan registrados en el log para revisión.
  </div>
</div>
