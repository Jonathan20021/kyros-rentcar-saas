<?php
use App\Models\Expense;
$isEdit = !empty($expense);
$action = $isEdit ? url('/admin/expenses/update/'.$expense['id']) : url('/admin/expenses');
function eval2($e,$k,$d=''){ return e($e[$k] ?? $d); }
?>
<div class="max-w-2xl mx-auto">
  <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1"><?= $isEdit ? 'Editar gasto' : 'Registrar gasto' ?></h1>
  <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Registra un costo operativo de tu negocio</p>

  <form method="POST" action="<?= $action ?>" class="card p-6 space-y-5">
    <?= csrf_field() ?>
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Descripción *</label><input name="description" required maxlength="200" value="<?= eval2($expense,'description') ?>" placeholder="Combustible flotilla, alquiler local…" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Monto *</label><input type="number" step="0.01" min="0" name="amount" required value="<?= eval2($expense,'amount') ?>" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Fecha *</label><input type="date" name="expense_date" required value="<?= eval2($expense,'expense_date', date('Y-m-d')) ?>" class="fld"></div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Categoría *</label>
        <select name="category" class="fld">
          <?php foreach (Expense::CATEGORIES as $k=>$lbl): ?><option value="<?= $k ?>" <?= (($expense['category'] ?? 'other')===$k)?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Método de pago *</label>
        <select name="payment_method" class="fld">
          <?php foreach (Expense::METHODS as $k=>$lbl): ?><option value="<?= $k ?>" <?= (($expense['payment_method'] ?? 'cash')===$k)?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?>
        </select>
      </div>
      <?php if (!empty($locations)): ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Sucursal</label>
        <select name="location_id" class="fld"><option value="">— Ninguna —</option>
          <?php foreach ($locations as $l): ?><option value="<?= $l['id'] ?>" <?= (($expense['location_id'] ?? '')==$l['id'])?'selected':'' ?>><?= e($l['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Vehículo (opcional)</label>
        <select name="vehicle_id" class="fld"><option value="">— Ninguno —</option>
          <?php foreach ($vehicles as $v): ?><option value="<?= $v['id'] ?>" <?= (($expense['vehicle_id'] ?? '')==$v['id'])?'selected':'' ?>><?= e($v['brand'].' '.$v['model'].' · '.($v['plate_number'] ?? 's/p')) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div><label class="block text-sm font-medium mb-1.5">Proveedor</label><input name="vendor" maxlength="150" value="<?= eval2($expense,'vendor') ?>" class="fld"></div>
      <div><label class="block text-sm font-medium mb-1.5">Referencia / No. factura</label><input name="reference" maxlength="80" value="<?= eval2($expense,'reference') ?>" class="fld"></div>
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Notas</label><textarea name="notes" rows="2" maxlength="500" class="fld"><?= eval2($expense,'notes') ?></textarea></div>
    </div>
    <div class="flex items-center gap-3">
      <button type="submit" class="k-btn k-btn-grad !px-6"><?= $isEdit ? 'Guardar cambios' : 'Registrar gasto' ?></button>
      <a href="<?= url('/admin/expenses') ?>" class="k-btn k-btn-outline !px-6">Cancelar</a>
    </div>
  </form>
</div>
