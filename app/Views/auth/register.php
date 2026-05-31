<?php $planName = $plan['name'] ?? 'Starter'; $planSlug = $plan['slug'] ?? 'starter'; ?>
<div>
  <h2 class="font-display text-3xl font-extrabold tracking-tight">Crea tu rent car</h2>
  <p class="text-white/50 mt-2">Empieza con el plan <strong class="text-white"><?= e($planName) ?></strong>. Sin tarjeta.</p>

  <form method="POST" action="<?= url('/register') ?>" class="mt-8 space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="plan" value="<?= e($planSlug) ?>">
    <div>
      <label class="block text-sm font-medium text-white/70 mb-1.5">Nombre de la empresa</label>
      <input type="text" name="company" value="<?= old('company') ?>" required placeholder="Speed Rent Car" class="fld-dark">
    </div>
    <div>
      <label class="block text-sm font-medium text-white/70 mb-1.5">Tu nombre</label>
      <input type="text" name="owner_name" value="<?= old('owner_name') ?>" required placeholder="Juan Perez" class="fld-dark">
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium text-white/70 mb-1.5">Correo</label>
        <input type="email" name="email" value="<?= old('email') ?>" required placeholder="tu@empresa.com" class="fld-dark">
      </div>
      <div>
        <label class="block text-sm font-medium text-white/70 mb-1.5">Telefono</label>
        <input type="text" name="phone" value="<?= old('phone') ?>" placeholder="+1 809 ..." class="fld-dark">
      </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium text-white/70 mb-1.5">Contrasena</label>
        <input type="password" name="password" required minlength="8" placeholder="Min. 8 caracteres" class="fld-dark">
      </div>
      <div>
        <label class="block text-sm font-medium text-white/70 mb-1.5">Confirmar</label>
        <input type="password" name="password_confirmation" required minlength="8" placeholder="Repite" class="fld-dark">
      </div>
    </div>
    <button type="submit" class="k-btn k-btn-grad w-full !py-3 text-base">Crear mi rent car <i data-lucide="arrow-right" class="w-4 h-4"></i></button>
  </form>

  <p class="mt-6 text-center text-sm text-white/50">¿Ya tienes cuenta? <a href="<?= url('/login') ?>" class="text-white font-semibold hover:text-brand2 transition">Inicia sesion</a></p>
</div>
