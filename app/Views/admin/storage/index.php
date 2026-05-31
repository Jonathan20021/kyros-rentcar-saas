<?php
use App\Services\StorageService;
$s = $snapshot;
$levelClass = $s['level'] === 'block' ? 'text-red-600 bg-red-50 border-red-200'
            : ($s['level'] === 'warn'  ? 'text-amber-700 bg-amber-50 border-amber-200'
                                       : 'text-emerald-700 bg-emerald-50 border-emerald-200');
$barColor   = $s['level'] === 'block' ? 'bg-red-500'
            : ($s['level'] === 'warn'  ? 'bg-amber-500'
                                       : 'bg-emerald-500');
$kinds = [
  'photos'     => ['Fotos de vehículos', 'image',         'bg-indigo-50 text-indigo-600'],
  'documents'  => ['Documentos / fotos contrato', 'file-text', 'bg-amber-50 text-amber-600'],
  'signatures' => ['Firmas digitales',   'pen-line',      'bg-emerald-50 text-emerald-600'],
  'branding'   => ['Marca (logo, portada)', 'palette',    'bg-brand/10 text-brand'],
  'other'      => ['Otros',              'archive',       'bg-slate-50 text-slate-500'],
];
?>
<div class="max-w-6xl mx-auto">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <h1 class="font-display text-2xl font-extrabold text-navy dark:text-white tracking-tight">Almacenamiento</h1>
      <p class="text-[13px] text-slate-500 mt-1">Cuota de tu plan + extras aprobados, y desglose por tipo.</p>
    </div>
    <div class="flex gap-2">
      <form method="POST" action="<?= url('/admin/storage/refresh') ?>"><?= csrf_field() ?>
        <button class="k-btn k-btn-outline !h-10"><i data-lucide="refresh-cw" class="w-4 h-4"></i> Recalcular</button>
      </form>
    </div>
  </div>

  <!-- Main usage panel -->
  <div class="card p-5 sm:p-7 mb-5">
    <div class="flex flex-col lg:flex-row lg:items-center gap-5">
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-2">
          <span class="eyebrow text-slate-400" style="font-size:11px;">Uso actual</span>
          <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10.5px] font-bold uppercase tracking-wider border <?= $levelClass ?>">
            <?= $s['level'] === 'block' ? 'Crítico' : ($s['level'] === 'warn' ? 'Atención' : 'OK') ?>
          </span>
        </div>
        <p class="font-display text-3xl sm:text-4xl font-extrabold text-navy dark:text-white tnum">
          <?= e($s['used_human']) ?> <span class="text-slate-300 font-normal text-2xl">/ <?= e($s['quota_human']) ?></span>
        </p>
        <p class="text-[13px] text-slate-500 mt-1">
          <?= e($s['free_human']) ?> disponibles
          <?php if ($s['level'] === 'warn'): ?>· <span class="text-amber-600 font-medium">Te estás acercando al límite</span><?php endif; ?>
          <?php if ($s['level'] === 'block'): ?>· <span class="text-red-600 font-medium">Cuota completa — las cargas están bloqueadas</span><?php endif; ?>
        </p>
        <!-- Progress -->
        <div class="mt-4 h-2.5 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
          <div class="h-full <?= $barColor ?> transition-all duration-500" style="width: <?= max(2, $s['percent']) ?>%"></div>
        </div>
        <div class="flex justify-between text-[11px] text-slate-400 mt-1.5 tnum">
          <span>0</span><span><?= $s['percent'] ?>%</span><span><?= e($s['quota_human']) ?></span>
        </div>
      </div>
      <!-- Side metric -->
      <div class="lg:w-56 lg:border-l hairline lg:pl-6">
        <p class="eyebrow text-slate-400" style="font-size:11px;">Total archivos</p>
        <p class="font-display text-2xl font-extrabold text-navy dark:text-white mt-1 tnum"><?= array_sum($breakdown['counts']) ?></p>
        <p class="text-[12px] text-slate-500 mt-0.5">
          <?= $breakdown['counts']['photos'] ?> fotos · <?= $breakdown['counts']['documents'] ?> documentos
        </p>
      </div>
    </div>
  </div>

  <!-- Breakdown grid -->
  <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-7">
    <?php foreach ($kinds as $k => [$label, $icon, $tint]):
      $bytes = $breakdown['bytes'][$k] ?? 0;
      $count = $breakdown['counts'][$k] ?? 0;
      $pct = $s['quota_bytes'] > 0 ? round(($bytes / $s['quota_bytes']) * 100, 1) : 0;
    ?>
    <div class="card p-4">
      <div class="w-9 h-9 rounded-xl grid place-items-center <?= $tint ?> mb-3"><i data-lucide="<?= e($icon) ?>" class="w-4 h-4"></i></div>
      <p class="text-[12px] text-slate-500 truncate" title="<?= e($label) ?>"><?= e($label) ?></p>
      <p class="font-display text-lg font-extrabold text-navy dark:text-white mt-1 tnum"><?= e(StorageService::format($bytes)) ?></p>
      <p class="text-[11px] text-slate-400 mt-0.5"><?= $count ?> archivos · <?= $pct ?>%</p>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Request extra -->
  <div class="grid lg:grid-cols-[1.4fr_1fr] gap-5">
    <div class="card p-5 sm:p-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand grid place-items-center"><i data-lucide="hard-drive-upload" class="w-5 h-5"></i></div>
        <div>
          <p class="font-display font-bold text-navy dark:text-white">Solicitar más almacenamiento</p>
          <p class="text-[12px] text-slate-500">Un administrador revisará y aprobará tu solicitud.</p>
        </div>
      </div>

      <?php if ($pending): ?>
        <div class="rounded-2xl border-2 border-dashed border-amber-200 bg-amber-50/60 p-5 flex items-start gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 grid place-items-center shrink-0"><i data-lucide="clock" class="w-5 h-5"></i></div>
          <div class="flex-1">
            <p class="font-display font-bold text-amber-900">Solicitud en revisión</p>
            <p class="text-[13px] text-amber-700 mt-1">
              Pediste <span class="font-bold tnum"><?= number_format((int)$pending['requested_mb']) ?> MB</span>
              el <?= e(date('d/m/Y H:i', strtotime($pending['created_at']))) ?>.
              <?php if (!empty($pending['reason'])): ?><br><span class="text-amber-700/80">"<?= e($pending['reason']) ?>"</span><?php endif; ?>
            </p>
            <form method="POST" action="<?= url('/admin/storage/cancel/' . $pending['id']) ?>" class="mt-3"
                  data-confirm="¿Cancelar esta solicitud?" data-confirm-variant="danger">
              <?= csrf_field() ?>
              <button class="text-[12px] text-red-600 font-semibold hover:underline">Cancelar solicitud</button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <form method="POST" action="<?= url('/admin/storage/request') ?>" class="space-y-3">
          <?= csrf_field() ?>
          <div>
            <label class="text-[12px] font-medium text-slate-500 block mb-1.5">¿Cuánto extra necesitas? (MB)</label>
            <div class="grid grid-cols-4 gap-2">
              <?php foreach ([500, 1000, 5000, 10000] as $preset): ?>
                <button type="button" onclick="document.getElementById('extra_mb').value=<?= $preset ?>"
                        class="px-3 py-2 rounded-xl border hairline text-[13px] font-semibold text-navy hover:border-brand hover:text-brand transition tnum">
                  +<?= $preset >= 1000 ? ($preset/1000).' GB' : $preset.' MB' ?>
                </button>
              <?php endforeach; ?>
            </div>
            <input type="number" name="extra_mb" id="extra_mb" min="100" max="100000" step="100" required
                   placeholder="Otro valor (100 – 100,000)"
                   class="fld mt-2">
          </div>
          <div>
            <label class="text-[12px] font-medium text-slate-500 block mb-1.5">Motivo (opcional)</label>
            <textarea name="reason" rows="3" maxlength="500" class="fld"
                      placeholder="Ej: subimos catálogo de 200 vehículos y se nos acaba el espacio."></textarea>
          </div>
          <button class="k-btn k-btn-grad w-full">
            <i data-lucide="send" class="w-4 h-4"></i> Enviar solicitud
          </button>
        </form>
      <?php endif; ?>
    </div>

    <!-- History -->
    <div class="card p-5 sm:p-6">
      <p class="font-display font-bold text-navy dark:text-white mb-4">Historial de solicitudes</p>
      <?php if (empty($history)): ?>
        <div class="text-center py-8">
          <div class="inline-flex w-10 h-10 rounded-xl bg-slate-100 text-slate-400 items-center justify-center"><i data-lucide="inbox" class="w-5 h-5"></i></div>
          <p class="text-sm text-slate-400 mt-2">Sin solicitudes aún</p>
        </div>
      <?php else: ?>
        <div class="space-y-2.5">
          <?php
          $statusBadge = [
            'pending'   => ['Pendiente',  'bg-amber-50 text-amber-700'],
            'approved'  => ['Aprobada',   'bg-emerald-50 text-emerald-700'],
            'rejected'  => ['Rechazada',  'bg-red-50 text-red-700'],
            'cancelled' => ['Cancelada',  'bg-slate-100 text-slate-500'],
          ];
          foreach ($history as $h):
            [$lbl, $cls] = $statusBadge[$h['status']] ?? ['—', 'bg-slate-100'];
          ?>
          <div class="p-3.5 rounded-xl border hairline hover:bg-paper/60 transition">
            <div class="flex items-center justify-between gap-2 mb-1">
              <span class="font-display font-bold text-navy text-[13.5px] tnum">+<?= number_format((int)$h['requested_mb']) ?> MB</span>
              <span class="text-[10.5px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full <?= $cls ?>"><?= e($lbl) ?></span>
            </div>
            <p class="text-[11px] text-slate-400 tnum"><?= e(date('d/m/Y H:i', strtotime($h['created_at']))) ?></p>
            <?php if (!empty($h['review_note'])): ?>
              <p class="text-[12px] text-slate-600 mt-1.5 leading-relaxed italic">"<?= e($h['review_note']) ?>"</p>
            <?php endif; ?>
            <?php if (!empty($h['granted_mb']) && $h['status'] === 'approved'): ?>
              <p class="text-[11px] text-emerald-600 font-semibold mt-1">+<?= number_format((int)$h['granted_mb']) ?> MB añadidos a tu cuota</p>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
