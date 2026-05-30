<?php
$d = $driver;
$fullName = trim($d['first_name'].' '.($d['last_name'] ?? ''));
$today = new DateTime('today');
$licWarn = null;
if (!empty($d['license_expiration'])) {
  $exp = new DateTime($d['license_expiration']);
  $diff = (int) $today->diff($exp)->format('%r%a');
  if ($diff < 0)        $licWarn = ['Vencida', 'bg-red-50 text-red-600', 'border-red-200'];
  elseif ($diff <= 30)  $licWarn = ["Vence en {$diff}d", 'bg-amber-50 text-amber-600', 'border-amber-200'];
}
?>
<div class="grid lg:grid-cols-[320px_1fr] gap-5">
  <!-- LEFT — driver card -->
  <div class="space-y-5">
    <div class="card p-6 reveal">
      <div class="relative">
        <div class="h-24 -mx-6 -mt-6 mb-4 bg-gradient-to-br from-brand/20 to-brand2/15 dark:from-brand/30 dark:to-navy rounded-t-2xl"></div>
        <div class="w-24 h-24 -mt-16 rounded-2xl bg-white dark:bg-slate-900 border-4 border-white dark:border-slate-900 shadow-card overflow-hidden grid place-items-center">
          <?php if (!empty($d['photo'])): ?>
            <img src="<?= e(media($d['photo'])) ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <span class="text-2xl font-bold text-slate-400"><?= e(initials($fullName ?: 'D')) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <h1 class="font-display font-extrabold text-navy dark:text-white text-xl mt-4 leading-tight"><?= e($fullName ?: '—') ?></h1>
      <p class="text-sm text-slate-400 mt-0.5"><?= e($d['license_number'] ?: 'Sin licencia registrada') ?></p>
      <?php if ($licWarn): ?>
      <div class="mt-3 px-3 py-2 rounded-xl border <?= $licWarn[2] ?> <?= $licWarn[1] ?> text-sm font-semibold flex items-center gap-2">
        <i data-lucide="alert-triangle" class="w-4 h-4"></i> Licencia: <?= $licWarn[0] ?>
      </div>
      <?php endif; ?>
      <dl class="mt-4 space-y-2.5 text-sm">
        <?php if (!empty($d['phone'])): ?>
        <div class="flex items-center justify-between"><dt class="text-slate-400 flex items-center gap-1.5"><i data-lucide="phone" class="w-3.5 h-3.5"></i>Teléfono</dt><dd class="font-medium text-navy dark:text-white tnum"><?= e($d['phone']) ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($d['email'])): ?>
        <div class="flex items-center justify-between"><dt class="text-slate-400 flex items-center gap-1.5"><i data-lucide="mail" class="w-3.5 h-3.5"></i>Email</dt><dd class="font-medium text-navy dark:text-white truncate ml-2"><?= e($d['email']) ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($d['document_number'])): ?>
        <div class="flex items-center justify-between"><dt class="text-slate-400 flex items-center gap-1.5"><i data-lucide="badge" class="w-3.5 h-3.5"></i>Documento</dt><dd class="font-medium text-navy dark:text-white tnum"><?= e($d['document_number']) ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($d['license_expiration'])): ?>
        <div class="flex items-center justify-between"><dt class="text-slate-400 flex items-center gap-1.5"><i data-lucide="calendar-clock" class="w-3.5 h-3.5"></i>Vence</dt><dd class="font-medium text-navy dark:text-white tnum"><?= format_date($d['license_expiration']) ?></dd></div>
        <?php endif; ?>
      </dl>
      <div class="mt-4 pt-4 border-t hairline grid grid-cols-2 gap-3">
        <div><p class="text-[11px] text-slate-400">Por día</p><p class="font-bold text-navy dark:text-white tnum"><?= money($d['daily_rate']) ?></p></div>
        <div><p class="text-[11px] text-slate-400">Por hora</p><p class="font-bold text-navy dark:text-white tnum"><?= money($d['hourly_rate']) ?></p></div>
      </div>
      <?php if (can('drivers.edit')): ?>
      <a href="<?= url('/admin/drivers/edit/'.$d['id']) ?>" class="k-btn k-btn-outline w-full mt-4"><i data-lucide="pencil" class="w-4 h-4"></i> Editar</a>
      <?php endif; ?>
    </div>

    <div class="card p-5 reveal">
      <h3 class="font-display font-bold text-navy dark:text-white mb-3">Notas</h3>
      <p class="text-sm text-slate-500 dark:text-slate-400 whitespace-pre-line"><?= e($d['notes'] ?: 'Sin notas.') ?></p>
    </div>
  </div>

  <!-- RIGHT — stats + recent trips -->
  <div class="space-y-5">
    <div class="grid sm:grid-cols-2 gap-4">
      <div class="card p-5 reveal">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-brand/10 text-brand grid place-items-center"><i data-lucide="route" class="w-5 h-5"></i></div>
          <div>
            <p class="text-[12px] text-slate-400">Viajes totales</p>
            <p class="text-2xl font-extrabold text-navy dark:text-white tnum" data-count="<?= (int)$stats['trips'] ?>">0</p>
          </div>
        </div>
      </div>
      <div class="card p-5 reveal">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center"><i data-lucide="banknote" class="w-5 h-5"></i></div>
          <div>
            <p class="text-[12px] text-slate-400">Generado en servicios</p>
            <p class="text-2xl font-extrabold text-navy dark:text-white tnum"><?= money($stats['earned']) ?></p>
          </div>
        </div>
      </div>
    </div>

    <div class="card reveal">
      <div class="px-6 py-4 border-b hairline flex items-center justify-between">
        <h2 class="font-display font-bold text-navy dark:text-white">Viajes recientes</h2>
        <span class="text-xs text-slate-400"><?= count($recentTrips) ?> contratos</span>
      </div>
      <?php if (empty($recentTrips)): ?>
        <p class="px-6 py-12 text-center text-slate-400 text-sm">Este chofer aún no tiene contratos asignados.</p>
      <?php else: ?>
      <div class="divide-y hairline">
        <?php foreach ($recentTrips as $t): ?>
        <div class="px-6 py-3.5 flex items-center gap-3 hover:bg-paper dark:hover:bg-slate-800/40">
          <div class="w-10 h-10 rounded-xl bg-paper grid place-items-center"><i data-lucide="car" class="w-5 h-5 text-slate-400"></i></div>
          <div class="min-w-0 flex-1">
            <p class="font-semibold text-navy dark:text-white truncate"><?= e($t['contract_number']) ?> · <?= e($t['brand'].' '.$t['model']) ?></p>
            <p class="text-xs text-slate-400 tnum"><?= format_date($t['start_datetime']) ?> → <?= format_date($t['end_datetime']) ?></p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?= status_badge($t['status']) ?>"><?= status_label($t['status']) ?></span>
          <span class="ml-3 font-bold text-navy dark:text-white tnum shrink-0"><?= money($t['total_amount']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
