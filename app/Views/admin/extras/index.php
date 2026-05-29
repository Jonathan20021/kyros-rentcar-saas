<?php
use App\Models\Extra;
$canManage = can('catalog.manage');
$isEdit = !empty($editing);
$action = $isEdit ? url('/admin/extras/update/'.$editing['id']) : url('/admin/extras');
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Servicios adicionales</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Extras que sumas a una reserva: GPS, silla de bebé, conductor adicional, seguro…</p>
  </div>
</div>

<div class="grid lg:grid-cols-[320px_1fr] gap-5">
  <?php if ($canManage): ?>
  <div class="card p-5 reveal h-fit">
    <h2 class="font-display font-bold text-navy dark:text-white mb-4 flex items-center gap-2">
      <i data-lucide="<?= $isEdit ? 'pencil' : 'plus' ?>" class="w-4 h-4 text-brand"></i>
      <?= $isEdit ? 'Editar servicio' : 'Nuevo servicio' ?>
    </h2>
    <form method="POST" action="<?= $action ?>" class="space-y-4">
      <?= csrf_field() ?>
      <div><label class="block text-sm font-medium mb-1.5">Nombre *</label><input name="name" required maxlength="120" value="<?= e($isEdit ? $editing['name'] : '') ?>" placeholder="GPS, Silla de bebé…" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Descripción</label><input name="description" maxlength="255" value="<?= e($isEdit ? ($editing['description'] ?? '') : '') ?>" class="fld"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-sm font-medium mb-1.5">Precio *</label><input type="number" step="0.01" min="0" name="price" required value="<?= e($isEdit ? $editing['price'] : '') ?>" class="fld"></div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Cobro</label>
          <select name="charge_type" class="fld">
            <?php foreach (Extra::CHARGE_TYPES as $k=>$lbl): ?>
              <option value="<?= $k ?>" <?= ($isEdit && $editing['charge_type']===$k)?'selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Estado</label>
        <select name="status" class="fld">
          <option value="active" <?= ($isEdit && $editing['status']==='inactive')?'':'selected' ?>>Activo</option>
          <option value="inactive" <?= ($isEdit && $editing['status']==='inactive')?'selected':'' ?>>Inactivo</option>
        </select>
      </div>
      <div class="flex gap-2 pt-1">
        <button type="submit" class="k-btn k-btn-grad flex-1"><?= $isEdit ? 'Guardar' : 'Crear' ?></button>
        <?php if ($isEdit): ?><a href="<?= url('/admin/extras') ?>" class="k-btn k-btn-outline">Cancelar</a><?php endif; ?>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="card overflow-hidden reveal <?= $canManage ? '' : 'lg:col-span-2' ?>">
    <div class="px-6 py-4 border-b hairline font-display font-bold text-navy dark:text-white flex items-center justify-between">
      Servicios <span class="text-xs font-medium text-slate-400"><?= count($extras) ?></span>
    </div>
    <div class="divide-y hairline">
      <?php foreach ($extras as $x): $active = $x['status']==='active'; ?>
      <div class="flex items-center gap-3 px-6 py-3.5 hover:bg-paper dark:hover:bg-slate-800/40">
        <span class="w-10 h-10 rounded-xl grid place-items-center shrink-0 <?= $active ? 'bg-brand/10 text-brand' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' ?>"><i data-lucide="sparkles" class="w-5 h-5"></i></span>
        <div class="min-w-0 flex-1">
          <p class="font-medium text-navy dark:text-white truncate"><?= e($x['name']) ?><?php if(!$active): ?> <span class="text-[11px] font-semibold text-slate-400">(inactivo)</span><?php endif; ?></p>
          <p class="text-xs text-slate-400 truncate"><?= e($x['description'] ?: Extra::CHARGE_TYPES[$x['charge_type']]) ?> · usado <?= (int)$x['used_count'] ?>×</p>
        </div>
        <div class="text-right shrink-0">
          <p class="font-bold text-navy dark:text-white tnum"><?= money($x['price']) ?></p>
          <p class="text-[11px] text-slate-400"><?= Extra::CHARGE_TYPES[$x['charge_type']] ?></p>
        </div>
        <?php if ($canManage): ?>
        <div class="flex items-center gap-1">
          <a href="<?= url('/admin/extras?edit='.$x['id']) ?>" class="p-2 rounded-lg hover:bg-paper dark:hover:bg-slate-800 text-slate-400 hover:text-navy dark:hover:text-white" title="Editar"><i data-lucide="pencil" class="w-4 h-4"></i></a>
          <form method="POST" action="<?= url('/admin/extras/delete/'.$x['id']) ?>" data-confirm="Las reservas que lo incluyan no se verán afectadas." data-confirm-title="¿Eliminar este servicio?">
            <?= csrf_field() ?>
            <button class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-slate-400 hover:text-red-600" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
          </form>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if (empty($extras)): ?><p class="px-6 py-12 text-center text-slate-400 text-sm">Aún no hay servicios.</p><?php endif; ?>
    </div>
  </div>
</div>
