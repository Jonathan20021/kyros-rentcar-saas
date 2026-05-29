<?php
$isEdit = !empty($customer);
$action = $isEdit ? url('/admin/customers/update/' . $customer['id']) : url('/admin/customers');
$inputCls = 'fld';
function cval($c,$k,$d=''){ return e($c[$k] ?? $d); }
?>
<div class="max-w-3xl mx-auto">
  <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1"><?= $isEdit ? 'Editar cliente' : 'Nuevo cliente' ?></h1>
  <p class="text-sm text-slate-500 mb-6">Datos del cliente y documentos</p>

  <form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="space-y-6">
    <?= csrf_field() ?>
    <div class="card p-6">
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Nombre *</label><input name="first_name" required value="<?= cval($customer,'first_name') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Apellido</label><input name="last_name" value="<?= cval($customer,'last_name') ?>" class="<?= $inputCls ?>"></div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Tipo de documento *</label>
          <select name="document_type" class="<?= $inputCls ?>">
            <?php foreach (['cedula'=>'Cedula','passport'=>'Pasaporte','license'=>'Licencia','rnc'=>'RNC'] as $k=>$lbl): ?>
              <option value="<?= $k ?>" <?= (($customer['document_type'] ?? 'cedula')===$k)?'selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div><label class="block text-sm font-medium mb-1.5">Numero de documento</label><input name="document_number" value="<?= cval($customer,'document_number') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Nacionalidad</label><input name="nationality" value="<?= cval($customer,'nationality') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Fecha nacimiento</label><input type="date" name="birth_date" value="<?= cval($customer,'birth_date') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Telefono</label><input name="phone" value="<?= cval($customer,'phone') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">WhatsApp</label><input name="whatsapp" value="<?= cval($customer,'whatsapp') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Email</label><input type="email" name="email" value="<?= cval($customer,'email') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Direccion</label><input name="address" value="<?= cval($customer,'address') ?>" class="<?= $inputCls ?>"></div>
      </div>
    </div>

    <div class="card p-6">
      <h2 class="font-semibold mb-4 flex items-center gap-2"><i data-lucide="id-card" class="w-4 h-4 text-brand"></i> Licencia y clasificacion</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">No. Licencia</label><input name="license_number" value="<?= cval($customer,'license_number') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Vencimiento licencia</label><input type="date" name="license_expiration" value="<?= cval($customer,'license_expiration') ?>" class="<?= $inputCls ?>"></div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Nivel de riesgo</label>
          <select name="risk_level" class="<?= $inputCls ?>">
            <?php foreach (['low'=>'Bajo','medium'=>'Medio','high'=>'Alto'] as $k=>$lbl): ?>
              <option value="<?= $k ?>" <?= (($customer['risk_level'] ?? 'low')===$k)?'selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Estado</label>
          <select name="status" class="<?= $inputCls ?>">
            <?php foreach (['active'=>'Activo','blocked'=>'Bloqueado','blacklist'=>'Lista negra','pending'=>'Pendiente'] as $k=>$lbl): ?>
              <option value="<?= $k ?>" <?= (($customer['status'] ?? 'active')===$k)?'selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Licencia (frontal)</label><input type="file" name="license_front_image" accept="image/*,application/pdf" class="<?= $inputCls ?>"></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Notas</label><textarea name="notes" rows="2" class="<?= $inputCls ?>"><?= cval($customer,'notes') ?></textarea></div>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button type="submit" class="k-btn k-btn-grad !px-6"><?= $isEdit ? 'Guardar' : 'Crear cliente' ?></button>
      <a href="<?= url('/admin/customers') ?>" class="k-btn k-btn-outline !px-6">Cancelar</a>
    </div>
  </form>
</div>
