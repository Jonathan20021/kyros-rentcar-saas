<?php
use App\Models\PromoCode;
$isEdit = !empty($promo);
$action = $isEdit ? url('/admin/promos/update/'.$promo['id']) : url('/admin/promos');
$val = fn(string $k, $d = '') => e($isEdit ? ($promo[$k] ?? $d) : $d);
?>
<div class="max-w-3xl mx-auto">
  <div class="mb-5">
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white"><?= $isEdit ? 'Editar código' : 'Nuevo código' ?></h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Configura un descuento aplicable a reservas online o internas.</p>
  </div>

  <form method="POST" action="<?= $action ?>" class="card p-6 space-y-5"
        x-data="{
          type: '<?= $val('discount_type','percent') ?>',
          value: <?= (float)($isEdit ? ($promo['discount_value'] ?? 0) : 10) ?>,
          isPublic: <?= $isEdit ? (int)($promo['is_public'] ?? 1) : 1 ?> === 1
        }">
    <?= csrf_field() ?>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Código *</label>
        <input name="code" required maxlength="40" value="<?= $val('code') ?>" placeholder="VERANO15"
               class="fld font-mono uppercase" oninput="this.value=this.value.toUpperCase()">
        <p class="text-[11px] text-slate-400 mt-1">Solo letras, números, guiones y guión bajo.</p>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Estado</label>
        <select name="status" class="fld">
          <option value="active"   <?= ($isEdit && $promo['status']==='inactive')?'':'selected' ?>>Activo</option>
          <option value="inactive" <?= ($isEdit && $promo['status']==='inactive')?'selected':'' ?>>Inactivo</option>
        </select>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1.5">Descripción</label>
      <input name="description" maxlength="200" value="<?= $val('description') ?>" placeholder="Promoción de verano" class="fld">
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Tipo de descuento *</label>
        <div class="seg w-full">
          <input type="radio" name="discount_type" id="dt-percent" value="percent" x-model="type" <?= !$isEdit || $promo['discount_type']==='percent' ? 'checked' : '' ?>>
          <label for="dt-percent" class="flex-1 text-center">Porcentaje</label>
          <input type="radio" name="discount_type" id="dt-fixed" value="fixed" x-model="type" <?= $isEdit && $promo['discount_type']==='fixed' ? 'checked' : '' ?>>
          <label for="dt-fixed" class="flex-1 text-center">Monto fijo</label>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Valor *</label>
        <div class="relative">
          <input type="number" step="0.01" min="0" name="discount_value" required value="<?= $val('discount_value') ?>"
                 x-model="value" class="fld pr-12">
          <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold pointer-events-none"
                x-text="type==='percent' ? '%' : 'RD$'"></span>
        </div>
      </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Compra mínima</label>
        <input type="number" step="0.01" min="0" name="min_amount" value="<?= $val('min_amount','0') ?>" class="fld" placeholder="0">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Máximo de usos</label>
        <input type="number" min="1" name="max_uses" value="<?= e($isEdit ? ($promo['max_uses'] ?? '') : '') ?>" class="fld" placeholder="Ilimitado">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5 invisible">.</label>
        <label class="flex items-center gap-3 h-[42px] px-3 rounded-xl border hairline cursor-pointer">
          <input type="hidden" name="is_public" value="0">
          <input type="checkbox" name="is_public" value="1" x-model="isPublic" class="w-4 h-4 accent-current text-brand">
          <span class="text-sm text-slate-700 dark:text-slate-200">Visible en página pública</span>
        </label>
      </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Válido desde</label>
        <input type="date" name="valid_from" value="<?= $val('valid_from') ?>" class="fld">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Válido hasta</label>
        <input type="date" name="valid_to" value="<?= $val('valid_to') ?>" class="fld">
      </div>
    </div>

    <!-- Live preview pill -->
    <div class="rounded-2xl border-2 border-dashed border-brand/30 bg-brand/5 p-4 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-brand text-white grid place-items-center"><i data-lucide="ticket-percent" class="w-5 h-5"></i></div>
      <div class="min-w-0 flex-1">
        <p class="text-[11px] uppercase font-bold text-brand">Vista previa</p>
        <p class="font-display font-extrabold text-navy dark:text-white text-lg leading-tight">
          <span x-text="type==='percent' ? value+'% de descuento' : 'RD$ '+Number(value).toLocaleString('en-US',{minimumFractionDigits:2})+' de descuento'"></span>
        </p>
      </div>
      <span class="px-3 py-1.5 rounded-lg bg-white border hairline font-mono font-bold text-navy text-sm" x-text="document.querySelector('input[name=code]')?.value || 'CÓDIGO'">CÓDIGO</span>
    </div>

    <div class="k-sticky flex gap-2 pt-2">
      <button type="submit" class="k-btn k-btn-grad"><?= $isEdit ? 'Guardar' : 'Crear código' ?></button>
      <a href="<?= url('/admin/promos') ?>" class="k-btn k-btn-outline">Cancelar</a>
    </div>
  </form>
</div>
