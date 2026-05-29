<?php
use App\Models\PromoCode;
$canManage = can('promos.manage');
function _fmt_pdv(array $p): string {
  if ($p['discount_type'] === 'percent') return rtrim(rtrim(number_format((float)$p['discount_value'], 2),'0'),'.') . '%';
  return money($p['discount_value']);
}
$active = $expired = $depleted = 0;
$today = date('Y-m-d');
foreach ($promos as $p) {
  $isActive = $p['status'] === 'active';
  $expiredNow = $isActive && !empty($p['valid_to']) && $today > $p['valid_to'];
  $maxedOut   = $isActive && $p['max_uses'] !== null && (int)$p['used_count'] >= (int)$p['max_uses'];
  if ($expiredNow) $expired++;
  elseif ($maxedOut) $depleted++;
  elseif ($isActive) $active++;
}
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Códigos promocionales</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Descuentos para tus clientes — visibles en la página pública o reservados para campañas internas.</p>
  </div>
  <?php if ($canManage): ?>
  <a href="<?= url('/admin/promos/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nuevo código</a>
  <?php endif; ?>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <?php
  $kpis = [
    ['Total', count($promos), 'ticket-percent', 'bg-navy/5 text-navy'],
    ['Activos', $active, 'badge-check', 'bg-emerald-50 text-emerald-600'],
    ['Vencidos', $expired, 'calendar-x', 'bg-amber-50 text-amber-600'],
    ['Agotados', $depleted, 'package-x', 'bg-red-50 text-red-600'],
  ];
  foreach ($kpis as $k): ?>
  <div class="card p-4 reveal">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl grid place-items-center <?= $k[3] ?>"><i data-lucide="<?= $k[2] ?>" class="w-[18px] h-[18px]"></i></div>
      <p class="text-[13px] text-slate-400 font-medium"><?= $k[0] ?></p>
    </div>
    <p class="mt-2 text-[24px] leading-none font-extrabold text-navy dark:text-white tnum" data-count="<?= (int)$k[1] ?>">0</p>
  </div>
  <?php endforeach; ?>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
  <div class="flex-1 min-w-[180px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
    <input name="search" value="<?= e($filters['search']) ?>" placeholder="Código o descripción" class="fld !h-10">
  </div>
  <div class="min-w-[150px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !h-10">
      <option value="">Todos</option>
      <option value="active"   <?= $filters['status']==='active'?'selected':'' ?>>Activos</option>
      <option value="inactive" <?= $filters['status']==='inactive'?'selected':'' ?>>Inactivos</option>
    </select>
  </div>
  <button class="k-btn k-btn-dark !h-10">Filtrar</button>
  <a href="<?= url('/admin/promos') ?>" class="k-btn k-btn-outline !h-10">Limpiar</a>
</form>

<?php if (empty($promos)): ?>
  <div class="card p-16 text-center">
    <div class="w-14 h-14 rounded-2xl bg-paper grid place-items-center mx-auto"><i data-lucide="ticket-percent" class="w-7 h-7 text-slate-300"></i></div>
    <h3 class="font-semibold text-navy mt-4">Aún no tienes códigos</h3>
    <p class="text-sm text-slate-400 mt-1">Crea tu primer código de descuento para impulsar las reservas.</p>
    <?php if ($canManage): ?><a href="<?= url('/admin/promos/create') ?>" class="k-btn k-btn-grad mt-4"><i data-lucide="plus" class="w-4 h-4"></i> Nuevo código</a><?php endif; ?>
  </div>
<?php else: ?>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <?php foreach ($promos as $p):
    $isActive   = $p['status'] === 'active';
    $expiredNow = $isActive && !empty($p['valid_to']) && $today > $p['valid_to'];
    $maxedOut   = $isActive && $p['max_uses'] !== null && (int)$p['used_count'] >= (int)$p['max_uses'];
    $usagePct = ($p['max_uses'] !== null && (int)$p['max_uses'] > 0)
      ? min(100, (int) round((int)$p['used_count'] / (int)$p['max_uses'] * 100))
      : null;
    if (!$isActive)   { $badge = ['Inactivo','bg-slate-100 text-slate-500']; }
    elseif ($expiredNow){ $badge = ['Vencido','bg-amber-50 text-amber-600']; }
    elseif ($maxedOut){ $badge = ['Agotado','bg-red-50 text-red-600']; }
    else              { $badge = ['Activo','bg-emerald-50 text-emerald-600']; }
  ?>
  <div class="card p-5 reveal-s relative overflow-hidden">
    <div class="absolute -right-8 -top-8 w-28 h-28 rounded-full bg-brand/5"></div>
    <div class="flex items-start justify-between gap-2 relative">
      <div class="min-w-0">
        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Código</p>
        <p class="font-display font-extrabold text-navy dark:text-white text-xl tracking-tight truncate"><?= e($p['code']) ?></p>
      </div>
      <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold <?= $badge[1] ?>"><?= $badge[0] ?></span>
    </div>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 min-h-[2.5em]"><?= e($p['description'] ?: '—') ?></p>
    <div class="mt-4 flex items-end justify-between gap-2 relative">
      <div>
        <p class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">Descuento</p>
        <p class="font-display font-extrabold text-brand text-2xl leading-none tnum"><?= _fmt_pdv($p) ?></p>
      </div>
      <div class="text-right">
        <p class="text-[11px] text-slate-400">Usos</p>
        <p class="text-sm font-bold text-navy dark:text-white tnum">
          <?= (int)$p['used_count'] ?><?php if ($p['max_uses'] !== null): ?> / <?= (int)$p['max_uses'] ?><?php endif; ?>
        </p>
      </div>
    </div>
    <?php if ($usagePct !== null): ?>
    <div class="progress mt-2"><i style="width:<?= $usagePct ?>%; background: var(--brand);"></i></div>
    <?php endif; ?>
    <div class="mt-3 pt-3 border-t hairline text-[12px] text-slate-500 flex items-center gap-3 flex-wrap">
      <?php if ($p['valid_from'] || $p['valid_to']): ?>
        <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3.5 h-3.5"></i>
          <?= e($p['valid_from'] ? format_date($p['valid_from']) : '—') ?> → <?= e($p['valid_to'] ? format_date($p['valid_to']) : '∞') ?>
        </span>
      <?php endif; ?>
      <?php if ((float)$p['min_amount'] > 0): ?>
        <span class="flex items-center gap-1"><i data-lucide="banknote" class="w-3.5 h-3.5"></i>Min <?= money($p['min_amount']) ?></span>
      <?php endif; ?>
      <?php if ((int)$p['is_public'] === 1): ?>
        <span class="flex items-center gap-1 text-brand"><i data-lucide="globe" class="w-3.5 h-3.5"></i>Público</span>
      <?php else: ?>
        <span class="flex items-center gap-1 text-slate-400"><i data-lucide="lock" class="w-3.5 h-3.5"></i>Privado</span>
      <?php endif; ?>
    </div>
    <?php if ($canManage): ?>
    <div class="mt-4 flex items-center gap-1.5">
      <a href="<?= url('/admin/promos/edit/'.$p['id']) ?>" class="k-btn k-btn-outline !h-9 !px-3 flex-1"><i data-lucide="pencil" class="w-4 h-4"></i> Editar</a>
      <button type="button" data-copy="<?= e($p['code']) ?>" onclick="navigator.clipboard.writeText(this.dataset.copy); this.classList.add('!text-emerald-600'); setTimeout(()=>this.classList.remove('!text-emerald-600'), 1200);"
              class="icon-btn !w-9 !h-9" title="Copiar código"><i data-lucide="copy" class="w-4 h-4"></i></button>
      <form method="POST" action="<?= url('/admin/promos/delete/'.$p['id']) ?>" data-confirm="El código <?= e($p['code']) ?> dejará de funcionar inmediatamente." data-confirm-title="¿Eliminar código promocional?">
        <?= csrf_field() ?>
        <button class="icon-btn !w-9 !h-9 hover:!text-red-600" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
      </form>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
