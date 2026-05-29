<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Empresas</h1>
    <p class="text-sm text-slate-500"><?= count($tenants) ?> rent cars registradas</p>
  </div>
  <a href="<?= url('/super-admin/tenants/create') ?>" class="k-btn k-btn-grad">
    <i data-lucide="plus" class="w-4 h-4"></i> Nueva empresa
  </a>
</div>

<div class="bg-white dark:bg-slate-900 rounded-2xl border hairline shadow-card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-slate-50 dark:bg-slate-800/50">
        <tr>
          <th class="px-6 py-3 font-medium">Empresa</th>
          <th class="px-6 py-3 font-medium">Plan</th>
          <th class="px-6 py-3 font-medium">Vehiculos</th>
          <th class="px-6 py-3 font-medium">Usuarios</th>
          <th class="px-6 py-3 font-medium">Estado</th>
          <th class="px-6 py-3 font-medium text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#EAECEF] dark:divide-slate-800">
        <?php foreach ($tenants as $t): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="px-6 py-3">
            <div class="font-medium text-slate-900 dark:text-white"><?= e($t['name']) ?></div>
            <a href="<?= url('/r/' . $t['slug']) ?>" target="_blank" class="text-xs text-indigo-500 hover:underline">/r/<?= e($t['slug']) ?></a>
          </td>
          <td class="px-6 py-3"><?= e($t['plan_name'] ?? '-') ?></td>
          <td class="px-6 py-3"><?= (int) $t['vehicles_count'] ?></td>
          <td class="px-6 py-3"><?= (int) $t['users_count'] ?></td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($t['status']) ?>"><?= status_label($t['status']) ?></span></td>
          <td class="px-6 py-3">
            <div class="flex items-center justify-end gap-1.5">
              <a href="<?= url('/super-admin/tenants/edit/' . $t['id']) ?>" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" title="Editar">
                <i data-lucide="pencil" class="w-4 h-4"></i>
              </a>
              <?php if ($t['status'] === 'suspended'): ?>
                <form method="POST" action="<?= url('/super-admin/tenants/activate/' . $t['id']) ?>"><?= csrf_field() ?>
                  <button class="p-2 rounded-lg hover:bg-emerald-50 text-emerald-600" title="Activar"><i data-lucide="play" class="w-4 h-4"></i></button>
                </form>
              <?php else: ?>
                <form method="POST" action="<?= url('/super-admin/tenants/suspend/' . $t['id']) ?>" data-confirm="Sus usuarios no podrán acceder hasta que la reactives." data-confirm-title="¿Suspender empresa?" data-confirm-label="Sí, suspender" data-confirm-variant="warning"><?= csrf_field() ?>
                  <button class="p-2 rounded-lg hover:bg-red-50 text-red-600" title="Suspender"><i data-lucide="pause" class="w-4 h-4"></i></button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($tenants)): ?>
        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">
          <i data-lucide="building-2" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
          <p>No hay empresas todavia</p>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
