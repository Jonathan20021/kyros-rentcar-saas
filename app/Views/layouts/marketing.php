<?php
/** Dark cinematic marketing layout (landing, plans). Expects $title, $content. */
use App\Core\View;
$flashes = $_flashes ?? [];
?>
<!DOCTYPE html>
<html lang="es" class="dark" x-data="{scrolled:false,mobile:false}" @scroll.window="scrolled = window.scrollY > 20" x-cloak>
<head>
<?= View::renderPartial('layouts/_assets', ['title' => $title ?? 'Kyros Rent Car', 'metaDescription' => $metaDescription ?? null]) ?>
<script src="https://unpkg.com/lucide@latest"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#0E1422] text-white antialiased selection:bg-brand/40">

<!-- Scroll progress -->
<div class="fixed top-0 left-0 right-0 h-[3px] z-[60]"><div id="kprogress" class="h-full origin-left grad-bg" style="transform:scaleX(0)"></div></div>

<!-- Nav -->
<header class="fixed top-0 inset-x-0 z-50 transition-all duration-300" :class="scrolled ? 'py-2' : 'py-4'"
        x-data="{sec:''}" x-init="
          const ids=['features','showcase','planes','faq'];
          const io=new IntersectionObserver((es)=>es.forEach(e=>{if(e.isIntersecting) sec=e.target.id;}),{rootMargin:'-45% 0px -50% 0px'});
          ids.forEach(i=>{const el=document.getElementById(i); if(el) io.observe(el);});
        ">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between rounded-xl px-3 sm:px-4 h-14 transition-all duration-300"
         :class="scrolled ? 'bg-[#141E30]/85 backdrop-blur-xl border border-white/[0.08] shadow-soft' : ''">
      <a href="<?= url('/') ?>" class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg grad-bg grid place-items-center font-black text-white text-sm">K</div>
        <span class="font-display font-extrabold text-[17px] tracking-tight">Kyros</span>
      </a>
      <nav class="hidden md:flex items-center gap-1 text-[14px] font-medium">
        <a href="#features" :class="sec==='features'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Plataforma</a>
        <a href="#showcase" :class="sec==='showcase'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Producto</a>
        <a href="#planes" :class="sec==='planes'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">Planes</a>
        <a href="#faq" :class="sec==='faq'?'text-white bg-white/10':'text-white/55 hover:text-white'" class="px-3 py-1.5 rounded-lg transition">FAQ</a>
      </nav>
      <div class="flex items-center gap-2">
        <a href="<?= url('/login') ?>" class="hidden sm:inline-flex k-btn k-btn-ghost !h-9 !px-3 !text-[14px] !text-white/70 hover:!bg-white/10 hover:!text-white">Iniciar sesión</a>
        <a href="<?= url('/register') ?>" class="k-btn k-btn-light !h-9 !px-4 !text-[14px]">Crear mi rent car</a>
      </div>
    </div>
  </div>
</header>

<?= $content ?>

<!-- Footer -->
<footer class="relative border-t border-white/10 mt-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid md:grid-cols-4 gap-10">
    <div class="md:col-span-2">
      <div class="flex items-center gap-2.5 mb-4">
        <div class="w-9 h-9 rounded-xl grad-bg grid place-items-center font-black">K</div>
        <span class="font-display font-extrabold text-lg">Kyros Rent Car</span>
      </div>
      <p class="text-white/50 text-sm max-w-sm">La plataforma de clase mundial para administrar tu rent car: flotilla, reservas, contratos, pagos y tu pagina publica.</p>
    </div>
    <div>
      <p class="font-semibold text-sm mb-3">Producto</p>
      <ul class="space-y-2 text-sm text-white/50">
        <li><a href="#features" class="hover:text-white">Plataforma</a></li>
        <li><a href="#planes" class="hover:text-white">Planes</a></li>
        <li><a href="<?= url('/r/kyros-rent-car') ?>" class="hover:text-white">Demo en vivo</a></li>
      </ul>
    </div>
    <div>
      <p class="font-semibold text-sm mb-3">Cuenta</p>
      <ul class="space-y-2 text-sm text-white/50">
        <li><a href="<?= url('/login') ?>" class="hover:text-white">Iniciar sesion</a></li>
        <li><a href="<?= url('/register') ?>" class="hover:text-white">Crear rent car</a></li>
      </ul>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-white/40">
      <p>&copy; <?= date('Y') ?> Kyros Rent Car. Todos los derechos reservados.</p>
      <p>Hecho con precision para rent cars de clase mundial.</p>
    </div>
  </div>
</footer>

<div class="fixed bottom-5 right-5 z-50 space-y-2.5" id="toasts"></div>
<script>
  const flashes=[<?php foreach ($flashes as $type=>$messages): foreach ((array)$messages as $m): ?>{type:<?= json_encode($type) ?>,message:<?= json_encode($m) ?>},<?php endforeach; endforeach; ?>];
  const dot={success:'bg-emerald-400',error:'bg-red-400',warning:'bg-amber-400',info:'bg-white/60'};
  flashes.forEach(f=>{const el=document.createElement('div');el.className='glass flex items-center gap-3 pl-4 pr-3 py-3 rounded-2xl min-w-[280px] text-white';el.innerHTML='<span class="w-2 h-2 rounded-full '+(dot[f.type]||'bg-white/60')+'"></span><span class="text-sm font-medium"></span>';el.querySelector('span:last-child').textContent=f.message;document.getElementById('toasts').appendChild(el);setTimeout(()=>el.remove(),6000);});
  document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons());
  document.addEventListener('alpine:initialized',()=>window.lucide&&lucide.createIcons());
  (function(){var bar=document.getElementById('kprogress');function upd(){var h=document.documentElement;var p=h.scrollTop/((h.scrollHeight-h.clientHeight)||1);bar.style.transform='scaleX('+Math.min(1,Math.max(0,p))+')';}window.addEventListener('scroll',upd,{passive:true});upd();})();
</script>
<?= $pageScripts ?? '' ?>
<?= View::stack('scripts') ?>
</body>
</html>
