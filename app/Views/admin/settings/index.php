<?php
function sval($t,$k,$d=''){ return e($t[$k] ?? $d); }
$publicUrl = rtrim(\App\Core\Config::get('app.url'),'/').'/r/'.$tenant['slug'];
?>
<div class="max-w-3xl mx-auto" x-data="{tab:'general'}">
  <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1">Configuración</h1>
  <p class="text-sm text-slate-500 mb-6">Datos de tu empresa y página pública</p>

  <!-- Public URL banner -->
  <div class="card p-4 mb-5 flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
      <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand grid place-items-center shrink-0"><i data-lucide="link" class="w-5 h-5"></i></div>
      <div class="min-w-0"><p class="text-sm font-medium text-navy">Tu página pública</p><a href="<?= url('/r/'.$tenant['slug']) ?>" target="_blank" class="text-sm text-brand hover:underline truncate block"><?= e($publicUrl) ?></a></div>
    </div>
    <a href="<?= url('/r/'.$tenant['slug']) ?>" target="_blank" class="k-btn k-btn-outline shrink-0"><i data-lucide="external-link" class="w-4 h-4"></i> Ver</a>
  </div>

  <!-- Tabs -->
  <div class="flex gap-1 p-1 rounded-xl bg-paper border hairline w-fit mb-5">
    <?php foreach (['general'=>'General','brand'=>'Marca','billing'=>'Facturación'] as $k=>$lbl): ?>
    <button type="button" @click="tab='<?= $k ?>'" :class="tab==='<?= $k ?>'?'bg-white shadow-xs text-navy':'text-slate-500'" class="px-4 py-2 rounded-lg text-sm font-semibold transition"><?= $lbl ?></button>
    <?php endforeach; ?>
  </div>

  <form method="POST" action="<?= url('/admin/settings') ?>" enctype="multipart/form-data" class="space-y-5">
    <?= csrf_field() ?>

    <!-- General -->
    <div x-show="tab==='general'" class="card p-6">
      <h2 class="font-display font-bold text-navy mb-4">Información general</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Nombre comercial *</label><input name="name" required value="<?= sval($tenant,'name') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Razón social</label><input name="legal_name" value="<?= sval($tenant,'legal_name') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Email</label><input type="email" name="email" value="<?= sval($tenant,'email') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Teléfono</label><input name="phone" value="<?= sval($tenant,'phone') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">WhatsApp</label><input name="whatsapp" value="<?= sval($tenant,'whatsapp') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">RNC</label><input name="rnc" value="<?= sval($tenant,'rnc') ?>" class="fld"></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Dirección</label><input name="address" value="<?= sval($tenant,'address') ?>" class="fld"></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Descripción</label><textarea name="description" rows="3" class="fld"><?= sval($tenant,'description') ?></textarea></div>
      </div>
    </div>

    <!-- Brand -->
    <div x-show="tab==='brand'" x-cloak class="card p-6">
      <h2 class="font-display font-bold text-navy mb-4">Identidad de marca</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Color primario</label><input type="color" name="primary_color" value="<?= sval($tenant,'primary_color','#F23645') ?>" class="w-full h-11 rounded-xl border hairline cursor-pointer"></div>
        <div><label class="block text-sm font-medium mb-1.5">Color secundario</label><input type="color" name="secondary_color" value="<?= sval($tenant,'secondary_color','#1C2433') ?>" class="w-full h-11 rounded-xl border hairline cursor-pointer"></div>
        <div><label class="block text-sm font-medium mb-1.5">Logo</label><input type="file" name="logo" accept="image/*" class="fld"><?php if(!empty($tenant['logo'])): ?><img src="<?= e($tenant['logo']) ?>" class="mt-2 h-10"><?php endif; ?></div>
        <div><label class="block text-sm font-medium mb-1.5">Imagen de portada</label><input type="file" name="cover_image" accept="image/*" class="fld"><?php if(!empty($tenant['cover_image'])): ?><img src="<?= e($tenant['cover_image']) ?>" class="mt-2 h-16 w-full object-cover rounded-lg"><?php endif; ?></div>
      </div>
      <p class="text-xs text-slate-400 mt-3">Estos colores se aplican a tu página pública de reservas.</p>
    </div>

    <!-- Billing -->
    <div x-show="tab==='billing'" x-cloak class="card p-6">
      <h2 class="font-display font-bold text-navy mb-4">Facturación y moneda</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Moneda</label><input name="currency" value="<?= sval($tenant,'currency','DOP') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Impuesto (%)</label><input type="number" step="0.01" name="tax_rate" value="<?= sval($tenant,'tax_rate','18') ?>" class="fld"></div>
      </div>
      <p class="text-xs text-slate-400 mt-3">El impuesto se aplica automáticamente al calcular reservas y contratos.</p>
    </div>

    <div class="flex items-center gap-3">
      <button type="submit" class="k-btn k-btn-grad !px-6">Guardar configuración</button>
    </div>
  </form>
</div>
