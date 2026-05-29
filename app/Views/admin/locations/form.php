<?php
$isEdit = !empty($location);
$action = $isEdit ? url('/admin/locations/update/'.$location['id']) : url('/admin/locations');
function lval($l,$k,$d=''){ return e($l[$k] ?? $d); }
?>
<div class="max-w-2xl mx-auto">
  <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1"><?= $isEdit ? 'Editar sucursal' : 'Nueva sucursal' ?></h1>
  <p class="text-sm text-slate-500 dark:text-slate-400 mb-6"><?= $isEdit ? e($location['name']) : 'Agrega una ubicación a tu operación' ?></p>

  <form method="POST" action="<?= $action ?>" class="card p-6 space-y-5">
    <?= csrf_field() ?>
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Nombre *</label><input name="name" required maxlength="120" value="<?= lval($location,'name') ?>" placeholder="Sucursal Principal" class="fld"></div>
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Dirección</label><input name="address" maxlength="255" value="<?= lval($location,'address') ?>" placeholder="Av. Principal #123, Santo Domingo" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Teléfono</label><input name="phone" maxlength="30" value="<?= lval($location,'phone') ?>" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Encargado</label><input name="manager_name" maxlength="120" value="<?= lval($location,'manager_name') ?>" class="fld"></div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Estado *</label>
        <select name="status" class="fld">
          <option value="active" <?= (($location['status'] ?? 'active')==='active')?'selected':'' ?>>Activa</option>
          <option value="inactive" <?= (($location['status'] ?? '')==='inactive')?'selected':'' ?>>Inactiva</option>
        </select>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <button type="submit" class="k-btn k-btn-grad !px-6"><?= $isEdit ? 'Guardar cambios' : 'Crear sucursal' ?></button>
      <a href="<?= url('/admin/locations') ?>" class="k-btn k-btn-outline !px-6">Cancelar</a>
    </div>
  </form>
</div>
