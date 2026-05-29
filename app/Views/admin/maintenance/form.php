<?php
$isEdit = !empty($record);
$action = $isEdit ? url('/admin/maintenance/update/' . $record['id']) : url('/admin/maintenance');
$val = fn(string $k, $d = '') => e($isEdit ? ($record[$k] ?? $d) : $d);
?>
<div class="max-w-2xl mx-auto">
  <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1"><?= $isEdit ? 'Editar mantenimiento' : 'Nuevo mantenimiento' ?></h1>
  <p class="text-sm text-slate-500 mb-6">Registra o programa un servicio para un vehículo.</p>

  <form method="POST" action="<?= $action ?>" class="card p-6 space-y-5">
    <?= csrf_field() ?>
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1.5">Vehículo *</label>
        <select name="vehicle_id" required class="fld">
          <option value="">Selecciona…</option>
          <?php foreach ($vehicles as $v): ?>
            <option value="<?= (int)$v['id'] ?>" <?= $isEdit && (int)$record['vehicle_id'] === (int)$v['id'] ? 'selected' : '' ?>>
              <?= e($v['brand'].' '.$v['model']) ?> · <?= e($v['plate_number'] ?? 's/p') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Tipo *</label>
        <select name="maintenance_type" class="fld">
          <?php $types = ['oil'=>'Cambio de aceite','tires'=>'Gomas','brakes'=>'Frenos','battery'=>'Batería','alignment'=>'Alineación','mechanical'=>'Mecánica general','deep_clean'=>'Limpieza profunda','paint'=>'Pintura','inspection'=>'Inspección','other'=>'Otro'];
          foreach ($types as $k=>$lbl): ?>
            <option value="<?= $k ?>" <?= $isEdit && $record['maintenance_type'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Estado *</label>
        <select name="status" class="fld">
          <?php $states = ['scheduled'=>'Programado','in_progress'=>'En proceso (bloquea vehículo)','completed'=>'Completado','cancelled'=>'Cancelado'];
          foreach ($states as $k=>$lbl): ?>
            <option value="<?= $k ?>" <?= $isEdit && $record['status'] === $k ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label class="block text-sm font-medium mb-1.5">Proveedor / taller</label><input name="provider" value="<?= $val('provider') ?>" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Costo (RD$)</label><input type="number" step="0.01" min="0" name="cost" value="<?= $val('cost', '0') ?>" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Kilometraje</label><input type="number" min="0" name="mileage" value="<?= $val('mileage') ?>" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Fecha inicio</label><input type="date" name="start_date" value="<?= $val('start_date', date('Y-m-d')) ?>" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Fecha fin</label><input type="date" name="end_date" value="<?= $val('end_date') ?>" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Próximo servicio (fecha)</label><input type="date" name="next_due_date" value="<?= $val('next_due_date') ?>" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Próximo servicio (km)</label><input type="number" min="0" name="next_due_mileage" value="<?= $val('next_due_mileage') ?>" class="fld"></div>
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Descripción</label><textarea name="description" rows="2" class="fld"><?= $val('description') ?></textarea></div>
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Notas</label><textarea name="notes" rows="2" class="fld"><?= $val('notes') ?></textarea></div>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="k-btn k-btn-grad"><?= $isEdit ? 'Guardar cambios' : 'Crear mantenimiento' ?></button>
      <a href="<?= url('/admin/maintenance') ?>" class="k-btn k-btn-outline">Cancelar</a>
    </div>
  </form>
</div>
