<?php
/** Minimal light layout for public-facing documents (contract share, receipts). */
use App\Core\View;
$flashes = $_flashes ?? [];
$brand   = $tenant['primary_color'] ?? '#F23645';
?>
<!DOCTYPE html>
<html lang="es" x-data="{}" x-cloak>
<head>
<?= View::renderPartial('layouts/_assets', ['title' => $title ?? 'Kyros Rent Car', 'accent' => $brand]) ?>
<script src="https://unpkg.com/lucide@latest"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
  body{ background:#F4F6FB; color:#1C2433; }
  .doc-shell{ background:#fff; border:1px solid #E6EAF1; border-radius:1.4rem;
              box-shadow:0 1px 3px rgba(28,36,51,.04), 0 20px 60px -30px rgba(28,36,51,.18); }
  .doc-header{ background:linear-gradient(135deg, var(--brand) 0%, color-mix(in srgb,var(--brand) 60%, #1C2433) 100%); }
  @media print{
    body{ background:#fff; }
    .no-print{ display:none !important; }
    .doc-shell{ box-shadow:none; border:0; border-radius:0; }
  }
</style>
</head>
<body class="antialiased">

<header class="no-print sticky top-0 z-30 backdrop-blur-md bg-white/85 border-b hairline">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center gap-3">
    <div class="flex items-center gap-2.5">
      <?php if (!empty($tenant['logo'])): ?>
        <img src="<?= e(media($tenant['logo'])) ?>" class="w-9 h-9 rounded-lg object-cover" alt="<?= e($tenant['name']) ?>">
      <?php else: ?>
        <div class="w-9 h-9 rounded-lg grid place-items-center text-white font-black text-sm" style="background:var(--brand)">
          <?= e(mb_substr($tenant['name'] ?? 'K', 0, 1)) ?>
        </div>
      <?php endif; ?>
      <div class="leading-tight">
        <p class="font-display font-extrabold text-[15px] text-ink"><?= e($tenant['name'] ?? 'Rent car') ?></p>
        <?php if (!empty($tenant['slug'])): ?>
          <p class="text-[11px] text-slate-400">/r/<?= e($tenant['slug']) ?></p>
        <?php endif; ?>
      </div>
    </div>
    <div class="ml-auto flex items-center gap-2">
      <?php if (!empty($token)): ?>
        <a href="<?= url('/contrato/' . $token . '/pdf') ?>" target="_blank" class="k-btn k-btn-dark !h-9">
          <i data-lucide="file-down" class="w-4 h-4"></i> <span class="hidden sm:inline">Descargar</span> PDF
        </a>
      <?php endif; ?>
      <?php if (!empty($tenant['slug'])): ?>
        <a href="<?= url('/r/' . $tenant['slug']) ?>" class="k-btn k-btn-outline !h-9">
          <i data-lucide="external-link" class="w-4 h-4"></i><span class="hidden sm:inline">Ver rent car</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
  <?= $content ?>
</main>

<footer class="no-print max-w-5xl mx-auto px-4 sm:px-6 pb-12">
  <div class="border-t hairline pt-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400">
    <p>Contrato emitido por <b class="text-slate-600"><?= e($tenant['name'] ?? '') ?></b> a través de Kyros Rent Car.</p>
    <p>&copy; <?= date('Y') ?> Kyros Rent Car.</p>
  </div>
</footer>

<div class="fixed bottom-5 right-5 z-50 space-y-2.5 no-print" x-data="{toasts: window.__flashes || []}">
  <template x-for="(t,i) in toasts" :key="i">
    <div x-show="t.show" x-init="setTimeout(()=>t.show=false,5000)" x-transition.opacity.duration.300ms
         class="flex items-center gap-3 pl-4 pr-3 py-3 rounded-2xl shadow-lift bg-white border hairline min-w-[280px]">
      <span class="w-2 h-2 rounded-full" :class="{'bg-emerald-500':t.type==='success','bg-red-500':t.type==='error','bg-amber-500':t.type==='warning','bg-slate-400':t.type==='info'}"></span>
      <span x-text="t.message" class="text-sm font-medium text-ink"></span>
      <button @click="t.show=false" class="ml-auto text-slate-400 hover:text-ink">&times;</button>
    </div>
  </template>
</div>

<script>
  window.__flashes = [
    <?php foreach ($flashes as $type => $messages): foreach ((array) $messages as $m): ?>
      { type: <?= json_encode($type) ?>, message: <?= json_encode($m) ?>, show:true },
    <?php endforeach; endforeach; ?>
  ];
  document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons());
  document.addEventListener('alpine:initialized',()=>window.lucide&&lucide.createIcons());
</script>
<?= View::stack('scripts') ?>
</body>
</html>
