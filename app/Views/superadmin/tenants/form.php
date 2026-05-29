<?php $isEdit = !empty($tenant); ?>
<div class="max-w-2xl mx-auto">
  <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-1"><?= $isEdit ? 'Editar empresa' : 'Nueva empresa' ?></h1>
  <p class="text-sm text-slate-500 mb-6"><?= $isEdit ? 'Actualiza los datos de la rent car' : 'Registra una nueva rent car y su dueno' ?></p>

  <form method="POST" action="<?= $isEdit ? url('/super-admin/tenants/update/' . $tenant['id']) : url('/super-admin/tenants') ?>"
        class="bg-white dark:bg-slate-900 rounded-2xl border hairline shadow-card p-6 space-y-5">
    <?= csrf_field() ?>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Nombre comercial *</label>
        <input name="name" required value="<?= e($tenant['name'] ?? old('name')) ?>" class="fld">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Razon social</label>
        <input name="legal_name" value="<?= e($tenant['legal_name'] ?? old('legal_name')) ?>" class="fld">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Correo *</label>
        <input type="email" name="email" required value="<?= e($tenant['email'] ?? old('email')) ?>" class="fld">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Telefono</label>
        <input name="phone" value="<?= e($tenant['phone'] ?? old('phone')) ?>" class="fld">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Plan *</label>
        <select name="plan_id" required class="fld">
          <?php foreach ($plans as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (($tenant['plan_id'] ?? 1) == $p['id']) ? 'selected' : '' ?>><?= e($p['name']) ?> — <?= money($p['price_monthly']) ?>/mes</option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if ($isEdit): ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Estado</label>
        <select name="status" class="fld">
          <?php foreach (['trial'=>'Prueba','active'=>'Activa','suspended'=>'Suspendida','inactive'=>'Inactiva'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($tenant['status'] === $k) ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
    </div>

    <?php if (!$isEdit): ?>
    <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
      <p class="font-semibold text-sm mb-3">Dueno de la empresa</p>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1.5">Nombre *</label>
          <input name="owner_name" required value="<?= old('owner_name') ?>" class="fld">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Correo del dueno *</label>
          <input type="email" name="owner_email" required value="<?= old('owner_email') ?>" class="fld">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium mb-1.5">Contrasena temporal *</label>
          <input type="text" name="owner_password" required minlength="8" value="<?= old('owner_password') ?>" class="fld" placeholder="Min. 8 caracteres">
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="flex items-center gap-3 pt-2">
      <button type="submit" class="k-btn k-btn-grad !px-5"><?= $isEdit ? 'Guardar cambios' : 'Crear empresa' ?></button>
      <a href="<?= url('/super-admin/tenants') ?>" class="k-btn k-btn-outline !px-5">Cancelar</a>
    </div>
  </form>
</div>
