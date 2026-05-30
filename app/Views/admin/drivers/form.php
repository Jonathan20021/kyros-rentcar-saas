<?php
use App\Models\Driver;
$isEdit = !empty($driver);
$action = $isEdit ? url('/admin/drivers/update/'.$driver['id']) : url('/admin/drivers');
$val = fn(string $k, $d = '') => e($isEdit ? ($driver[$k] ?? $d) : $d);
?>
<div class="max-w-4xl mx-auto">
  <div class="mb-5">
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white"><?= $isEdit ? 'Editar chofer' : 'Nuevo chofer' ?></h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Datos de identidad, licencia y tarifa.</p>
  </div>

  <form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="card p-6 space-y-6">
    <?= csrf_field() ?>

    <!-- Identity -->
    <div>
      <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Identidad</p>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Nombres *</label><input name="first_name" required maxlength="80" value="<?= $val('first_name') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Apellidos</label><input name="last_name" maxlength="80" value="<?= $val('last_name') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Cédula / Documento</label><input name="document_number" maxlength="40" value="<?= $val('document_number') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Teléfono</label><input name="phone" maxlength="30" value="<?= $val('phone') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Email</label><input type="email" name="email" maxlength="150" value="<?= $val('email') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Estado</label>
          <select name="status" class="fld">
            <?php foreach (Driver::STATUSES as $k=>$lbl): ?>
              <option value="<?= $k ?>" <?= ($isEdit && $driver['status']===$k) ? 'selected' : ($k==='active' && !$isEdit ? 'selected' : '') ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Dirección</label><input name="address" maxlength="255" value="<?= $val('address') ?>" class="fld"></div>
      </div>
    </div>

    <!-- License -->
    <div class="pt-2 border-t hairline">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 mt-4">Licencia de conducir</p>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Número de licencia</label><input name="license_number" maxlength="40" value="<?= $val('license_number') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Fecha de vencimiento</label><input type="date" name="license_expiration" value="<?= $val('license_expiration') ?>" class="fld"></div>
      </div>
    </div>

    <!-- Rates -->
    <div class="pt-2 border-t hairline">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 mt-4">Tarifas</p>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1.5">Tarifa por día (RD$)</label>
          <input type="number" step="0.01" min="0" name="daily_rate" value="<?= $val('daily_rate','0') ?>" class="fld">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Tarifa por hora (RD$)</label>
          <input type="number" step="0.01" min="0" name="hourly_rate" value="<?= $val('hourly_rate','0') ?>" class="fld">
        </div>
      </div>
    </div>

    <!-- Photo + notes -->
    <div class="pt-2 border-t hairline">
      <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 mt-4">Foto y notas</p>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1.5">Foto</label>
          <?php if ($isEdit && !empty($driver['photo'])): ?>
            <div class="mb-2 flex items-center gap-3">
              <img src="<?= e($driver['photo']) ?>" class="w-16 h-16 rounded-xl object-cover border hairline">
              <span class="text-xs text-slate-400">Foto actual (sube otra para reemplazar)</span>
            </div>
          <?php endif; ?>
          <input type="file" name="photo" accept="image/*" class="fld">
        </div>
        <div><label class="block text-sm font-medium mb-1.5">Notas</label><textarea name="notes" rows="4" class="fld" placeholder="Idiomas, especialidades, restricciones…"><?= $val('notes') ?></textarea></div>
      </div>
    </div>

    <div class="k-sticky flex gap-2 pt-2">
      <button type="submit" class="k-btn k-btn-grad"><?= $isEdit ? 'Guardar cambios' : 'Crear chofer' ?></button>
      <a href="<?= url('/admin/drivers') ?>" class="k-btn k-btn-outline">Cancelar</a>
    </div>
  </form>
</div>
