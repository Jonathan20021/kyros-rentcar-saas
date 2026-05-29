<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Pagos</h1>
    <p class="text-sm text-slate-500"><?= count($payments) ?> registros</p>
  </div>
  <?php if (can('payments.create')): ?><a href="<?= url('/admin/payments/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Registrar pago</a><?php endif; ?>
</div>

<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
  <div class="bg-gradient-to-br from-emerald-500 to-teal-500 text-white rounded-2xl p-5 shadow-lg">
    <p class="text-sm text-white/80">Ingresos del mes</p><p class="text-2xl font-extrabold mt-1"><?= money($incomeMonth) ?></p>
  </div>
  <div class="card p-5">
    <p class="text-sm text-slate-500">Pagos pendientes</p><p class="text-2xl font-bold text-amber-600 mt-1"><?= $pending ?></p>
  </div>
</div>

<form method="GET" class="card p-4 mb-5 flex gap-3 items-end">
  <div class="min-w-[160px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !py-2 !text-[13px]">
      <option value="">Todos</option>
      <?php foreach (['pending','paid','partial','refunded','voided'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status']===$s)?'selected':'' ?>><?= status_label($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="k-btn k-btn-dark !py-2">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-slate-50 dark:bg-slate-800/50">
        <tr><th class="px-6 py-3 font-medium">Código</th><th class="px-6 py-3 font-medium">Cliente</th><th class="px-6 py-3 font-medium">Monto</th><th class="px-6 py-3 font-medium">Método</th><th class="px-6 py-3 font-medium">Fecha</th><th class="px-6 py-3 font-medium">Estado</th><th class="px-6 py-3 font-medium text-right">Acciones</th></tr>
      </thead>
      <tbody class="divide-y divide-[#EAECEF] dark:divide-slate-800">
        <?php $methods=['cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta','paypal'=>'PayPal','stripe'=>'Stripe','azul'=>'Azul','cardnet'=>'CardNet','other'=>'Otro']; ?>
        <?php foreach ($payments as $p): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="px-6 py-3 font-mono text-xs font-medium text-brand"><?= e($p['payment_code']) ?></td>
          <td class="px-6 py-3"><?= e($p['customer_name'] ?? '—') ?></td>
          <td class="px-6 py-3 font-semibold tnum"><?= money($p['amount']) ?></td>
          <td class="px-6 py-3 text-slate-500"><?= $methods[$p['method']] ?? $p['method'] ?></td>
          <td class="px-6 py-3 text-slate-500 tnum"><?= format_date($p['payment_date']) ?></td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($p['status']) ?>"><?= status_label($p['status']) ?></span></td>
          <td class="px-6 py-3 text-right whitespace-nowrap">
            <a href="<?= url('/admin/payments/receipt/'.$p['id']) ?>" target="_blank" class="p-1.5 inline-grid rounded-lg hover:bg-paper dark:hover:bg-slate-800 text-slate-400 hover:text-navy dark:hover:text-white" title="Recibo">
              <i data-lucide="receipt" class="w-4 h-4"></i>
            </a>
            <?php if ($p['status'] === 'paid' && can('payments.edit')): ?>
              <form method="POST" action="<?= url('/admin/payments/void/'.$p['id']) ?>" class="inline"
                    data-confirm="El pago se anulará y el balance del contrato se recalculará."
                    data-confirm-title="¿Anular pago <?= e($p['payment_code']) ?>?"
                    data-confirm-label="Sí, anular" data-confirm-variant="warning">
                <?= csrf_field() ?>
                <button class="p-1.5 inline-grid rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-slate-400 hover:text-red-600" title="Anular pago">
                  <i data-lucide="x-circle" class="w-4 h-4"></i>
                </button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($payments)): ?>
        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><i data-lucide="credit-card" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>No hay pagos</p></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
