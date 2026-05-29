<?php
$isEdit = !empty($vehicle);
$action = $isEdit ? url('/admin/vehicles/update/' . $vehicle['id']) : url('/admin/vehicles');
$featuresStr = '';
if ($isEdit && !empty($vehicle['features'])) {
    $f = json_decode($vehicle['features'], true);
    if (is_array($f)) $featuresStr = implode(', ', $f);
}
function vval($vehicle, $k, $d=''){ return e($vehicle[$k] ?? $d); }
$inputCls = 'fld';
?>
<div class="max-w-4xl mx-auto">
  <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1"><?= $isEdit ? 'Editar vehiculo' : 'Nuevo vehiculo' ?></h1>
  <p class="text-sm text-slate-500 mb-6"><?= $isEdit ? e($vehicle['brand'].' '.$vehicle['model']) : 'Agrega un vehiculo a tu flotilla' ?></p>

  <form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="space-y-6">
    <?= csrf_field() ?>

    <!-- Basic -->
    <div class="card p-6">
      <h2 class="font-semibold mb-4 flex items-center gap-2"><i data-lucide="info" class="w-4 h-4 text-brand"></i> Informacion basica</h2>
      <div class="grid sm:grid-cols-3 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Marca *</label><input name="brand" required value="<?= vval($vehicle,'brand') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Modelo *</label><input name="model" required value="<?= vval($vehicle,'model') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Version</label><input name="version" value="<?= vval($vehicle,'version') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Ano</label><input type="number" name="year" value="<?= vval($vehicle,'year', date('Y')) ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Placa</label><input name="plate_number" value="<?= vval($vehicle,'plate_number') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">VIN</label><input name="vin" value="<?= vval($vehicle,'vin') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Color</label><input name="color" value="<?= vval($vehicle,'color') ?>" class="<?= $inputCls ?>"></div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Categoria</label>
          <select name="category_id" class="<?= $inputCls ?>">
            <option value="">Seleccionar</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= (($vehicle['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Sucursal</label>
          <select name="location_id" class="<?= $inputCls ?>">
            <option value="">Sin asignar</option>
            <?php foreach (($locations ?? []) as $loc): ?>
              <option value="<?= $loc['id'] ?>" <?= (($vehicle['location_id'] ?? '') == $loc['id']) ? 'selected' : '' ?>><?= e($loc['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Estado *</label>
          <select name="status" class="<?= $inputCls ?>">
            <?php foreach (\App\Models\Vehicle::STATUSES as $s): ?>
              <option value="<?= $s ?>" <?= (($vehicle['status'] ?? 'available') === $s) ? 'selected' : '' ?>><?= status_label($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Specs -->
    <div class="card p-6">
      <h2 class="font-semibold mb-4 flex items-center gap-2"><i data-lucide="settings-2" class="w-4 h-4 text-brand"></i> Especificaciones</h2>
      <div class="grid sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1.5">Transmision *</label>
          <select name="transmission" class="<?= $inputCls ?>">
            <option value="automatic" <?= (($vehicle['transmission'] ?? '')==='automatic')?'selected':'' ?>>Automatica</option>
            <option value="manual" <?= (($vehicle['transmission'] ?? '')==='manual')?'selected':'' ?>>Manual</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Combustible *</label>
          <select name="fuel_type" class="<?= $inputCls ?>">
            <?php foreach (['gasoline'=>'Gasolina','diesel'=>'Diesel','electric'=>'Electrico','hybrid'=>'Hibrido','gas'=>'Gas'] as $k=>$lbl): ?>
              <option value="<?= $k ?>" <?= (($vehicle['fuel_type'] ?? 'gasoline')===$k)?'selected':'' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div><label class="block text-sm font-medium mb-1.5">Kilometraje</label><input type="number" name="mileage" value="<?= vval($vehicle,'mileage','0') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Pasajeros</label><input type="number" name="passengers" value="<?= vval($vehicle,'passengers','5') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Puertas</label><input type="number" name="doors" value="<?= vval($vehicle,'doors','4') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Maletas</label><input type="number" name="luggage_capacity" value="<?= vval($vehicle,'luggage_capacity','2') ?>" class="<?= $inputCls ?>"></div>
      </div>
      <div class="mt-4">
        <label class="block text-sm font-medium mb-1.5">Caracteristicas (separadas por coma)</label>
        <input name="features" value="<?= e($featuresStr) ?>" placeholder="Bluetooth, Camara reversa, A/C" class="<?= $inputCls ?>">
      </div>
      <div class="mt-4">
        <label class="block text-sm font-medium mb-1.5">Descripcion</label>
        <textarea name="description" rows="3" class="<?= $inputCls ?>"><?= vval($vehicle,'description') ?></textarea>
      </div>
    </div>

    <!-- Pricing -->
    <div class="card p-6">
      <h2 class="font-semibold mb-4 flex items-center gap-2"><i data-lucide="banknote" class="w-4 h-4 text-brand"></i> Precios</h2>
      <div class="grid sm:grid-cols-3 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Precio diario *</label><input type="number" step="0.01" name="daily_price" required value="<?= vval($vehicle,'daily_price','0') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Precio semanal</label><input type="number" step="0.01" name="weekly_price" value="<?= vval($vehicle,'weekly_price') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Precio mensual</label><input type="number" step="0.01" name="monthly_price" value="<?= vval($vehicle,'monthly_price') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Deposito</label><input type="number" step="0.01" name="deposit_amount" value="<?= vval($vehicle,'deposit_amount','0') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Seguro / dia</label><input type="number" step="0.01" name="insurance_price" value="<?= vval($vehicle,'insurance_price','0') ?>" class="<?= $inputCls ?>"></div>
      </div>
    </div>

    <!-- Documents -->
    <div class="card p-6">
      <h2 class="font-semibold mb-4 flex items-center gap-2"><i data-lucide="calendar-clock" class="w-4 h-4 text-brand"></i> Vencimientos</h2>
      <div class="grid sm:grid-cols-4 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Seguro</label><input type="date" name="insurance_expires" value="<?= vval($vehicle,'insurance_expires') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Marbete</label><input type="date" name="marbete_expires" value="<?= vval($vehicle,'marbete_expires') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Matricula</label><input type="date" name="plate_expires" value="<?= vval($vehicle,'plate_expires') ?>" class="<?= $inputCls ?>"></div>
        <div><label class="block text-sm font-medium mb-1.5">Inspeccion</label><input type="date" name="inspection_expires" value="<?= vval($vehicle,'inspection_expires') ?>" class="<?= $inputCls ?>"></div>
      </div>
    </div>

    <!-- Images -->
    <div class="card p-6">
      <h2 class="font-semibold mb-4 flex items-center gap-2"><i data-lucide="image" class="w-4 h-4 text-brand"></i> Imagenes</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1.5">Imagen principal</label>
          <input type="file" name="main_image" accept="image/*" class="<?= $inputCls ?>">
          <?php if ($isEdit && !empty($vehicle['main_image'])): ?>
            <img src="<?= e($vehicle['main_image']) ?>" class="mt-2 w-32 h-20 object-cover rounded-lg">
          <?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Galeria (multiples)</label>
          <input type="file" name="gallery[]" accept="image/*" multiple class="<?= $inputCls ?>">
        </div>
      </div>
      <?php if (!empty($images)): ?>
      <div class="flex flex-wrap gap-2 mt-3">
        <?php foreach ($images as $img): ?>
          <img src="<?= e($img['path']) ?>" class="w-20 h-16 object-cover rounded-lg border border-slate-200">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Flags + submit -->
    <div class="card p-6">
      <div class="flex flex-wrap gap-6">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_public" <?= (($vehicle['is_public'] ?? 1)) ? 'checked' : '' ?> class="rounded text-brand"> Visible en pagina publica</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" <?= (($vehicle['is_featured'] ?? 0)) ? 'checked' : '' ?> class="rounded text-brand"> Destacado</label>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <button type="submit" class="k-btn k-btn-grad !px-6"><?= $isEdit ? 'Guardar cambios' : 'Crear vehiculo' ?></button>
      <a href="<?= url('/admin/vehicles') ?>" class="k-btn k-btn-outline !px-6">Cancelar</a>
    </div>
  </form>
</div>
