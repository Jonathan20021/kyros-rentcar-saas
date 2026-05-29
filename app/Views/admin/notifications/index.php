<?php
$icons = ['reservation'=>'calendar-check','document'=>'file-warning','contract'=>'file-text','payment'=>'credit-card','info'=>'bell'];
$tints = ['reservation'=>'bg-indigo-50 text-indigo-600','document'=>'bg-amber-50 text-amber-600','contract'=>'bg-navy/5 text-navy','payment'=>'bg-emerald-50 text-emerald-600','info'=>'bg-slate-100 text-slate-500'];
?>
<div class="max-w-3xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Notificaciones</h1>
      <p class="text-sm text-slate-500"><?= $unread ?> sin leer</p>
    </div>
    <?php if ($unread > 0): ?>
    <form method="POST" action="<?= url('/admin/notifications/read-all') ?>"><?= csrf_field() ?><button class="k-btn k-btn-outline"><i data-lucide="check-check" class="w-4 h-4"></i> Marcar todas</button></form>
    <?php endif; ?>
  </div>

  <div class="flex gap-1 p-1 rounded-xl bg-paper border hairline w-fit mb-5">
    <a href="<?= url('/admin/notifications') ?>" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= !$onlyUnread?'bg-white shadow-xs text-navy':'text-slate-500' ?>">Todas</a>
    <a href="<?= url('/admin/notifications?filter=unread') ?>" class="px-4 py-2 rounded-lg text-sm font-semibold transition <?= $onlyUnread?'bg-white shadow-xs text-navy':'text-slate-500' ?>">Sin leer</a>
  </div>

  <div class="card overflow-hidden divide-y hairline">
    <?php foreach ($items as $n): $ic=$icons[$n['type']]??'bell'; $tt=$tints[$n['type']]??$tints['info']; ?>
    <form method="POST" action="<?= url('/admin/notifications/read/'.$n['id']) ?>"><?= csrf_field() ?>
    <button type="submit" class="w-full text-left px-5 py-4 flex items-start gap-3.5 hover:bg-paper transition <?= $n['is_read']?'':'bg-brand/[0.03]' ?>">
      <div class="w-10 h-10 rounded-xl grid place-items-center shrink-0 <?= $tt ?>"><i data-lucide="<?= $ic ?>" class="w-5 h-5"></i></div>
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
          <p class="font-medium text-navy dark:text-white"><?= e($n['title']) ?></p>
          <?php if (!$n['is_read']): ?><span class="w-1.5 h-1.5 rounded-full bg-brand"></span><?php endif; ?>
        </div>
        <p class="text-sm text-slate-500 mt-0.5"><?= e($n['message']) ?></p>
      </div>
      <span class="text-xs text-slate-400 shrink-0 whitespace-nowrap"><?= format_date($n['created_at']) ?></span>
    </button>
    </form>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
    <div class="px-6 py-16 text-center text-slate-400"><i data-lucide="bell-off" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>No hay notificaciones</p></div>
    <?php endif; ?>
  </div>
</div>
