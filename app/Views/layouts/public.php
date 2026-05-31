<?php
/**
 * Public layout — SaaS landing + tenant storefronts.
 * Expects $title, $content. Optional: $tenant (theming), $metaDescription,
 * $pageScripts, $bodyClass.
 */
use App\Core\View;
$accent  = $tenant['primary_color'] ?? '#4F46E5';
$accent2 = $tenant['secondary_color'] ?? '#06B6D4';
$flashes = $_flashes ?? [];
?>
<!DOCTYPE html>
<html lang="es" x-data="{}" x-cloak>
<head>
<?= View::renderPartial('layouts/_assets', ['title' => $title ?? 'Kyros Rent Car', 'accent' => $accent, 'accent2' => $accent2, 'metaDescription' => $metaDescription ?? null]) ?>
<link href="<?= asset('css/aos.css') ?>" rel="stylesheet">
<script src="<?= asset('js/lucide.min.js') ?>"></script>
<script defer src="<?= asset('js/alpine.min.js') ?>"></script>
</head>
<body class="bg-white text-ink <?= e($bodyClass ?? '') ?>">
<?= $content ?>

<div class="fixed bottom-5 right-5 z-50 space-y-2.5" id="toasts"></div>
<script src="<?= asset('js/aos.min.js') ?>"></script>
<script>
  AOS.init({ once:true, duration:650, easing:'ease-out-cubic', offset:60 });
  const flashes = [
    <?php foreach ($flashes as $type => $messages): foreach ((array) $messages as $m): ?>
      { type: <?= json_encode($type) ?>, message: <?= json_encode($m) ?> },
    <?php endforeach; endforeach; ?>
  ];
  const dot={success:'bg-emerald-500',error:'bg-red-500',warning:'bg-amber-500',info:'bg-slate-400'};
  flashes.forEach(f=>{
    const el=document.createElement('div');
    el.className='flex items-center gap-3 pl-4 pr-3 py-3 rounded-2xl shadow-lift bg-white border hairline min-w-[280px]';
    el.innerHTML='<span class="w-2 h-2 rounded-full '+(dot[f.type]||'bg-slate-400')+'"></span><span class="text-sm font-medium text-ink"></span>';
    el.querySelector('span:last-child').textContent=f.message;
    document.getElementById('toasts').appendChild(el);
    setTimeout(()=>el.remove(),6000);
  });
  document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons());
  document.addEventListener('alpine:initialized',()=>window.lucide&&lucide.createIcons());
</script>
<?= $pageScripts ?? '' ?>
<?= View::stack('scripts') ?>
</body>
</html>
