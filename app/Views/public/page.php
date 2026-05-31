<?php
$sections = $page['sections'] ?? [];
?>
<main class="bg-[#0B1120] text-white">
  <section class="relative overflow-hidden pt-32 pb-20 sm:pt-40 sm:pb-28">
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute inset-0 grid-dark opacity-35"></div>
      <div class="absolute inset-x-0 top-0 h-px" style="background:var(--grad)"></div>
      <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-[#101828] to-transparent"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-5 sm:px-6">
      <div class="max-w-3xl">
        <p class="eyebrow text-brand mb-4"><?= e($page['eyebrow'] ?? 'Kyros') ?></p>
        <h1 class="font-display display-xl text-[42px] sm:text-[72px] font-extrabold text-white"><?= e($page['headline'] ?? '') ?></h1>
        <p class="mt-6 text-lg leading-relaxed text-white/62 max-w-2xl"><?= e($page['intro'] ?? '') ?></p>
        <div class="mt-9 flex flex-col sm:flex-row gap-3">
          <a href="<?= url('/register') ?>" class="k-cta magnetic group">
            <span>Crear mi rent car</span>
            <span class="k-cta-arrow"><i data-lucide="arrow-right" class="w-4 h-4"></i></span>
          </a>
          <a href="<?= url('/login#demo') ?>" class="k-cta k-cta-ghost group">
            <span>Probar demo</span>
            <span class="k-cta-arrow"><i data-lucide="play" class="w-4 h-4"></i></span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="border-y border-white/[0.06] bg-[#101828] py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-5 sm:px-6">
      <div class="grid md:grid-cols-2 gap-4">
        <?php foreach ($sections as $i => $section): ?>
        <article class="bento p-6 sm:p-8 reveal">
          <div class="w-10 h-10 rounded-xl grid place-items-center bg-brand/15 text-brand mb-8">
            <span class="font-display font-extrabold tnum"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          </div>
          <h2 class="font-display text-2xl font-extrabold tracking-tight text-white"><?= e($section[0]) ?></h2>
          <p class="mt-3 text-white/58 leading-relaxed"><?= e($section[1]) ?></p>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="py-16 sm:py-24">
    <div class="max-w-5xl mx-auto px-5 sm:px-6">
      <div class="bezel-outer reveal-s">
        <div class="bezel-inner p-7 sm:p-12 text-center" style="background:var(--grad)">
          <h2 class="font-display text-[32px] sm:text-[52px] font-extrabold leading-tight">Convierte tu operacion en un sistema claro.</h2>
          <p class="mt-4 text-white/80 max-w-xl mx-auto">Activa la pagina publica, sube tu flotilla y empieza a tomar reservas con una marca propia.</p>
          <a href="<?= url('/register') ?>" class="k-cta k-cta-light magnetic group mt-8">
            <span>Empezar ahora</span>
            <span class="k-cta-arrow"><i data-lucide="arrow-right" class="w-4 h-4"></i></span>
          </a>
        </div>
      </div>
    </div>
  </section>
</main>
