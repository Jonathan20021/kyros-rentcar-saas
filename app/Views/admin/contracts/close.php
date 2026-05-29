<?php
$v = $c['vehicle']; $cu = $c['customer'];
$startMileage = (int) ($c['start_mileage'] ?? 0);
$startFuel    = (int) ($c['start_fuel_level'] ?? 100);
$dailyRate    = (float) ($c['daily_rate'] ?? 0);
$hourlyLate   = $dailyRate > 0 ? round($dailyRate / 8, 2) : 250.0; // sensible default
$endIso       = $c['end_datetime'];
?>
<div class="max-w-3xl mx-auto"
     x-data='closeContract({
       startMileage: <?= $startMileage ?>,
       startFuel: <?= $startFuel ?>,
       endIso: <?= json_encode($endIso) ?>,
       hourlyLate: <?= $hourlyLate ?>,
       dailyRate: <?= $dailyRate ?>
     })' x-init="init()">

  <div class="mb-5">
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Cerrar contrato <?= e($c['contract_number']) ?></h1>
    <p class="text-sm text-slate-500">Registra la devolución del <?= e($v['brand'].' '.$v['model']) ?> · matrícula <?= e($v['plate_number'] ?? 's/placa') ?>.</p>
  </div>

  <!-- Live late-return banner -->
  <div x-show="lateHours > 0" x-cloak class="card p-4 mb-5 border-2 border-amber-200 bg-amber-50 dark:bg-amber-500/10 flex items-start gap-3">
    <i data-lucide="alarm-clock" class="w-5 h-5 text-amber-600 mt-0.5 shrink-0"></i>
    <div class="text-sm">
      <p class="font-semibold text-amber-900 dark:text-amber-100">Devolución tardía detectada</p>
      <p class="text-amber-800 dark:text-amber-200 mt-0.5">
        El cliente devolvió <span class="font-bold tnum" x-text="lateHours.toFixed(1)"></span> hora(s) tarde.
        Sugerencia: <span class="font-bold tnum" x-text="'RD$ ' + lateSuggestion.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></span> de cargo por mora.
        <button type="button" @click="applyLateFee()" class="ml-1 underline font-semibold">Aplicar</button>
      </p>
    </div>
  </div>

  <form method="POST" action="<?= url('/admin/contracts/close/'.$c['id']) ?>" enctype="multipart/form-data" class="card p-6 space-y-6">
    <?= csrf_field() ?>

    <!-- Fuel level slider -->
    <div>
      <div class="flex items-center justify-between mb-2">
        <label class="text-sm font-medium">Nivel de combustible al regresar</label>
        <span class="text-sm font-bold text-navy dark:text-white tnum"><span x-text="endFuel"></span>%</span>
      </div>
      <input type="range" min="0" max="100" step="5" name="end_fuel_level" x-model.number="endFuel"
             class="w-full h-2 rounded-full appearance-none bg-slate-200 dark:bg-slate-700"
             style="accent-color: var(--brand);">
      <div class="flex justify-between text-[10px] text-slate-400 mt-1.5 px-1">
        <span>E</span><span>¼</span><span>½</span><span>¾</span><span>F</span>
      </div>
      <p class="text-xs text-amber-600 mt-1.5" x-show="endFuel < startFuel" x-cloak>
        <i data-lucide="fuel" class="w-3 h-3 inline -mt-0.5"></i>
        Diferencia: <span x-text="startFuel - endFuel"></span>% menos del nivel entregado (<span x-text="startFuel"></span>%).
      </p>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 pt-4 border-t hairline">
      <div>
        <label class="block text-sm font-medium mb-1.5">Kilometraje de regreso</label>
        <input type="number" min="0" name="end_mileage" x-model.number="endMileage" class="fld">
        <p class="text-xs text-slate-400 mt-1">Salió con <span class="font-semibold text-slate-600 tnum"><?= number_format($startMileage) ?></span> km
          · <span class="text-brand font-semibold" x-show="kmDelta > 0">+<span x-text="kmDelta.toLocaleString()"></span> km</span></p>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Penalidades / cargos extra (RD$)</label>
        <input type="number" step="0.01" min="0" name="penalties_total" x-model.number="penalties" class="fld">
        <p class="text-xs text-slate-400 mt-1">Mora, combustible faltante, daños menores…</p>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1.5">Estado del vehículo</label>
      <div class="grid sm:grid-cols-3 gap-2">
        <?php
        $vehStates = [
          'cleaning'    => ['Limpieza', 'spray-can'],
          'available'   => ['Disponible', 'circle-check-big'],
          'maintenance' => ['Mantenimiento', 'wrench'],
        ];
        foreach ($vehStates as $k => [$lbl, $ic]):
        ?>
        <label class="cursor-pointer">
          <input type="radio" name="vehicle_status" value="<?= $k ?>" class="peer sr-only" <?= $k==='cleaning' ? 'checked' : '' ?>>
          <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl border hairline peer-checked:border-brand peer-checked:bg-brand/5 peer-checked:text-brand text-slate-500 transition">
            <i data-lucide="<?= $ic ?>" class="w-4 h-4"></i>
            <span class="text-sm font-semibold"><?= $lbl ?></span>
          </div>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1.5">Daños / observaciones <span class="text-slate-400">(crea incidencia)</span></label>
      <textarea name="damage_note" rows="3" class="fld" placeholder="Describe daños, manchas, faltantes… Esto generará una incidencia ligada al contrato."></textarea>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1.5">Fotos de devolución</label>
      <input type="file" name="return_photos[]" accept="image/*" multiple class="fld" @change="photoCount = $event.target.files.length">
      <p class="text-xs text-slate-400 mt-1" x-show="photoCount > 0"><span x-text="photoCount"></span> foto(s) seleccionada(s)</p>
    </div>

    <!-- Live total preview -->
    <div class="rounded-2xl border hairline bg-paper dark:bg-slate-800/40 p-4">
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Resumen</p>
      <div class="grid grid-cols-2 gap-y-1.5 text-sm">
        <span class="text-slate-500">Total contrato</span>
        <span class="text-right font-medium tnum"><?= money($c['total_amount']) ?></span>
        <span class="text-slate-500">+ Penalidades</span>
        <span class="text-right font-medium tnum text-amber-600">+ <span x-text="penalties.toFixed(2)"></span></span>
        <span class="text-slate-500">Pagado</span>
        <span class="text-right font-medium tnum text-emerald-600">- <?= number_format((float)$c['paid_amount'],2,'.',',') ?></span>
        <span class="font-bold text-navy dark:text-white pt-1 border-t hairline">Balance a cobrar</span>
        <span class="text-right font-extrabold tnum text-brand pt-1 border-t hairline"
              x-text="'RD$ ' + Math.max(0, (<?= (float)$c['total_amount'] ?> + penalties) - <?= (float)$c['paid_amount'] ?>).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})">RD$ 0.00</span>
      </div>
    </div>

    <?php if ($c['balance_due'] > 0): ?>
    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-200 text-sm flex items-center gap-2">
      <i data-lucide="alert-triangle" class="w-4 h-4"></i>
      Balance pendiente del contrato: <b><?= money($c['balance_due']) ?></b>. Podrás registrar el pago después de cerrar.
    </div>
    <?php endif; ?>

    <div class="flex gap-2 pt-2">
      <button type="submit" class="k-btn k-btn-grad"><i data-lucide="check" class="w-4 h-4"></i> Cerrar contrato</button>
      <a href="<?= url('/admin/contracts/show/'.$c['id']) ?>" class="k-btn k-btn-outline">Cancelar</a>
    </div>
  </form>
</div>

<?php \App\Core\View::push('scripts', "<script>
function closeContract(cfg){
  return {
    startMileage: cfg.startMileage,
    startFuel: cfg.startFuel,
    endMileage: cfg.startMileage,
    endFuel: 100,
    penalties: 0,
    photoCount: 0,
    endIso: cfg.endIso,
    hourlyLate: cfg.hourlyLate,
    lateHours: 0,
    lateSuggestion: 0,
    init(){
      // Late hours = max(0, now - endIso) in hours
      const ms = Date.now() - new Date(this.endIso.replace(' ','T')).getTime();
      this.lateHours = Math.max(0, ms / 3600000);
      this.lateSuggestion = Math.round(this.lateHours * this.hourlyLate * 100) / 100;
      this.\$nextTick(()=>window.lucide&&lucide.createIcons());
    },
    get kmDelta(){ return Math.max(0, (this.endMileage||0) - this.startMileage); },
    applyLateFee(){ this.penalties = this.lateSuggestion; }
  }
}
</script>"); ?>
