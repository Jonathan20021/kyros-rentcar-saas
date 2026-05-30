<?php $atCap = $maxUsers >= 0 && $count >= $maxUsers; ?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
  <div>
    <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Equipo</h1>
    <p class="text-sm text-slate-500"><?= $count ?> usuario<?= $count===1?'':'s' ?><?= $maxUsers>=0 ? ' de '.$maxUsers.' del plan' : '' ?></p>
  </div>
  <?php if (can('users.create')): ?>
    <?php if ($atCap): ?>
      <span class="k-btn k-btn-outline opacity-60 cursor-not-allowed" title="Límite del plan alcanzado"><i data-lucide="lock" class="w-4 h-4"></i> Límite alcanzado</span>
    <?php else: ?>
      <a href="<?= url('/admin/users/create') ?>" class="k-btn k-btn-grad"><i data-lucide="user-plus" class="w-4 h-4"></i> Invitar usuario</a>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php if ($maxUsers >= 0): ?>
<div class="card p-4 mb-5">
  <div class="flex items-center justify-between mb-2 text-sm">
    <span class="text-slate-500">Uso de licencias</span>
    <span class="font-semibold text-navy dark:text-white tnum"><?= $count ?> / <?= $maxUsers ?></span>
  </div>
  <div class="progress"><i style="width:<?= min(100, (int)round($count/max(1,$maxUsers)*100)) ?>%"></i></div>
</div>
<?php endif; ?>

<div class="card overflow-hidden">
  <div class="overflow-x-auto sm:overflow-x-visible">
    <table class="k-table">
      <thead>
        <tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Último acceso</th><th class="text-right">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td data-label="Usuario" class="k-td-primary">
            <div class="flex items-center gap-3 w-full">
              <div class="w-9 h-9 rounded-lg bg-navy/5 dark:bg-white/5 text-navy dark:text-white grid place-items-center text-xs font-bold shrink-0"><?= e(initials($u['name'])) ?></div>
              <div class="min-w-0">
                <div class="font-medium text-navy dark:text-white truncate"><?= e($u['name']) ?><?= (int)$u['id']===$_auth['user_id']?' <span class="text-[11px] text-slate-400 font-normal">(tú)</span>':'' ?></div>
                <div class="text-xs text-slate-400 truncate"><?= e($u['email']) ?></div>
              </div>
            </div>
          </td>
          <td data-label="Rol"><span class="px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600"><?= e($u['role_name']) ?></span></td>
          <td data-label="Estado"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($u['status']) ?>"><?= status_label($u['status']) ?></span></td>
          <td data-label="Último acceso" class="text-slate-500 tnum"><?= $u['last_login_at'] ? format_datetime($u['last_login_at']) : 'Nunca' ?></td>
          <td class="k-td-actions text-right">
            <div class="flex items-center justify-end gap-1.5">
              <?php if (can('users.edit')): ?>
                <a href="<?= url('/admin/users/edit/'.$u['id']) ?>" class="icon-btn !w-8 !h-8" title="Editar"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                <?php if ((int)$u['id'] !== $_auth['user_id']): ?>
                <form method="POST" action="<?= url('/admin/users/toggle/'.$u['id']) ?>"><?= csrf_field() ?>
                  <button class="icon-btn !w-8 !h-8 <?= $u['status']==='active'?'hover:!text-brand':'hover:!text-emerald-600' ?>" title="<?= $u['status']==='active'?'Desactivar':'Activar' ?>"><i data-lucide="<?= $u['status']==='active'?'user-x':'user-check' ?>" class="w-4 h-4"></i></button>
                </form>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
