<div class="max-w-2xl mx-auto">
  <h1 class="font-display text-2xl font-bold text-navy mb-1">Nueva incidencia</h1>
  <p class="text-sm text-slate-500 mb-6">Registra una multa, daño o reclamación.</p>

  <form method="POST" action="<?= url('/admin/incidents') ?>" class="card p-6 space-y-5">
    <?= csrf_field() ?>
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Tipo *</label>
        <select name="type" class="fld">
          <?php foreach (['traffic_fine'=>'Multa de tránsito','exterior_damage'=>'Daño exterior','interior_damage'=>'Daño interior','accident'=>'Accidente','theft'=>'Robo','late'=>'Retraso','fuel'=>'Combustible','cleaning'=>'Limpieza','key_loss'=>'Pérdida de llave','other'=>'Otro'] as $k=>$lbl): ?>
            <option value="<?= $k ?>"><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label class="block text-sm font-medium mb-1.5">Monto</label><input type="number" step="0.01" name="amount" value="0" class="fld"></div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Vehículo</label>
        <select name="vehicle_id" class="fld"><option value="">—</option><?php foreach ($vehicles as $v): ?><option value="<?= $v['id'] ?>"><?= e($v['brand'].' '.$v['model']) ?> · <?= e($v['plate_number'] ?? 's/p') ?></option><?php endforeach; ?></select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Cliente</label>
        <select name="customer_id" class="fld"><option value="">—</option><?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e(trim($c['first_name'].' '.$c['last_name'])) ?></option><?php endforeach; ?></select>
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1.5">Contrato relacionado</label>
        <select name="contract_id" class="fld"><option value="">—</option><?php foreach ($contracts as $ct): ?><option value="<?= $ct['id'] ?>"><?= e($ct['contract_number']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Descripción</label><textarea name="description" rows="3" class="fld"></textarea></div>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="k-btn k-btn-grad">Registrar incidencia</button>
      <a href="<?= url('/admin/incidents') ?>" class="k-btn k-btn-outline">Cancelar</a>
    </div>
  </form>
</div>
