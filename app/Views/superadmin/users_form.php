<?php
  $isEdit   = !empty($user);
  $isSystem = $isEdit ? ($user['tenant_id'] === null) : true; // default: Super Admin
  $curRole  = $isEdit ? (int) $user['role_id'] : 0;
  $curTenant= $isEdit ? (int) ($user['tenant_id'] ?? 0) : 0;
  $type     = old('account_type') ?: ($isSystem ? 'system' : 'tenant');
?>
<div class="max-w-2xl mx-auto">
  <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-1"><?= $isEdit ? 'Editar usuario' : 'Nuevo usuario' ?></h1>
  <p class="text-sm text-slate-500 mb-6"><?= $isEdit ? 'Actualiza los datos y permisos del usuario' : 'Crea un Super Admin o un usuario de empresa' ?></p>

  <form method="POST" action="<?= $isEdit ? url('/super-admin/users/update/' . $user['id']) : url('/super-admin/users') ?>"
        class="bg-white dark:bg-slate-900 rounded-2xl border hairline shadow-card p-6 space-y-5">
    <?= csrf_field() ?>

    <!-- Account type -->
    <div>
      <label class="block text-sm font-medium mb-2">Tipo de cuenta</label>
      <div class="grid sm:grid-cols-2 gap-3" id="accountType">
        <label class="flex items-start gap-3 p-3 rounded-xl border hairline cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 has-[:checked]:border-indigo-400 has-[:checked]:ring-1 has-[:checked]:ring-indigo-300">
          <input type="radio" name="account_type" value="system" class="mt-1" <?= $type === 'system' ? 'checked' : '' ?>>
          <span>
            <span class="block font-medium text-sm text-slate-900 dark:text-white">Super Admin (Kyros)</span>
            <span class="block text-xs text-slate-500">Acceso total a la plataforma</span>
          </span>
        </label>
        <label class="flex items-start gap-3 p-3 rounded-xl border hairline cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 has-[:checked]:border-indigo-400 has-[:checked]:ring-1 has-[:checked]:ring-indigo-300">
          <input type="radio" name="account_type" value="tenant" class="mt-1" <?= $type === 'tenant' ? 'checked' : '' ?>>
          <span>
            <span class="block font-medium text-sm text-slate-900 dark:text-white">Usuario de empresa</span>
            <span class="block text-xs text-slate-500">Asignado a una rent car con un rol</span>
          </span>
        </label>
      </div>
    </div>

    <!-- Tenant + role (only for tenant users) -->
    <div class="grid sm:grid-cols-2 gap-4 js-tenant-fields" <?= $type === 'system' ? 'style="display:none"' : '' ?>>
      <div>
        <label class="block text-sm font-medium mb-1.5">Empresa</label>
        <select name="tenant_id" class="fld">
          <option value="">— Selecciona —</option>
          <?php foreach ($tenants as $t): ?>
            <option value="<?= $t['id'] ?>" <?= ((int) (old('tenant_id') ?: $curTenant) === (int) $t['id']) ? 'selected' : '' ?>><?= e($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Rol</label>
        <select name="role_id" class="fld">
          <?php foreach ($tenantRoles as $r): ?>
            <option value="<?= $r['id'] ?>" <?= ((int) (old('role_id') ?: $curRole) === (int) $r['id']) ? 'selected' : '' ?>><?= e($r['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Identity -->
    <div class="grid sm:grid-cols-2 gap-4 border-t border-slate-100 dark:border-slate-800 pt-5">
      <div>
        <label class="block text-sm font-medium mb-1.5">Nombre *</label>
        <input name="name" required value="<?= e($user['name'] ?? old('name')) ?>" class="fld">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Correo *</label>
        <input type="email" name="email" required value="<?= e($user['email'] ?? old('email')) ?>" class="fld">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Teléfono</label>
        <input name="phone" value="<?= e($user['phone'] ?? old('phone')) ?>" class="fld">
      </div>
      <?php if ($isEdit): ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Estado</label>
        <select name="status" class="fld">
          <?php foreach (['active'=>'Activo','inactive'=>'Inactivo','blocked'=>'Bloqueado'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($user['status'] === $k) ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1.5">Contraseña <?= $isEdit ? '' : '*' ?></label>
        <input type="text" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8" value="<?= old('password') ?>" class="fld"
               placeholder="<?= $isEdit ? 'Dejar en blanco para no cambiarla' : 'Mín. 8 caracteres' ?>">
      </div>
    </div>

    <div class="flex items-center gap-3 pt-2">
      <button type="submit" class="k-btn k-btn-grad !px-5"><?= $isEdit ? 'Guardar cambios' : 'Crear usuario' ?></button>
      <a href="<?= url('/super-admin/users') ?>" class="k-btn k-btn-outline !px-5">Cancelar</a>
    </div>
  </form>
</div>

<script>
(function () {
  var radios = document.querySelectorAll('#accountType input[name="account_type"]');
  var fields = document.querySelector('.js-tenant-fields');
  function sync() {
    var val = document.querySelector('#accountType input[name="account_type"]:checked');
    if (fields) fields.style.display = (val && val.value === 'tenant') ? '' : 'none';
  }
  radios.forEach(function (r) { r.addEventListener('change', sync); });
  sync();
})();
</script>
