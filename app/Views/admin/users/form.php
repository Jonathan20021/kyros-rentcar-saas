<?php $isEdit = !empty($user); $action = $isEdit ? url('/admin/users/update/'.$user['id']) : url('/admin/users'); ?>
<div class="max-w-xl mx-auto">
  <h1 class="font-display text-2xl font-bold text-navy mb-1"><?= $isEdit ? 'Editar usuario' : 'Invitar usuario' ?></h1>
  <p class="text-sm text-slate-500 mb-6"><?= $isEdit ? 'Actualiza los datos y permisos del usuario.' : 'Agrega un miembro a tu equipo y asígnale un rol.' ?></p>

  <form method="POST" action="<?= $action ?>" class="card p-6 space-y-5">
    <?= csrf_field() ?>
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Nombre completo *</label><input name="name" required value="<?= e($user['name'] ?? old('name')) ?>" class="fld"></div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1.5">Correo *</label>
        <input type="email" name="email" <?= $isEdit?'disabled':'required' ?> value="<?= e($user['email'] ?? old('email')) ?>" class="fld <?= $isEdit?'opacity-60':'' ?>">
        <?php if ($isEdit): ?><p class="text-xs text-slate-400 mt-1">El correo no se puede cambiar.</p><?php endif; ?>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Rol *</label>
        <select name="role_id" class="fld">
          <?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>" <?= (($user['role_id'] ?? 3)==$r['id'])?'selected':'' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div><label class="block text-sm font-medium mb-1.5">Teléfono</label><input name="phone" value="<?= e($user['phone'] ?? '') ?>" class="fld"></div>
      <?php if (!empty($locations)): ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Sucursal</label>
        <select name="location_id" class="fld">
          <option value="">Sin asignar</option>
          <?php foreach ($locations as $loc): ?><option value="<?= $loc['id'] ?>" <?= (($user['location_id'] ?? '')==$loc['id'])?'selected':'' ?>><?= e($loc['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <?php if ($isEdit): ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Estado</label>
        <select name="status" class="fld"><?php foreach (['active'=>'Activo','inactive'=>'Inactivo','blocked'=>'Bloqueado'] as $k=>$lbl): ?><option value="<?= $k ?>" <?= (($user['status']??'active')===$k)?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?></select>
      </div>
      <?php endif; ?>
      <div class="<?= $isEdit?'':'sm:col-span-2' ?>">
        <label class="block text-sm font-medium mb-1.5"><?= $isEdit ? 'Nueva contraseña' : 'Contraseña *' ?></label>
        <input type="password" name="password" <?= $isEdit?'':'required minlength="8"' ?> placeholder="<?= $isEdit?'Dejar en blanco para no cambiar':'Mín. 8 caracteres' ?>" class="fld">
      </div>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="k-btn k-btn-grad"><?= $isEdit ? 'Guardar cambios' : 'Agregar al equipo' ?></button>
      <a href="<?= url('/admin/users') ?>" class="k-btn k-btn-outline">Cancelar</a>
    </div>
  </form>
</div>
