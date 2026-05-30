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
        <span class="font-display font-extrabold text-[17px] tracking-tight">Kyros Rent Car</span>
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
<footer class="relative border-t border-white/[0.07] bg-[#0B1120] mt-10">
  <div class="max-w-7xl mx-auto px-5 sm:px-6 py-16 grid md:grid-cols-12 gap-10">
    <!-- Brand block -->
    <div class="md:col-span-5">
      <div class="flex items-center gap-2.5 mb-4">
        <div class="w-10 h-10 rounded-xl grad-bg grid place-items-center font-black text-white">K</div>
        <span class="font-display font-extrabold text-xl tracking-tight">Kyros Rent Car</span>
      </div>
      <p class="text-white/55 text-[14.5px] max-w-sm leading-relaxed">El sistema operativo de tu rent car. Flotilla, reservas, contratos, pagos y página pública — en una plataforma.</p>

      <!-- Status -->
      <a href="#" class="inline-flex items-center gap-2 mt-6 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[12.5px] text-emerald-400 hover:bg-emerald-500/15 transition">
        <span class="relative w-2 h-2 rounded-full bg-emerald-400">
          <span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-60"></span>
        </span>
        Todos los sistemas operativos
      </a>
    </div>

    <div class="md:col-span-2">
      <p class="font-semibold text-[13px] text-white/85 mb-4 uppercase tracking-wider">Producto</p>
      <ul class="space-y-2.5 text-[14px] text-white/55">
        <li><a href="#features" class="hover:text-white transition">Plataforma</a></li>
        <li><a href="#showcase" class="hover:text-white transition">Recorrido</a></li>
        <li><a href="#planes" class="hover:text-white transition">Planes</a></li>
        <li><a href="<?= url('/r/kyros-rent-car') ?>" class="hover:text-white transition flex items-center gap-1">Demo en vivo <i data-lucide="external-link" class="w-3 h-3"></i></a></li>
      </ul>
    </div>

    <div class="md:col-span-2">
      <p class="font-semibold text-[13px] text-white/85 mb-4 uppercase tracking-wider">Cuenta</p>
      <ul class="space-y-2.5 text-[14px] text-white/55">
        <li><a href="<?= url('/login') ?>" class="hover:text-white transition">Iniciar sesión</a></li>
        <li><a href="<?= url('/login#demo') ?>" class="hover:text-white transition">Probar demo · 5h</a></li>
        <li><a href="<?= url('/register') ?>" class="hover:text-white transition">Crear rent car</a></li>
      </ul>
    </div>

    <div class="md:col-span-3">
      <p class="font-semibold text-[13px] text-white/85 mb-4 uppercase tracking-wider">Contacto</p>
      <ul class="space-y-2.5 text-[14px] text-white/55">
        <li class="flex items-start gap-2"><i data-lucide="mail" class="w-3.5 h-3.5 mt-1 shrink-0"></i><a href="mailto:soporte@kyrosrd.com" class="hover:text-white transition">soporte@kyrosrd.com</a></li>
        <li class="flex items-start gap-2"><i data-lucide="map-pin" class="w-3.5 h-3.5 mt-1 shrink-0"></i>Santo Domingo, RD</li>
      </ul>
      <div class="flex items-center gap-2 mt-5">
        <a href="#" class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.07] grid place-items-center text-white/55 hover:text-white transition"><i data-lucide="twitter" class="w-4 h-4"></i></a>
        <a href="#" class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.07] grid place-items-center text-white/55 hover:text-white transition"><i data-lucide="instagram" class="w-4 h-4"></i></a>
        <a href="#" class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.07] grid place-items-center text-white/55 hover:text-white transition"><i data-lucide="linkedin" class="w-4 h-4"></i></a>
      </div>
    </div>
  </div>

  <!-- Bottom bar -->
  <div class="border-t border-white/[0.06]">
    <div class="max-w-7xl mx-auto px-5 sm:px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-[12.5px] text-white/45">
      <p>&copy; <?= date('Y') ?> Kyros Rent Car · Todos los derechos reservados.</p>
      <div class="flex items-center gap-5">
        <a href="#" class="hover:text-white transition">Términos</a>
        <a href="#" class="hover:text-white transition">Privacidad</a>
        <a href="#" class="hover:text-white transition">Seguridad</a>
      </div>
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
