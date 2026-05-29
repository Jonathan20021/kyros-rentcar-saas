<?php
$custName = $customer ? trim($customer['first_name'].' '.($customer['last_name'] ?? '')) : ($r['lead_name'] ?? 'Cliente');
$hasCustomer = (bool) $customer;
$hasLead = !empty($r['lead_name']);
$canConvert = !$contract && !in_array($r['status'], ['cancelled','rejected'], true);
$canCancel  = !$contract && !in_array($r['status'], ['cancelled','rejected','converted','finished'], true);
?>
<div class="max-w-5xl mx-auto">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
      <div class="flex items-center gap-3">
        <h1 class="font-display text-2xl font-bold text-navy dark:text-white"><?= e($r['reservation_code']) ?></h1>
        <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($r['status']) ?>"><?= status_label($r['status']) ?></span>
        <?php if ($r['source']==='public'): ?><span class="px-2 py-0.5 rounded-full text-[11px] bg-indigo-50 text-indigo-600 font-medium">Pública</span><?php endif; ?>
      </div>
      <p class="text-sm text-slate-500 mt-1"><?= e($custName) ?> · creada <?= format_date($r['created_at']) ?></p>
    </div>
    <div class="flex gap-2 flex-wrap">
      <?php if ($canConvert && can('contracts.create') && $hasCustomer): ?>
        <button type="button" @click="document.getElementById('convertModal').classList.remove('hidden')" class="k-btn k-btn-grad">
          <i data-lucide="file-plus" class="w-4 h-4"></i> Generar contrato
        </button>
      <?php elseif ($canConvert && can('contracts.create') && !$hasCustomer): ?>
        <button type="button" @click="document.getElementById('assignModal').classList.remove('hidden')" class="k-btn k-btn-grad">
          <i data-lucide="user-plus" class="w-4 h-4"></i> Asignar cliente para generar contrato
        </button>
      <?php endif; ?>
      <?php if ($contract): ?>
        <a href="<?= url('/admin/contracts/show/'.$contract['id']) ?>" class="k-btn k-btn-dark"><i data-lucide="file-text" class="w-4 h-4"></i> Ver contrato</a>
      <?php endif; ?>
      <?php if ($canCancel && can('reservations.cancel')): ?>
        <form method="POST" action="<?= url('/admin/reservations/cancel/'.$r['id']) ?>" class="inline"
              data-confirm="El cliente será notificado y el vehículo se liberará si corresponde."
              data-confirm-title="¿Cancelar reserva?" data-confirm-label="Sí, cancelar" data-confirm-variant="warning">
          <?= csrf_field() ?>
          <button class="k-btn k-btn-outline"><i data-lucide="x" class="w-4 h-4"></i> Cancelar reserva</button>
        </form>
      <?php endif; ?>
      <a href="<?= url('/admin/reservations') ?>" class="k-btn k-btn-ghost">Volver</a>
    </div>
  </div>

  <?php if (!$hasCustomer && $hasLead): ?>
  <div class="card p-4 mb-5 border-2 border-amber-200 bg-gradient-to-r from-amber-50 to-white dark:from-amber-500/10 dark:to-slate-900 flex items-center gap-3">
    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300 grid place-items-center shrink-0">
      <i data-lucide="user-x" class="w-5 h-5"></i>
    </div>
    <div class="flex-1">
      <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">Reserva pública sin cliente registrado</p>
      <p class="text-[12.5px] text-amber-700 dark:text-amber-200">Asigna un cliente para generar el contrato. Puedes crear uno con los datos del prospecto (<?= e($r['lead_name']) ?>).</p>
    </div>
    <button type="button" @click="document.getElementById('assignModal').classList.remove('hidden')" class="k-btn k-btn-dark !h-9">Asignar</button>
  </div>
  <?php endif; ?>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
      <div class="card p-6">
        <h2 class="font-display font-bold text-navy dark:text-white mb-4">Detalle de la reserva</h2>
        <div class="grid sm:grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-400">Vehículo</p><p class="font-medium text-navy dark:text-white"><?= e($vehicle['brand'].' '.$vehicle['model']) ?> · <?= e($vehicle['plate_number'] ?? '') ?></p></div>
          <div><p class="text-slate-400">Cliente</p><p class="font-medium text-navy dark:text-white"><?= e($custName) ?><?php if (!$hasCustomer): ?> <span class="text-amber-600 text-[11px] font-semibold">(no registrado)</span><?php endif; ?></p></div>
          <div><p class="text-slate-400">Inicio</p><p class="font-medium text-navy dark:text-white tnum"><?= format_datetime($r['start_datetime']) ?></p></div>
          <div><p class="text-slate-400">Devolución</p><p class="font-medium text-navy dark:text-white tnum"><?= format_datetime($r['end_datetime']) ?></p></div>
          <div><p class="text-slate-400">Entrega</p><p class="font-medium text-navy dark:text-white"><?= e($r['pickup_location'] ?? '—') ?></p></div>
          <div><p class="text-slate-400">Devolución en</p><p class="font-medium text-navy dark:text-white"><?= e($r['return_location'] ?? '—') ?></p></div>
        </div>
        <?php if (!empty($r['notes'])): ?>
        <div class="mt-4 text-sm border-t hairline pt-4">
          <p class="text-slate-400">Notas</p>
          <p class="text-navy dark:text-white mt-1"><?= e($r['notes']) ?></p>
        </div>
        <?php endif; ?>
      </div>

      <?php if (!$hasCustomer && $hasLead): ?>
      <div class="card p-6">
        <h2 class="font-display font-bold text-navy dark:text-white mb-4">Datos del prospecto</h2>
        <div class="grid sm:grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-400">Nombre</p><p class="font-medium text-navy dark:text-white"><?= e($r['lead_name']) ?></p></div>
          <?php if ($r['lead_phone']): ?><div><p class="text-slate-400">Teléfono</p><p class="font-medium text-navy dark:text-white tnum"><?= e($r['lead_phone']) ?></p></div><?php endif; ?>
          <?php if ($r['lead_email']): ?><div><p class="text-slate-400">Email</p><p class="font-medium text-navy dark:text-white"><?= e($r['lead_email']) ?></p></div><?php endif; ?>
          <?php if ($r['lead_document']): ?><div><p class="text-slate-400">Documento</p><p class="font-medium text-navy dark:text-white tnum"><?= e($r['lead_document']) ?></p></div><?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($extras)): ?>
      <div class="card p-6">
        <h2 class="font-display font-bold text-navy dark:text-white mb-3">Extras</h2>
        <div class="divide-y hairline">
          <?php foreach ($extras as $x): ?>
            <div class="flex justify-between py-2.5 text-sm">
              <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-brand"></i><?= e($x['name']) ?>
              </span>
              <span class="font-medium text-navy dark:text-white tnum"><?= money($x['line_total']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="card p-6 h-fit">
      <h2 class="font-display font-bold text-navy dark:text-white mb-4">Resumen de precio</h2>
      <div class="space-y-2.5 text-sm">
        <div class="flex justify-between text-slate-500"><span>Subtotal (<?= (int)$r['days_count'] ?> días)</span><span class="font-medium text-navy dark:text-white tnum"><?= money($r['subtotal']) ?></span></div>
        <?php if ($r['discount_amount']>0): ?><div class="flex justify-between text-emerald-600"><span class="flex items-center gap-1"><i data-lucide="ticket-percent" class="w-3.5 h-3.5"></i>Descuento</span><span class="tnum">-<?= money($r['discount_amount']) ?></span></div><?php endif; ?>
        <?php if ($r['extras_total']>0): ?><div class="flex justify-between text-slate-500"><span>Extras</span><span class="font-medium text-navy dark:text-white tnum"><?= money($r['extras_total']) ?></span></div><?php endif; ?>
        <div class="flex justify-between text-slate-500"><span>Impuesto</span><span class="font-medium text-navy dark:text-white tnum"><?= money($r['tax_amount']) ?></span></div>
        <div class="flex justify-between text-slate-400 text-xs"><span>Depósito (reembolsable)</span><span class="tnum"><?= money($r['deposit_amount']) ?></span></div>
        <div class="flex justify-between pt-3 border-t hairline text-base"><span class="font-bold text-navy dark:text-white">Total</span><span class="font-extrabold text-brand tnum"><?= money($r['total_amount']) ?></span></div>
      </div>
    </div>
  </div>
</div>

<!-- Convert to contract modal -->
<?php if ($canConvert && $hasCustomer): ?>
<div id="convertModal" class="hidden fixed inset-0 z-50 grid place-items-center p-4">
  <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" onclick="document.getElementById('convertModal').classList.add('hidden')"></div>
  <div class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-lift border hairline w-full max-w-md overflow-hidden">
    <div class="p-6">
      <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-2xl bg-brand/10 text-brand grid place-items-center shrink-0"><i data-lucide="file-plus" class="w-6 h-6"></i></div>
        <div>
          <h3 class="font-display font-bold text-navy dark:text-white text-lg">Generar contrato</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Registra el estado del vehículo al momento de la entrega.</p>
        </div>
      </div>
      <form method="POST" action="<?= url('/admin/reservations/convert/'.$r['id']) ?>" enctype="multipart/form-data" class="mt-5 space-y-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium mb-1.5">Kilometraje salida</label>
            <input type="number" min="0" name="start_mileage" value="<?= (int)($vehicle['mileage'] ?? 0) ?>" class="fld">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1.5">Combustible (%)</label>
            <input type="number" name="start_fuel_level" value="100" min="0" max="100" class="fld">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Fotos de entrega</label>
          <input type="file" name="delivery_photos[]" accept="image/*" multiple class="fld">
          <p class="text-[11px] text-slate-400 mt-1">JPG, PNG. Múltiple selección permitida.</p>
        </div>
        <div class="flex gap-2 pt-2">
          <button type="submit" class="k-btn k-btn-grad flex-1">Confirmar contrato</button>
          <button type="button" onclick="document.getElementById('convertModal').classList.add('hidden')" class="k-btn k-btn-outline">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Assign customer modal (for public reservations without customer_id) -->
<?php if ($canConvert && !$hasCustomer): ?>
<div id="assignModal" class="hidden fixed inset-0 z-50 grid place-items-center p-4" x-data="{mode: '<?= $hasLead ? 'lead' : 'existing' ?>'}">
  <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" onclick="document.getElementById('assignModal').classList.add('hidden')"></div>
  <div class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-lift border hairline w-full max-w-lg overflow-hidden">
    <div class="p-6">
      <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-2xl bg-brand/10 text-brand grid place-items-center shrink-0"><i data-lucide="user-plus" class="w-6 h-6"></i></div>
        <div>
          <h3 class="font-display font-bold text-navy dark:text-white text-lg">Asignar cliente</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Necesario para generar el contrato.</p>
        </div>
      </div>

      <form method="POST" action="<?= url('/admin/reservations/assign-customer/'.$r['id']) ?>" class="mt-5 space-y-4">
        <?= csrf_field() ?>

        <?php if ($hasLead): ?>
        <div class="seg w-full">
          <input type="radio" name="mode" id="mode-lead" value="lead" x-model="mode" checked>
          <label for="mode-lead" class="flex-1 text-center text-sm">Crear desde prospecto</label>
          <input type="radio" name="mode" id="mode-existing" value="existing" x-model="mode">
          <label for="mode-existing" class="flex-1 text-center text-sm">Elegir existente</label>
        </div>
        <?php else: ?>
          <input type="hidden" name="mode" value="existing">
        <?php endif; ?>

        <!-- Lead-based create -->
        <?php if ($hasLead): ?>
        <div x-show="mode==='lead'" class="rounded-xl bg-paper dark:bg-slate-800/40 p-4 text-sm space-y-2">
          <div class="flex items-center gap-2"><i data-lucide="user" class="w-4 h-4 text-brand"></i><b class="text-navy dark:text-white"><?= e($r['lead_name']) ?></b></div>
          <?php if ($r['lead_phone']): ?><div class="text-slate-500 tnum"><i data-lucide="phone" class="w-3.5 h-3.5 inline mr-1"></i><?= e($r['lead_phone']) ?></div><?php endif; ?>
          <?php if ($r['lead_email']): ?><div class="text-slate-500"><i data-lucide="mail" class="w-3.5 h-3.5 inline mr-1"></i><?= e($r['lead_email']) ?></div><?php endif; ?>
          <?php if ($r['lead_document']): ?><div class="text-slate-500 tnum"><i data-lucide="badge" class="w-3.5 h-3.5 inline mr-1"></i><?= e($r['lead_document']) ?></div><?php endif; ?>
          <p class="text-[11px] text-slate-400 pt-2 border-t hairline">Si ya existe un cliente con este email o teléfono, lo reutilizaremos.</p>
        </div>
        <?php endif; ?>

        <!-- Existing customer select -->
        <div x-show="mode==='existing'" x-cloak>
          <label class="block text-sm font-medium mb-1.5">Selecciona cliente</label>
          <select name="customer_id" class="fld">
            <option value="">— Selecciona —</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= e(trim($c['first_name'].' '.($c['last_name'] ?? ''))) ?><?php if ($c['document_number']): ?> · <?= e($c['document_number']) ?><?php endif; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="flex gap-2 pt-2">
          <button type="submit" class="k-btn k-btn-grad flex-1">Asignar cliente</button>
          <button type="button" onclick="document.getElementById('assignModal').classList.add('hidden')" class="k-btn k-btn-outline">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
