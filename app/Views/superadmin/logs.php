<h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-1">Logs de actividad</h1>
<p class="text-sm text-slate-500 mb-6">Auditoria global de la plataforma</p>

<div class="bg-white dark:bg-slate-900 rounded-2xl border hairline shadow-card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-slate-50 dark:bg-slate-800/50">
        <tr>
          <th class="px-6 py-3 font-medium">Fecha</th>
          <th class="px-6 py-3 font-medium">Usuario</th>
          <th class="px-6 py-3 font-medium">Empresa</th>
          <th class="px-6 py-3 font-medium">Accion</th>
          <th class="px-6 py-3 font-medium">Modulo</th>
          <th class="px-6 py-3 font-medium">Descripcion</th>
          <th class="px-6 py-3 font-medium">IP</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#EAECEF] dark:divide-slate-800">
        <?php foreach ($logs as $l): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="px-6 py-3 text-slate-500 whitespace-nowrap"><?= format_datetime($l['created_at']) ?></td>
          <td class="px-6 py-3"><?= e($l['user_name'] ?? '-') ?></td>
          <td class="px-6 py-3"><?= e($l['tenant_name'] ?? 'Sistema') ?></td>
          <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-xs font-medium"><?= e($l['action']) ?></span></td>
          <td class="px-6 py-3 text-slate-500"><?= e($l['module'] ?? '-') ?></td>
          <td class="px-6 py-3 text-slate-500"><?= e($l['description'] ?? '') ?></td>
          <td class="px-6 py-3 text-slate-400 text-xs"><?= e($l['ip_address'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($logs)): ?>
        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Sin actividad registrada</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
