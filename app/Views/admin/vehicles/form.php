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
    <?php
      $localeTenant = \App\Models\Tenant::find(\App\Core\Auth::tenantId(), null);
      $expirations = \App\Services\LocaleService::vehicleExpirationFields(strtoupper($localeTenant['country'] ?? 'DO'));
    ?>
    <div class="card p-6">
      <h2 class="font-semibold mb-4 flex items-center gap-2">
        <i data-lucide="calendar-clock" class="w-4 h-4 text-brand"></i>
        Vencimientos
        <span class="text-[11px] font-normal text-slate-400 ml-1">· <?= ($localeTenant['country'] ?? 'DO') === 'CO' ? '🇨🇴 Colombia' : '🇩🇴 República Dominicana' ?></span>
      </h2>
      <div class="grid sm:grid-cols-4 gap-4">
        <?php foreach ($expirations as $exp): ?>
          <div>
            <label class="block text-sm font-medium mb-1.5 flex items-center gap-1.5">
              <i data-lucide="<?= e($exp['icon']) ?>" class="w-3.5 h-3.5 <?= $exp['critical'] ? 'text-red-500' : 'text-slate-400' ?>"></i>
              <?= e($exp['label']) ?>
              <?php if ($exp['critical']): ?><span class="text-[9.5px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-red-50 text-red-600 ml-auto">Crítico</span><?php endif; ?>
            </label>
            <input type="date" name="<?= e($exp['col']) ?>" value="<?= vval($vehicle, $exp['col']) ?>" class="<?= $inputCls ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Images -->
    <div class="card p-6" x-data="vehicleImageManager()">
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-5">
        <div>
          <h2 class="font-semibold flex items-center gap-2"><i data-lucide="image" class="w-4 h-4 text-brand"></i> Imagenes publicas</h2>
          <p class="text-xs text-slate-500 mt-1">La imagen principal encabeza la ficha publica; la galeria aparece debajo como selector.</p>
        </div>
        <?php if (!empty($images)): ?>
          <span class="px-2.5 py-1 rounded-full bg-paper border hairline text-xs text-slate-500 self-start"><?= count($images) ?> en galeria</span>
        <?php endif; ?>
      </div>

      <div class="grid lg:grid-cols-2 gap-5">
        <div class="rounded-2xl border hairline bg-paper/60 p-4">
          <label class="block text-sm font-semibold text-navy dark:text-white mb-2">Imagen principal</label>
          <input type="file" name="main_image" accept="image/*" class="<?= $inputCls ?>" @change="previewMain($event)">
          <div class="mt-3 aspect-[16/10] rounded-xl overflow-hidden bg-white dark:bg-slate-900 border hairline grid place-items-center">
            <template x-if="mainPreview"><img :src="mainPreview" class="w-full h-full object-cover" alt="Preview principal"></template>
            <?php if ($isEdit && !empty($vehicle['main_image'])): ?>
              <img x-show="!mainPreview" src="<?= e(media($vehicle['main_image'])) ?>" class="w-full h-full object-cover" alt="Imagen principal actual">
            <?php else: ?>
              <div x-show="!mainPreview" class="text-center text-slate-400">
                <i data-lucide="car" class="w-9 h-9 mx-auto mb-2"></i>
                <span class="text-xs">Sin imagen principal</span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="rounded-2xl border hairline bg-paper/60 p-4">
          <label class="block text-sm font-semibold text-navy dark:text-white mb-2">Galeria</label>
          <input type="file" name="gallery[]" accept="image/*" multiple class="<?= $inputCls ?>" @change="previewGallery($event)">
          <div class="mt-3 grid grid-cols-3 gap-2" x-show="galleryPreview.length">
            <template x-for="src in galleryPreview" :key="src">
              <img :src="src" class="aspect-[4/3] w-full rounded-lg object-cover border hairline bg-white" alt="Preview galeria">
            </template>
          </div>
          <div class="mt-3 rounded-xl border border-dashed border-slate-300/80 dark:border-white/10 p-5 text-center text-xs text-slate-500" x-show="!galleryPreview.length">
            Puedes seleccionar varias fotos del mismo vehiculo en una sola carga.
          </div>
        </div>
      </div>

      <?php if (!empty($images)): ?>
      <div class="mt-6">
        <div class="flex items-center justify-between gap-3 mb-3">
          <h3 class="text-sm font-semibold text-navy dark:text-white">Galeria actual</h3>
          <p class="text-xs text-slate-500">Marca una foto como principal o retirala de la publicacion.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <?php foreach ($images as $img): $isMainImg = (int)($img['is_main'] ?? 0) === 1 || (($vehicle['main_image'] ?? '') === ($img['path'] ?? '')); ?>
            <div class="rounded-xl border hairline bg-white dark:bg-slate-950 overflow-hidden">
              <div class="relative aspect-[16/10] bg-paper">
                <img src="<?= e(media($img['path'])) ?>" class="w-full h-full object-cover" alt="Imagen del vehiculo">
                <?php if ($isMainImg): ?><span class="absolute left-2 top-2 px-2 py-1 rounded-lg bg-brand text-white text-[11px] font-bold">Principal</span><?php endif; ?>
              </div>
              <div class="p-3 flex items-center gap-2">
                <?php if (!$isMainImg): ?>
                  <button type="submit" form="vehicle-image-main-<?= (int)$img['id'] ?>" class="k-btn k-btn-outline !h-9 !px-3 text-xs flex-1 justify-center">Principal</button>
                <?php else: ?>
                  <span class="k-btn k-btn-ghost !h-9 !px-3 text-xs flex-1 justify-center opacity-70">Publica</span>
                <?php endif; ?>
                <button type="submit" form="vehicle-image-delete-<?= (int)$img['id'] ?>" class="icon-btn !w-9 !h-9 hover:!text-brand" title="Eliminar imagen"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
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

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
      <button type="submit" class="k-btn k-btn-grad !px-6 w-full sm:w-auto justify-center"><?= $isEdit ? 'Guardar cambios' : 'Crear vehiculo' ?></button>
      <a href="<?= url('/admin/vehicles') ?>" class="k-btn k-btn-outline !px-6 w-full sm:w-auto justify-center">Cancelar</a>
    </div>
  </form>

  <?php if (!empty($images)): foreach ($images as $img): ?>
    <form id="vehicle-image-main-<?= (int)$img['id'] ?>" method="POST" action="<?= url('/admin/vehicles/image/main/' . $img['id']) ?>" class="hidden"><?= csrf_field() ?></form>
    <form id="vehicle-image-delete-<?= (int)$img['id'] ?>" method="POST" action="<?= url('/admin/vehicles/image/delete/' . $img['id']) ?>" class="hidden" data-confirm="La imagen saldra de la pagina publica del vehiculo." data-confirm-title="Eliminar imagen"><?= csrf_field() ?></form>
  <?php endforeach; endif; ?>
</div>

<?php \App\Core\View::push('scripts', <<<'HTML'
<script>
function vehicleImageManager(){
  return {
    mainPreview: '',
    galleryPreview: [],
    previewMain(event){
      const file = event.target.files && event.target.files[0];
      if (!file) { this.mainPreview = ''; return; }
      this.mainPreview = URL.createObjectURL(file);
      this.$nextTick(() => window.lucide && lucide.createIcons());
    },
    previewGallery(event){
      this.galleryPreview.forEach(src => URL.revokeObjectURL(src));
      this.galleryPreview = Array.from(event.target.files || []).map(file => URL.createObjectURL(file));
    }
  }
}
</script>
HTML); ?>
