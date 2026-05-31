<?php $meId = (int) ($_auth['user_id'] ?? 0); ?>
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Usuarios globales</h1>
    <p class="text-sm text-slate-500"><?= count($users) ?> usuarios en la plataforma</p>
  </div>
  <a href="<?= url('/super-admin/users/create') ?>" class="k-btn k-btn-grad">
    <i data-lucide="plus" class="w-4 h-4"></i> Nuevo usuario
  </a>
</div>

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
          <th class="px-6 py-3 font-medium text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#EAECEF] dark:divide-slate-800">
        <?php foreach ($users as $u): ?>
        <?php $isSuper = ($u['role_slug'] ?? '') === 'super-admin'; $isSelf = (int) $u['id'] === $meId; ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="px-6 py-3">
            <div class="font-medium text-slate-900 dark:text-white flex items-center gap-2">
              <?= e($u['name']) ?>
              <?php if ($isSuper): ?><span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">SUPER</span><?php endif; ?>
              <?php if ($isSelf): ?><span class="text-[10px] text-slate-400">(tú)</span><?php endif; ?>
            </div>
            <div class="text-xs text-slate-400"><?= e($u['email']) ?></div>
          </td>
          <td class="px-6 py-3"><?= e($u['tenant_name'] ?? 'Kyros (sistema)') ?></td>
          <td class="px-6 py-3"><?= e($u['role_name']) ?></td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($u['status']) ?>"><?= status_label($u['status']) ?></span></td>
          <td class="px-6 py-3 text-slate-500"><?= $u['last_login_at'] ? format_datetime($u['last_login_at']) : 'Nunca' ?></td>
          <td class="px-6 py-3">
            <div class="flex items-center justify-end gap-1.5">
              <a href="<?= url('/super-admin/users/edit/' . $u['id']) ?>" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" title="Editar">
                <i data-lucide="pencil" class="w-4 h-4"></i>
              </a>
              <?php if (!$isSelf): ?>
                <form method="POST" action="<?= url('/super-admin/users/toggle/' . $u['id']) ?>">
                  <?= csrf_field() ?>
                  <?php if ($u['status'] === 'active'): ?>
                    <button class="p-2 rounded-lg hover:bg-amber-50 text-amber-600" title="Desactivar"><i data-lucide="pause" class="w-4 h-4"></i></button>
                  <?php else: ?>
                    <button class="p-2 rounded-lg hover:bg-emerald-50 text-emerald-600" title="Activar"><i data-lucide="play" class="w-4 h-4"></i></button>
                  <?php endif; ?>
                </form>
                <form method="POST" action="<?= url('/super-admin/users/delete/' . $u['id']) ?>"
                      data-confirm="Esta acción eliminará al usuario <?= e($u['name']) ?>. No podrá iniciar sesión."
                      data-confirm-title="¿Eliminar usuario?" data-confirm-label="Sí, eliminar" data-confirm-variant="danger">
                  <?= csrf_field() ?>
                  <button class="p-2 rounded-lg hover:bg-red-50 text-red-600" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">
          <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
          <p>No hay usuarios todavía</p>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
