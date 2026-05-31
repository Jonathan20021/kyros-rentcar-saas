<?php
use App\Models\NcfSequence;
$isRD = $country === 'DO';
$statusBadge = [
  'active'    => ['Activa',     'bg-emerald-50 text-emerald-700 border-emerald-200'],
  'exhausted' => ['Agotada',    'bg-red-50 text-red-700 border-red-200'],
  'expired'   => ['Vencida',    'bg-amber-50 text-amber-700 border-amber-200'],
  'disabled'  => ['Deshabilitada','bg-slate-100 text-slate-500 border-slate-200'],
];
?>
<div class="max-w-6xl mx-auto">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <h1 class="font-display text-2xl font-extrabold text-navy dark:text-white tracking-tight">
        <?= $isRD ? 'Comprobantes fiscales · DGII' : 'Facturación electrónica · DIAN' ?>
      </h1>
      <p class="text-[13px] text-slate-500 mt-1">
        <?= $isRD
            ? 'Secuencias de NCF activas y consumo en tiempo real. Solo un rango activo por tipo.'
            : 'Tu país está configurado como Colombia — los NCF son específicos de RD. Configura tu resolución DIAN en facturación.' ?>
      </p>
    </div>
    <a href="<?= url('/admin/settings') ?>" class="k-btn k-btn-outline !h-10"><i data-lucide="settings" class="w-4 h-4"></i> País / impuestos</a>
  </div>

  <?php if (!$isRD): ?>
    <div class="card p-7 text-center">
      <div class="inline-flex w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 items-center justify-center"><i data-lucide="info" class="w-7 h-7"></i></div>
      <p class="font-display font-bold text-navy text-lg mt-4">Esta sección aplica solo a República Dominicana</p>
      <p class="text-[13px] text-slate-500 mt-2 max-w-md mx-auto">
        Para Colombia el control fiscal se hace con una <b>resolución DIAN</b> y la facturación electrónica.
        Cambia el país en <a href="<?= url('/admin/settings') ?>" class="text-brand font-medium hover:underline">Configuración → País</a> si tu rent car opera en RD.
      </p>
    </div>
  <?php else: ?>

  <!-- KPI row: one card per registered type -->
  <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-5">
    <?php
    $byType = [];
    foreach ($sequences as $s) {
      if ($s['status'] === 'active') $byType[$s['ncf_type']] = $s;
    }
    foreach ($types as $code => $label):
      $s = $byType[$code] ?? null;
      $pct = $s ? NcfSequence::percentUsed($s) : 0;
      $left = $s ? NcfSequence::remaining($s) : null;
      $color = !$s ? 'text-slate-300' : ($pct >= 90 ? 'text-red-500' : ($pct >= 70 ? 'text-amber-500' : 'text-emerald-500'));
    ?>
    <div class="card p-4">
      <div class="flex items-center justify-between mb-2">
        <p class="font-mono font-bold text-[15px] tnum text-navy"><?= $code ?></p>
        <i data-lucide="<?= $s ? 'check-circle-2' : 'circle-dashed' ?>" class="w-4 h-4 <?= $color ?>"></i>
      </div>
      <p class="text-[11.5px] text-slate-500 truncate" title="<?= e($label) ?>"><?= e($label) ?></p>
      <?php if ($s): ?>
        <p class="font-display text-lg font-extrabold text-navy mt-2 tnum"><?= number_format($left) ?></p>
        <p class="text-[10.5px] text-slate-400">disponibles · <?= round($pct, 1) ?>%</p>
        <div class="mt-2 h-1 rounded-full bg-slate-100 overflow-hidden">
          <div class="h-full <?= $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') ?>" style="width: <?= max(2,$pct) ?>%"></div>
        </div>
      <?php else: ?>
        <p class="text-[11px] text-slate-400 mt-2 italic">Sin secuencia</p>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="grid lg:grid-cols-[1.4fr_1fr] gap-5">
    <!-- Sequences table -->
    <div class="card p-5 sm:p-6">
      <div class="flex items-center justify-between mb-4">
        <p class="font-display font-bold text-navy">Secuencias registradas</p>
        <span class="text-[11px] text-slate-400 tnum"><?= count($sequences) ?> registros</span>
      </div>
      <?php if (empty($sequences)): ?>
        <div class="rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center">
          <div class="inline-flex w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 items-center justify-center"><i data-lucide="receipt" class="w-6 h-6"></i></div>
          <p class="text-slate-500 font-medium mt-3">Aún no has registrado secuencias NCF</p>
          <p class="text-[12px] text-slate-400 mt-1">Empieza con B02 para consumidor final.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-[13px]">
            <thead class="text-[11px] uppercase tracking-wider text-slate-400 border-b hairline">
              <tr>
                <th class="text-left py-2.5 pr-3">Tipo</th>
                <th class="text-left py-2.5 px-3">Rango</th>
                <th class="text-left py-2.5 px-3">Consumido</th>
                <th class="text-left py-2.5 px-3">Vence</th>
                <th class="text-left py-2.5 px-3">Estado</th>
                <th class="py-2.5"></th>
              </tr>
            </thead>
            <tbody class="divide-y hairline">
              <?php foreach ($sequences as $s):
                $pct = NcfSequence::percentUsed($s);
                [$lbl, $cls] = $statusBadge[$s['status']] ?? ['—', 'bg-slate-100'];
              ?>
              <tr class="hover:bg-paper/50">
                <td class="py-3 pr-3">
                  <p class="font-mono font-bold text-navy tnum"><?= e($s['ncf_type']) ?></p>
                  <p class="text-[11px] text-slate-400"><?= e($types[$s['ncf_type']] ?? '') ?></p>
                </td>
                <td class="py-3 px-3 tnum text-slate-600"><?= number_format(1) ?> – <?= number_format((int)$s['max_seq']) ?></td>
                <td class="py-3 px-3 tnum">
                  <p class="font-semibold text-navy"><?= number_format((int)$s['current_seq']) ?></p>
                  <div class="mt-1 h-1 w-24 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full <?= $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') ?>" style="width: <?= max(2,$pct) ?>%"></div>
                  </div>
                </td>
                <td class="py-3 px-3 text-slate-600 tnum"><?= $s['valid_until'] ? e(date('d/m/Y', strtotime($s['valid_until']))) : '—' ?></td>
                <td class="py-3 px-3">
                  <span class="text-[10.5px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border <?= $cls ?>"><?= e($lbl) ?></span>
                </td>
                <td class="py-3 text-right">
                  <?php if ($s['status'] === 'active'): ?>
                  <form method="POST" action="<?= url('/admin/ncf/disable/' . $s['id']) ?>"
                        data-confirm="¿Deshabilitar esta secuencia? Las facturas existentes no se afectan."
                        data-confirm-variant="danger">
                    <?= csrf_field() ?>
                    <button class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Deshabilitar">
                      <i data-lucide="ban" class="w-4 h-4"></i>
                    </button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- New sequence -->
    <div class="card p-5 sm:p-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand grid place-items-center"><i data-lucide="plus" class="w-5 h-5"></i></div>
        <div>
          <p class="font-display font-bold text-navy">Registrar nueva secuencia</p>
          <p class="text-[12px] text-slate-500">Cárgala desde tu autorización DGII.</p>
        </div>
      </div>
      <form method="POST" action="<?= url('/admin/ncf') ?>" class="space-y-3">
        <?= csrf_field() ?>
        <div>
          <label class="text-[12px] font-medium text-slate-500 block mb-1.5">Tipo</label>
          <select name="ncf_type" class="fld" required>
            <?php foreach ($types as $code => $label): ?>
              <option value="<?= $code ?>"><?= $code ?> · <?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-[12px] font-medium text-slate-500 block mb-1.5">Inicio</label>
            <input type="number" name="start" min="1" value="1" class="fld tnum" required>
          </div>
          <div>
            <label class="text-[12px] font-medium text-slate-500 block mb-1.5">Fin</label>
            <input type="number" name="max" min="1" placeholder="ej: 1000" class="fld tnum" required>
          </div>
        </div>
        <div>
          <label class="text-[12px] font-medium text-slate-500 block mb-1.5">Vence (opcional)</label>
          <input type="date" name="valid_until" class="fld">
        </div>
        <div>
          <label class="text-[12px] font-medium text-slate-500 block mb-1.5">Notas (opcional)</label>
          <input type="text" name="notes" maxlength="200" placeholder="Ej: Resolución 2025-001" class="fld">
        </div>
        <button class="k-btn k-btn-grad w-full !h-10"><i data-lucide="plus" class="w-4 h-4"></i> Registrar secuencia</button>
      </form>
    </div>
  </div>
  <?php endif; ?>
</div>
