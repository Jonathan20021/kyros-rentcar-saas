<h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-1">Usuarios globales</h1>
<p class="text-sm text-slate-500 mb-6"><?= count($users) ?> usuarios en la plataforma</p>

<div class="bg-white dark:bg-slate-900 rounded-2xl border hairline shadow-card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-slate-50 dark:bg-slate-800/50">
        <tr>
          <th class="px-6 py-3 font-medium">Usuario</th>
          <th class="px-6 py-3 font-medium">Empresa</th>
          <th class="px-6 py-3 font-medium">Rol</th>
          <th class="px-6 py-3 font-medium">Estado</th>
          <th class="px-6 py-3 font-medium">Ultimo acceso</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#EAECEF] dark:divide-slate-800">
        <?php foreach ($users as $u): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="px-6 py-3">
            <div class="font-medium text-slate-900 dark:text-white"><?= e($u['name']) ?></div>
            <div class="text-xs text-slate-400"><?= e($u['email']) ?></div>
          </td>
          <td class="px-6 py-3"><?= e($u['tenant_name'] ?? 'Kyros (sistema)') ?></td>
          <td class="px-6 py-3"><?= e($u['role_name']) ?></td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($u['status']) ?>"><?= status_label($u['status']) ?></span></td>
          <td class="px-6 py-3 text-slate-500"><?= $u['last_login_at'] ? format_datetime($u['last_login_at']) : 'Nunca' ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
