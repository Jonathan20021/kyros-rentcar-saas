<?php
$regList   = (string)($s['notify_recipients_registration'] ?? '');
$demoList  = (string)($s['notify_recipients_demo'] ?? '');
$loginList = (string)($s['notify_recipients_logins'] ?? '');
$loginsOn  = ($s['notify_logins_enabled'] ?? '0') === '1';
$filter    = (string)($s['notify_logins_filter'] ?? 'failed_only');

$totalRecipients = function (string $raw): int {
  $parts = preg_split('/[\s,;]+/', $raw) ?: [];
  $emails = array_filter(array_map('trim', $parts), fn($p) => $p !== '' && filter_var($p, FILTER_VALIDATE_EMAIL));
  return count(array_unique($emails));
};
?>
<div class="max-w-5xl mx-auto">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <h1 class="font-display text-2xl font-extrabold text-navy dark:text-white tracking-tight">Notificaciones de plataforma</h1>
      <p class="text-[13px] text-slate-500 mt-1">Quién recibe correos cuando ocurren eventos clave en el SaaS.</p>
    </div>
    <form method="POST" action="<?= url('/super-admin/notifications/test') ?>"><?= csrf_field() ?>
      <button class="k-btn k-btn-outline !h-10" <?= !$mailReady ? 'disabled title="Activa Mailer primero en Configuración"' : '' ?>>
        <i data-lucide="send" class="w-4 h-4"></i> Enviar correos de prueba
      </button>
    </form>
  </div>

  <?php if (!$mailReady): ?>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 mb-5 flex items-start gap-3">
      <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mt-0.5 shrink-0"></i>
      <div class="text-[13px] text-amber-800 leading-relaxed">
        El servicio de correo (Resend) no está activo. Las notificaciones se guardan en el log pero no se envían.
        <a href="<?= url('/super-admin/settings') ?>" class="font-semibold underline">Configurar ahora</a>.
      </div>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= url('/super-admin/notifications') ?>" class="space-y-5">
    <?= csrf_field() ?>

    <!-- KPI strip -->
    <div class="grid grid-cols-3 gap-3 sm:gap-4">
      <div class="card p-4 sm:p-5">
        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 grid place-items-center mb-2.5"><i data-lucide="building-2" class="w-4 h-4"></i></div>
        <p class="font-display text-2xl font-extrabold text-navy dark:text-white tnum"><?= $totalRecipients($regList) ?></p>
        <p class="text-[12px] text-slate-500 mt-0.5">Destinatarios · Registros</p>
      </div>
      <div class="card p-4 sm:p-5">
        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 grid place-items-center mb-2.5"><i data-lucide="sparkles" class="w-4 h-4"></i></div>
        <p class="font-display text-2xl font-extrabold text-navy dark:text-white tnum"><?= $totalRecipients($demoList) ?></p>
        <p class="text-[12px] text-slate-500 mt-0.5">Destinatarios · Demos</p>
      </div>
      <div class="card p-4 sm:p-5">
        <div class="w-9 h-9 rounded-xl <?= $loginsOn ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' ?> grid place-items-center mb-2.5"><i data-lucide="log-in" class="w-4 h-4"></i></div>
        <p class="font-display text-2xl font-extrabold text-navy dark:text-white tnum"><?= $totalRecipients($loginList) ?></p>
        <p class="text-[12px] text-slate-500 mt-0.5">Destinatarios · Logins <?= $loginsOn ? '' : '· <span class="text-slate-400">desactivado</span>' ?></p>
      </div>
    </div>

    <!-- Registration alerts -->
    <div class="card p-5 sm:p-7">
      <div class="flex items-start gap-4 mb-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 grid place-items-center shrink-0"><i data-lucide="building-2" class="w-5 h-5"></i></div>
        <div class="flex-1 min-w-0">
          <h2 class="font-display font-bold text-navy dark:text-white text-[15.5px]">Nueva empresa registrada</h2>
          <p class="text-[12.5px] text-slate-500 mt-0.5">Se envía cada vez que alguien completa el formulario de registro (antes de la aprobación del super admin).</p>
        </div>
      </div>
      <label class="text-[12px] font-medium text-slate-500 block mb-1.5">Destinatarios (separados por coma o salto de línea)</label>
      <textarea name="notify_recipients_registration" rows="3"
                class="fld font-mono !text-[13px]"
                placeholder="ops@kyrosrd.com, hidalgo@evallishbpo.com"><?= e($regList) ?></textarea>
      <p class="text-[11.5px] text-slate-400 mt-2">Solo direcciones válidas se guardan. Los duplicados se descartan automáticamente.</p>
    </div>

    <!-- Demo alerts -->
    <div class="card p-5 sm:p-7">
      <div class="flex items-start gap-4 mb-4">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 grid place-items-center shrink-0"><i data-lucide="sparkles" class="w-5 h-5"></i></div>
        <div class="flex-1 min-w-0">
          <h2 class="font-display font-bold text-navy dark:text-white text-[15.5px]">Demo redimida</h2>
          <p class="text-[12.5px] text-slate-500 mt-0.5">Se envía cuando alguien usa un código de licencia demo en la pantalla de login. Incluye plan, código y horas válidas.</p>
        </div>
      </div>
      <label class="text-[12px] font-medium text-slate-500 block mb-1.5">Destinatarios</label>
      <textarea name="notify_recipients_demo" rows="3"
                class="fld font-mono !text-[13px]"
                placeholder="ventas@kyrosrd.com"><?= e($demoList) ?></textarea>
    </div>

    <!-- Login alerts -->
    <div class="card p-5 sm:p-7" x-data="{ on: <?= $loginsOn ? 'true' : 'false' ?> }">
      <div class="flex items-start gap-4 mb-4">
        <div class="w-12 h-12 rounded-2xl <?= $loginsOn ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' ?> grid place-items-center shrink-0" :class="on ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400'"><i data-lucide="log-in" class="w-5 h-5"></i></div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-3">
            <h2 class="font-display font-bold text-navy dark:text-white text-[15.5px]">Logs de acceso</h2>
            <label class="inline-flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="notify_logins_enabled" value="1" x-model="on"
                     class="sr-only peer">
              <span class="relative w-10 h-6 bg-slate-200 rounded-full peer-checked:bg-emerald-500 transition">
                <span class="absolute left-0.5 top-0.5 w-5 h-5 rounded-full bg-white shadow transition" :class="on ? 'translate-x-4' : ''"></span>
              </span>
              <span class="text-[12px] font-semibold" :class="on ? 'text-emerald-600' : 'text-slate-400'" x-text="on ? 'Activo' : 'Inactivo'"></span>
            </label>
          </div>
          <p class="text-[12.5px] text-slate-500 mt-0.5">Notifica intentos de acceso al panel. Útil para detectar actividad sospechosa.</p>
        </div>
      </div>

      <div :class="on ? '' : 'opacity-50 pointer-events-none'">
        <label class="text-[12px] font-medium text-slate-500 block mb-1.5">Destinatarios</label>
        <textarea name="notify_recipients_logins" rows="3"
                  class="fld font-mono !text-[13px]"
                  placeholder="security@kyrosrd.com"><?= e($loginList) ?></textarea>

        <label class="text-[12px] font-medium text-slate-500 block mt-3 mb-1.5">Qué eventos notificar</label>
        <div class="grid sm:grid-cols-3 gap-2">
          <?php foreach ([
            'failed_only' => ['Solo fallidos',    'Únicamente intentos rechazados',      'shield-alert'],
            'super_only'  => ['Solo super admin', 'Login del equipo Kyros (sin tenant)','crown'],
            'all'         => ['Todos',            'Cada login exitoso y fallido',        'list'],
          ] as $val => [$lbl, $desc, $icon]): ?>
            <label class="relative cursor-pointer">
              <input type="radio" name="notify_logins_filter" value="<?= $val ?>" <?= $filter === $val ? 'checked' : '' ?> class="peer sr-only">
              <div class="p-3.5 rounded-2xl border hairline transition peer-checked:border-brand peer-checked:bg-brand/[0.04] peer-checked:shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                  <i data-lucide="<?= $icon ?>" class="w-4 h-4 text-slate-400 peer-checked:text-brand"></i>
                  <span class="font-display font-bold text-navy text-[13px]"><?= e($lbl) ?></span>
                </div>
                <p class="text-[11.5px] text-slate-500 leading-snug"><?= e($desc) ?></p>
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end gap-2">
      <a href="<?= url('/super-admin/dashboard') ?>" class="k-btn k-btn-outline">Cancelar</a>
      <button type="submit" class="k-btn k-btn-grad !px-6"><i data-lucide="check" class="w-4 h-4"></i> Guardar configuración</button>
    </div>
  </form>

  <p class="text-center text-[11.5px] text-slate-400 mt-6">
    Los correos se envían vía Resend desde el remitente configurado en
    <a href="<?= url('/super-admin/settings') ?>" class="text-brand font-medium hover:underline">Configuración</a>.
    Cada envío fallido queda en los logs.
  </p>
</div>
