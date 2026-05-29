<div>
  <h2 class="font-display text-3xl font-extrabold tracking-tight">Nueva contrasena</h2>
  <p class="text-white/50 mt-2">Elige una contrasena segura (min. 8 caracteres).</p>

  <form method="POST" action="<?= url('/reset-password') ?>" class="mt-8 space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
    <div>
      <label class="block text-sm font-medium text-white/70 mb-1.5">Nueva contrasena</label>
      <input type="password" name="password" required minlength="8" class="fld-dark">
    </div>
    <div>
      <label class="block text-sm font-medium text-white/70 mb-1.5">Confirmar contrasena</label>
      <input type="password" name="password_confirmation" required minlength="8" class="fld-dark">
    </div>
    <button type="submit" class="k-btn k-btn-grad w-full !py-3 text-base">Guardar contrasena</button>
  </form>
</div>
