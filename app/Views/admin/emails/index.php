<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Plantillas de correo</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Personaliza los correos automáticos que reciben tus clientes y tu equipo</p>
  </div>
  <a href="<?= url('/super-admin/settings') ?>" class="hidden"></a>
</div>

<div class="card p-4 mb-5 flex items-center gap-3">
  <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 grid place-items-center shrink-0"><i data-lucide="info" class="w-5 h-5"></i></div>
  <p class="text-sm text-slate-600 dark:text-slate-300">El envío real usa <strong>Resend</strong> (configurado por el Super Admin). Si está deshabilitado, los correos se registran en el log y los flujos no se interrumpen.</p>
</div>

<div class="grid sm:grid-cols-2 gap-4">
  <?php foreach ($templates as $tpl): ?>
  <a href="<?= url('/admin/emails/edit/'.$tpl['code']) ?>" class="card p-5 reveal group hover:border-brand/30 transition">
    <div class="flex items-start justify-between gap-3">
      <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand grid place-items-center shrink-0"><i data-lucide="mail" class="w-5 h-5"></i></div>
      <div class="flex items-center gap-2">
        <?php if ($tpl['customized']): ?><span class="px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 text-[11px] font-semibold">Personalizada</span><?php endif; ?>
        <?php if ($tpl['status']==='inactive'): ?><span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 text-[11px] font-semibold">Inactiva</span>
        <?php else: ?><span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-[11px] font-semibold">Activa</span><?php endif; ?>
      </div>
    </div>
    <h3 class="font-display font-bold text-navy dark:text-white mt-4 group-hover:text-brand transition"><?= e($tpl['label']) ?></h3>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-snug"><?= e($tpl['desc']) ?></p>
    <p class="text-xs text-slate-400 mt-3 pt-3 border-t hairline truncate"><span class="font-medium text-slate-500">Asunto:</span> <?= e($tpl['subject']) ?></p>
  </a>
  <?php endforeach; ?>
</div>
