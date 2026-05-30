<?php
/** Dark cinematic auth layout. Expects $title, $content. */
use App\Core\View;
$flashes = $_flashes ?? [];
?>
<!DOCTYPE html>
<html lang="es" class="dark" x-data="{}" x-cloak>
<head>
<?= View::renderPartial('layouts/_assets', ['title' => $title ?? 'Acceder · Kyros Rent Car']) ?>
<script src="https://unpkg.com/lucide@latest"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-[#0E1422] text-white antialiased">
<div class="min-h-screen grid lg:grid-cols-2">

  <!-- Brand panel -->
  <div class="hidden lg:flex flex-col justify-between p-12 mesh-dark relative overflow-hidden">
    <div class="grid-dark absolute inset-0"></div>
    <div class="orb w-96 h-96 -bottom-20 -left-20 grad-bg"></div>
    <a href="<?= url('/') ?>" class="relative flex items-center gap-2.5">
      <div class="w-10 h-10 rounded-xl grad-bg grid place-items-center font-black ">K</div>
      <span class="font-display font-extrabold text-xl">Kyros Rent Car</span>
    </a>
    <div class="relative">
      <h1 class="font-display text-4xl xl:text-5xl font-extrabold leading-[1.05] tracking-tight">Conduce el futuro<br>de tu <span class="text-grad">rent car</span>.</h1>
      <p class="mt-5 text-white/60 max-w-md text-lg">Flotilla, reservas, contratos, pagos y tu pagina publica — en una sola plataforma de clase mundial.</p>
      <div class="mt-9 space-y-3.5">
        <?php foreach (['Reservas online sin doble booking','Contratos y pagos con balances','Pagina publica con tu marca'] as $b): ?>
        <div class="flex items-center gap-3 text-white/80"><span class="w-6 h-6 rounded-full grad-bg grid place-items-center shrink-0"><i data-lucide="check" class="w-3.5 h-3.5"></i></span><?= e($b) ?></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="relative glass rounded-2xl p-5">
      <div class="flex gap-1 text-amber-400 mb-2"><?php for($i=0;$i<5;$i++):?><i data-lucide="star" class="w-4 h-4 fill-amber-400"></i><?php endfor;?></div>
      <p class="text-sm text-white/70">"Montamos nuestra rent car online en una tarde. La pagina publica nos trae reservas todos los dias."</p>
      <p class="text-xs text-white/40 mt-2">— Carlos, Speed Rent Car</p>
    </div>
  </div>

  <!-- Form -->
  <div class="flex items-center justify-center p-6 sm:p-12 relative">
    <div class="orb w-72 h-72 top-0 right-0 grad-bg opacity-30 lg:hidden"></div>
    <div class="relative w-full max-w-md">
      <a href="<?= url('/') ?>" class="lg:hidden flex items-center gap-2.5 mb-8">
        <div class="w-10 h-10 rounded-xl grad-bg grid place-items-center font-black">K</div>
        <span class="font-display font-extrabold text-xl">Kyros Rent Car</span>
      </a>
      <?= $content ?>
    </div>
  </div>
</div>

<div class="fixed bottom-5 right-5 z-50 space-y-2.5" id="toasts"></div>
<script>
  const flashes=[<?php foreach ($flashes as $type=>$messages): foreach ((array)$messages as $m): ?>{type:<?= json_encode($type) ?>,message:<?= json_encode($m) ?>},<?php endforeach; endforeach; ?>];
  const dot={success:'bg-emerald-400',error:'bg-red-400',warning:'bg-amber-400',info:'bg-white/60'};
  flashes.forEach(f=>{const el=document.createElement('div');el.className='glass flex items-center gap-3 pl-4 pr-3 py-3 rounded-2xl min-w-[280px]';el.innerHTML='<span class="w-2 h-2 rounded-full '+(dot[f.type]||'bg-white/60')+'"></span><span class="text-sm font-medium"></span>';el.querySelector('span:last-child').textContent=f.message;document.getElementById('toasts').appendChild(el);setTimeout(()=>el.remove(),6000);});
  document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons());
  document.addEventListener('alpine:initialized',()=>window.lucide&&lucide.createIcons());
</script>
</body>
</html>
