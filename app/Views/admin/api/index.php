<?php
$canManage = can('api.manage');
$endpoints = [
  ['GET',  '/vehicles',            'Lista la flotilla (id, marca, modelo, precio, estado).'],
  ['GET',  '/vehicles/{id}',       'Detalle de un vehículo.'],
  ['GET',  '/customers',           'Lista de clientes.'],
  ['GET',  '/reservations',        'Lista de reservas.'],
  ['GET',  '/reservations/{id}',   'Detalle de una reserva.'],
  ['POST', '/reservations',        'Crea una reserva (vehicle_id, start, end, customer...).'],
  ['GET',  '/contracts',           'Lista de contratos.'],
  ['GET',  '/payments',            'Lista de pagos.'],
];
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">API & Integraciones</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Conecta Kyros con tus apps usando la API REST v1</p>
  </div>
  <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-xs font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> v1 estable</span>
</div>

<?php if (!empty($newToken)): ?>
<div class="card p-5 mb-5 border-2 !border-brand/30 reveal in" x-data="{copied:false, t:<?= json_encode($newToken) ?>}">
  <div class="flex items-start gap-3">
    <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand grid place-items-center shrink-0"><i data-lucide="key-round" class="w-5 h-5"></i></div>
    <div class="min-w-0 flex-1">
      <p class="font-semibold text-navy dark:text-white">Tu nueva clave API</p>
      <p class="text-xs text-amber-600 mb-2.5 flex items-center gap-1"><i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Cópiala ahora: por seguridad no volverá a mostrarse.</p>
      <div class="flex items-center gap-2">
        <code class="flex-1 px-3 py-2.5 rounded-xl bg-navy text-white text-[13px] font-mono break-all" x-text="t"></code>
        <button @click="navigator.clipboard.writeText(t); copied=true; setTimeout(()=>copied=false,1800)" class="k-btn k-btn-dark shrink-0">
          <i data-lucide="copy" class="w-4 h-4" x-show="!copied"></i><i data-lucide="check" class="w-4 h-4" x-show="copied" x-cloak></i>
          <span x-text="copied?'Copiado':'Copiar'"></span>
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="grid lg:grid-cols-3 gap-5">
  <!-- Left: keys -->
  <div class="lg:col-span-2 space-y-5">
    <?php if ($canManage): ?>
    <div class="card p-6 reveal">
      <h2 class="font-display font-bold text-navy dark:text-white mb-1">Generar clave</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Crea un token para autenticar tus integraciones.</p>
      <form method="POST" action="<?= url('/admin/api') ?>" class="flex flex-col sm:flex-row gap-3">
        <?= csrf_field() ?>
        <input name="name" maxlength="120" placeholder="Nombre (ej. Integración web, App móvil)" class="fld flex-1">
        <button type="submit" class="k-btn k-btn-grad shrink-0"><i data-lucide="plus" class="w-4 h-4"></i> Crear clave</button>
      </form>
    </div>
    <?php endif; ?>

    <div class="card overflow-hidden reveal">
      <div class="px-6 py-4 border-b hairline font-display font-bold text-navy dark:text-white flex items-center justify-between">
        Claves activas <span class="text-xs font-medium text-slate-400"><?= count($keys) ?> total</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-left text-slate-400 bg-paper dark:bg-slate-800/50">
            <tr><th class="px-6 py-3 font-medium">Nombre</th><th class="px-6 py-3 font-medium">Estado</th><th class="px-6 py-3 font-medium">Último uso</th><th class="px-6 py-3 font-medium">Creada</th><th class="px-6 py-3"></th></tr>
          </thead>
          <tbody class="divide-y hairline">
            <?php foreach ($keys as $k): $active = $k['status']==='active'; ?>
            <tr class="hover:bg-paper dark:hover:bg-slate-800/40">
              <td class="px-6 py-3.5">
                <span class="font-medium text-navy dark:text-white flex items-center gap-2"><i data-lucide="key-round" class="w-4 h-4 text-slate-400"></i><?= e($k['name']) ?></span>
              </td>
              <td class="px-6 py-3.5">
                <?php if ($active): ?><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-xs font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activa</span>
                <?php else: ?><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs font-semibold">Revocada</span><?php endif; ?>
              </td>
              <td class="px-6 py-3.5 text-slate-500"><?= $k['last_used_at'] ? e(date('d/m/Y H:i', strtotime($k['last_used_at']))) : '—' ?></td>
              <td class="px-6 py-3.5 text-slate-500"><?= e(date('d/m/Y', strtotime($k['created_at']))) ?></td>
              <td class="px-6 py-3.5 text-right">
                <?php if ($active && $canManage): ?>
                <form method="POST" action="<?= url('/admin/api/revoke/'.$k['id']) ?>" data-confirm="¿Revocar esta clave? Las apps que la usen dejarán de funcionar." data-confirm-title="Revocar clave API" data-confirm-label="Sí, revocar">
                  <?= csrf_field() ?>
                  <button class="text-xs font-semibold text-red-600 hover:underline">Revocar</button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($keys)): ?>
            <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">
              <i data-lucide="key-round" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
              <p class="text-sm">Aún no tienes claves API.</p>
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Right: docs -->
  <div class="space-y-5">
    <div class="card p-6 reveal">
      <h2 class="font-display font-bold text-navy dark:text-white mb-3">Cómo autenticar</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Incluye tu token en el header de cada petición:</p>
      <div class="rounded-xl bg-navy text-slate-200 text-[12px] font-mono p-3.5 leading-relaxed overflow-x-auto" x-data="{c:false}">
        <div class="flex items-start justify-between gap-2">
          <pre class="whitespace-pre-wrap break-all">curl <?= e($apiBase) ?>/vehicles \
  -H "Authorization: Bearer TU_TOKEN"</pre>
          <button @click="navigator.clipboard.writeText('curl <?= e($apiBase) ?>/vehicles -H \'Authorization: Bearer TU_TOKEN\''); c=true; setTimeout(()=>c=false,1500)" class="text-slate-400 hover:text-white shrink-0"><i data-lucide="copy" class="w-3.5 h-3.5" x-show="!c"></i><i data-lucide="check" class="w-3.5 h-3.5" x-show="c" x-cloak></i></button>
        </div>
      </div>
      <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">
        <span class="font-medium text-navy dark:text-white">Base URL</span>
        <code class="block mt-1 px-2.5 py-1.5 rounded-lg bg-paper dark:bg-slate-800 text-[12px] break-all"><?= e($apiBase) ?></code>
      </div>
    </div>

    <div class="card p-6 reveal">
      <h2 class="font-display font-bold text-navy dark:text-white mb-3">Endpoints</h2>
      <div class="space-y-2.5">
        <?php foreach ($endpoints as [$m,$path,$desc]): ?>
        <div class="flex items-start gap-2.5">
          <span class="mt-0.5 shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide <?= $m==='GET'?'bg-sky-50 dark:bg-sky-500/10 text-sky-600':'bg-amber-50 dark:bg-amber-500/10 text-amber-600' ?>"><?= $m ?></span>
          <div class="min-w-0">
            <code class="text-[12.5px] font-mono text-navy dark:text-slate-200 break-all"><?= e($path) ?></code>
            <p class="text-xs text-slate-400 leading-snug"><?= e($desc) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <p class="text-xs text-slate-400 mt-4 pt-3 border-t hairline">Todas las respuestas son JSON y están aisladas a tu empresa (tenant). Los datos de otras empresas nunca son accesibles.</p>
    </div>
  </div>
</div>
