<div>
  <h2 class="font-display text-3xl font-extrabold tracking-tight">Recuperar contrasena</h2>
  <p class="text-white/50 mt-2">Te enviaremos un enlace para restablecerla.</p>

  <form method="POST" action="<?= url('/forgot-password') ?>" class="mt-8 space-y-4">
    <?= csrf_field() ?>
    <div>
      <label class="block text-sm font-medium text-white/70 mb-1.5">Correo electronico</label>
      <input type="email" name="email" value="<?= old('email') ?>" required autofocus placeholder="tu@empresa.com" class="fld-dark">
    </div>
    <button type="submit" class="k-btn k-btn-grad w-full !py-3 text-base">Enviar enlace</button>
  </form>
  <p class="mt-6 text-center text-sm text-white/50"><a href="<?= url('/login') ?>" class="text-white font-semibold hover:text-brand2 transition">Volver a iniciar sesion</a></p>
</div>
