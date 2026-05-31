<?php
/**
 * Reusable empty-state component.
 *
 * Usage:
 *   <?= View::renderPartial('_partials/empty_state', [
 *     'icon'    => 'users',
 *     'title'   => 'Sin clientes registrados',
 *     'message' => 'Crea tu primer cliente para empezar a tomar reservas.',
 *     'cta'     => ['label' => 'Nuevo cliente', 'url' => url('/admin/customers/create'), 'icon' => 'user-plus'],
 *     'tone'    => 'brand', // 'brand' (default) | 'amber' | 'neutral'
 *   ]) ?>
 */
$icon    = $icon    ?? 'inbox';
$title   = $title   ?? 'Sin resultados';
$message = $message ?? '';
$tone    = $tone    ?? 'brand';
$cta     = $cta     ?? null;

$tones = [
  'brand'   => ['bg-brand/10 text-brand',   'border-brand/15'],
  'amber'   => ['bg-amber-50 text-amber-600','border-amber-200'],
  'emerald' => ['bg-emerald-50 text-emerald-600','border-emerald-200'],
  'neutral' => ['bg-slate-100 text-slate-400','border-slate-200'],
];
[$iconClass, $borderClass] = $tones[$tone] ?? $tones['brand'];
?>
<div class="rounded-2xl border-2 border-dashed <?= $borderClass ?> py-12 px-6 text-center">
  <div class="inline-flex w-14 h-14 rounded-2xl items-center justify-center <?= $iconClass ?>">
    <i data-lucide="<?= e($icon) ?>" class="w-7 h-7"></i>
  </div>
  <p class="font-display font-bold text-navy dark:text-white text-[16px] mt-4"><?= e($title) ?></p>
  <?php if ($message): ?>
    <p class="text-[13px] text-slate-500 mt-1.5 max-w-md mx-auto leading-relaxed"><?= e($message) ?></p>
  <?php endif; ?>
  <?php if ($cta && !empty($cta['url'])): ?>
    <a href="<?= e($cta['url']) ?>" class="k-btn k-btn-grad mt-5 !px-5">
      <?php if (!empty($cta['icon'])): ?><i data-lucide="<?= e($cta['icon']) ?>" class="w-4 h-4"></i><?php endif; ?>
      <?= e($cta['label'] ?? 'Crear') ?>
    </a>
  <?php endif; ?>
</div>
