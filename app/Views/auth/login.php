<?php $demoOffers = $demoOffers ?? []; ?>
<div x-data="{tab: window.location.hash === '#demo' ? 'demo' : 'login'}">

  <!-- Tabs -->
  <div class="inline-flex p-1 rounded-2xl bg-white/[0.04] border border-white/[0.08] mb-7">
    <button type="button" @click="tab='login'; window.history.replaceState(null,'','#')"
            :class="tab==='login' ? 'bg-white text-navy shadow-sm' : 'text-white/60 hover:text-white'"
            class="px-5 py-2 rounded-xl text-sm font-semibold transition-all">Iniciar sesión</button>
    <button type="button" @click="tab='demo'; window.history.replaceState(null,'','#demo')"
            :class="tab==='demo' ? 'bg-white text-navy shadow-sm' : 'text-white/60 hover:text-white'"
            class="px-5 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-1.5">
      <span class="relative w-1.5 h-1.5"><span class="absolute inset-0 rounded-full bg-brand animate-pulse"></span></span>
      Demo · 5h
    </button>
  </div>

  <!-- ============ LOGIN ============ -->
  <div x-show="tab==='login'" x-transition.opacity.duration.250ms>
    <h2 class="font-display text-3xl font-extrabold tracking-tight">Bienvenido de nuevo</h2>
    <p class="text-white/55 mt-2">Inicia sesión para administrar tu rent car.</p>

    <form method="POST" action="<?= url('/login') ?>" class="mt-8 space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium text-white/70 mb-1.5">Correo electrónico</label>
        <input type="email" name="email" value="<?= old('email') ?>" required autofocus
               placeholder="tu@empresa.com" class="fld-dark">
      </div>
      <div>
        <label class="block text-sm font-medium text-white/70 mb-1.5">Contraseña</label>
        <div class="relative" x-data="{show:false}">
          <input :type="show?'text':'password'" name="password" required placeholder="••••••••" class="fld-dark pr-11">
          <button type="button" @click="show=!show" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white">
            <i data-lucide="eye" x-show="!show" class="w-5 h-5"></i>
            <i data-lucide="eye-off" x-show="show" x-cloak class="w-5 h-5"></i>
          </button>
        </div>
      </div>
      <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 text-white/60"><input type="checkbox" name="remember" class="rounded border-white/20 bg-white/10 text-brand focus:ring-brand/40"> Recordarme</label>
        <a href="<?= url('/forgot-password') ?>" class="text-brand2 font-medium hover:text-white transition">¿Olvidaste tu contraseña?</a>
      </div>
      <button type="submit" class="k-btn k-btn-grad w-full !py-3 text-base">Iniciar sesión <i data-lucide="arrow-right" class="w-4 h-4"></i></button>
    </form>

    <p class="mt-6 text-center text-sm text-white/50">
      ¿No tienes cuenta? <a href="<?= url('/register') ?>" class="text-white font-semibold hover:text-brand2 transition">Crea tu rent car</a>
    </p>

    <div class="mt-8 glass rounded-2xl p-4 text-xs text-white/55">
      <p class="font-semibold text-white/75 mb-1.5 flex items-center gap-1.5"><i data-lucide="key-round" class="w-3.5 h-3.5"></i> Credenciales demo (cuenta persistente)</p>
      <p>Super Admin: <b class="text-white/85 font-mono">admin@kyrosrd.com</b> / Admin123*</p>
      <p>Rent Car: <b class="text-white/85 font-mono">owner@demo.com</b> / Demo123*</p>
    </div>
  </div>

  <!-- ============ DEMO LICENSE ============ -->
  <div x-show="tab==='demo'" x-transition.opacity.duration.250ms x-cloak>
    <h2 class="font-display text-3xl font-extrabold tracking-tight">Prueba con un código demo</h2>
    <p class="text-white/55 mt-2">Cuenta nueva con datos de muestra. <b class="text-white/80">5 horas</b> para explorar sin compromiso.</p>

    <form method="POST" action="<?= url('/demo') ?>" class="mt-8 space-y-4" x-data="{ code: '' }">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium text-white/70 mb-1.5">Código de licencia</label>
        <input type="text" name="demo_code" required autofocus
               x-model="code" oninput="this.value=this.value.toUpperCase()"
               placeholder="KYROS-DEMO-PREMIUM" class="fld-dark font-mono uppercase">
      </div>
      <div>
        <label class="block text-sm font-medium text-white/70 mb-1.5">Tu nombre <span class="text-white/35">(opcional)</span></label>
        <input type="text" name="demo_name" value="Demo User" placeholder="Cómo te llamamos" class="fld-dark">
      </div>

      <button type="submit" class="k-btn k-btn-grad w-full !py-3 text-base">Activar demo <i data-lucide="zap" class="w-4 h-4"></i></button>

      <p class="text-[11px] text-white/40 text-center">Al activar, te creamos un tenant nuevo y todos los datos se eliminan al expirar.</p>
    </form>

    <?php if (!empty($demoOffers)): ?>
    <div class="mt-7">
      <p class="text-[11px] uppercase tracking-[0.18em] text-white/40 font-bold mb-3">Códigos disponibles</p>
      <div class="space-y-2">
        <?php foreach ($demoOffers as $o): ?>
        <button type="button" @click="code='<?= e($o['code']) ?>'; document.querySelector('input[name=demo_code]').value='<?= e($o['code']) ?>'"
                class="w-full glass rounded-xl p-4 text-left hover:border-white/20 transition flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-brand/15 text-brand grid place-items-center shrink-0">
            <i data-lucide="<?= $o['plan_slug'] === 'premium' ? 'crown' : ($o['plan_slug'] === 'business' ? 'rocket' : 'gem') ?>" class="w-5 h-5"></i>
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-mono font-bold text-white text-sm truncate"><?= e($o['code']) ?></p>
            <p class="text-[11px] text-white/55 mt-0.5">Plan <?= e($o['plan_name']) ?> · <?= (int)$o['hours_valid'] ?>h · <?= (int)$o['max_vehicles'] === -1 ? 'Vehículos ∞' : $o['max_vehicles'].' vehículos' ?></p>
          </div>
          <i data-lucide="arrow-right" class="w-4 h-4 text-white/35 shrink-0"></i>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

</div>
