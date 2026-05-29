<?php
$canManage = can('catalog.manage');
$isEdit = !empty($editing);
$action = $isEdit ? url('/admin/categories/update/'.$editing['id']) : url('/admin/categories');
$iconHints = ['car','car-front','truck','bus','bike','caravan','crown','gem','zap','snowflake','users','briefcase'];
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Categorías de vehículos</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Organiza tu flotilla por tipo (económico, SUV, lujo…)</p>
  </div>
</div>

<div class="grid lg:grid-cols-[320px_1fr] gap-5">
  <!-- form -->
  <?php if ($canManage): ?>
  <div class="card p-5 reveal h-fit" x-data="{icon: '<?= e($isEdit ? ($editing['icon'] ?: 'car') : 'car') ?>'}">
    <h2 class="font-display font-bold text-navy dark:text-white mb-4 flex items-center gap-2">
      <i data-lucide="<?= $isEdit ? 'pencil' : 'plus' ?>" class="w-4 h-4 text-brand"></i>
      <?= $isEdit ? 'Editar categoría' : 'Nueva categoría' ?>
    </h2>
    <form method="POST" action="<?= $action ?>" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Nombre *</label>
        <input name="name" required maxlength="80" value="<?= e($isEdit ? $editing['name'] : '') ?>" placeholder="SUV, Económico, Lujo…" class="fld">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Ícono</label>
        <div class="flex items-center gap-2">
          <span class="w-11 h-11 rounded-xl bg-brand/10 text-brand grid place-items-center shrink-0"><i :data-lucide="icon" x-effect="$nextTick(()=>window.lucide&&lucide.createIcons())" class="w-5 h-5"></i></span>
          <input name="icon" x-model="icon" maxlength="60" placeholder="car" class="fld font-mono text-[13px]">
        </div>
        <div class="flex flex-wrap gap-1.5 mt-2">
          <?php foreach ($iconHints as $ic): ?>
          <button type="button" @click="icon='<?= $ic ?>'" class="w-8 h-8 rounded-lg border hairline grid place-items-center text-slate-500 hover:text-brand hover:border-brand/40" title="<?= $ic ?>"><i data-lucide="<?= $ic ?>" class="w-4 h-4"></i></button>
          <?php endforeach; ?>
        </div>
        <p class="text-[11px] text-slate-400 mt-1.5">Nombres de <a href="https://lucide.dev/icons" target="_blank" class="text-brand hover:underline">lucide.dev</a></p>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Estado</label>
        <select name="status" class="fld">
          <option value="active" <?= ($isEdit && $editing['status']==='inactive')?'':'selected' ?>>Activa</option>
          <option value="inactive" <?= ($isEdit && $editing['status']==='inactive')?'selected':'' ?>>Inactiva</option>
        </select>
      </div>
      <div class="flex gap-2 pt-1">
        <button type="submit" class="k-btn k-btn-grad flex-1"><?= $isEdit ? 'Guardar' : 'Crear' ?></button>
        <?php if ($isEdit): ?><a href="<?= url('/admin/categories') ?>" class="k-btn k-btn-outline">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <!-- list -->
  <div class="card overflow-hidden reveal <?= $canManage ? '' : 'lg:col-span-2' ?>">
    <div class="px-6 py-4 border-b hairline font-display font-bold text-navy dark:text-white flex items-center justify-between">
      Categorías <span class="text-xs font-medium text-slate-400"><?= count($categories) ?></span>
    </div>
    <div class="divide-y hairline">
      <?php foreach ($categories as $c): $active = $c['status']==='active'; ?>
      <div class="flex items-center gap-3 px-6 py-3.5 hover:bg-paper dark:hover:bg-slate-800/40">
        <span class="w-10 h-10 rounded-xl grid place-items-center shrink-0 <?= $active ? 'bg-brand/10 text-brand' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' ?>"><i data-lucide="<?= e($c['icon'] ?: 'car') ?>" class="w-5 h-5"></i></span>
        <div class="min-w-0 flex-1">
          <p class="font-medium text-navy dark:text-white truncate"><?= e($c['name']) ?></p>
          <p class="text-xs text-slate-400"><?= (int)$c['vehicle_count'] ?> vehículo<?= (int)$c['vehicle_count']===1?'':'s' ?> · <code class="text-[11px]"><?= e($c['slug']) ?></code></p>
        </div>
        <?php if (!$active): ?><span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 text-[11px] font-semibold">Inactiva</span><?php endif; ?>
        <a href="<?= url('/admin/vehicles?category_id='.$c['id']) ?>" class="text-xs text-slate-400 hover:text-brand hidden sm:block">Ver</a>
        <?php if ($canManage): ?>
        <div class="flex items-center gap-1">
          <a href="<?= url('/admin/categories?edit='.$c['id']) ?>" class="p-2 rounded-lg hover:bg-paper dark:hover:bg-slate-800 text-slate-400 hover:text-navy dark:hover:text-white" title="Editar"><i data-lucide="pencil" class="w-4 h-4"></i></a>
          <form method="POST" action="<?= url('/admin/categories/delete/'.$c['id']) ?>" data-confirm="Los vehículos asignados a esta categoría quedarán sin categoría." data-confirm-title="¿Eliminar categoría?">
            <?= csrf_field() ?>
            <button class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-slate-400 hover:text-red-600" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
          </form>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if (empty($categories)): ?><p class="px-6 py-12 text-center text-slate-400 text-sm">Aún no hay categorías.</p><?php endif; ?>
    </div>
  </div>
</div>
