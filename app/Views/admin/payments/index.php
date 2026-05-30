<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
  <div>
    <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Pagos</h1>
    <p class="text-sm text-slate-500"><?= count($payments) ?> registros</p>
  </div>
  <?php if (can('payments.create')): ?><a href="<?= url('/admin/payments/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Registrar pago</a><?php endif; ?>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-5">
  <div class="bg-gradient-to-br from-emerald-500 to-teal-500 text-white rounded-2xl p-5 shadow-lg">
    <p class="text-sm text-white/85">Ingresos del mes</p>
    <p class="text-2xl sm:text-3xl font-extrabold mt-1 tnum"><?= money($incomeMonth) ?></p>
  </div>
  <div class="card p-5">
    <p class="text-sm text-slate-500">Pagos pendientes</p>
    <p class="text-2xl sm:text-3xl font-bold text-amber-600 mt-1 tnum"><?= $pending ?></p>
  </div>
  <div class="card p-5 hidden lg:block">
    <p class="text-sm text-slate-500">Total registrados</p>
    <p class="text-2xl sm:text-3xl font-bold text-navy dark:text-white mt-1 tnum"><?= count($payments) ?></p>
  </div>
</div>

<form method="GET" class="card p-3 sm:p-4 mb-5 flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-end">
  <div class="flex-1 min-w-[160px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !h-10 !text-[13px]">
      <option value="">Todos</option>
      <?php foreach (['pending','paid','partial','refunded','voided','void'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status']===$s)?'selected':'' ?>><?= status_label($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="k-btn k-btn-dark !h-10 self-end">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto sm:overflow-x-visible">
    <table class="k-table">
      <thead>
        <tr>
          <th>Código</th><th>Cliente</th><th>Monto</th><th>Método</th>
          <th>Fecha</th><th>Estado</th><th class="text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php $methods=['cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta','paypal'=>'PayPal','stripe'=>'Stripe','azul'=>'Azul','cardnet'=>'CardNet','other'=>'Otro']; ?>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td data-label="Código" class="k-td-primary">
            <a href="<?= url('/admin/payments/receipt/'.$p['id']) ?>" target="_blank" class="font-mono text-xs font-semibold text-brand hover:underline"><?= e($p['payment_code']) ?></a>
          </td>
          <td data-label="Cliente"><span class="text-navy dark:text-white truncate"><?= e($p['customer_name'] ?? '—') ?></span></td>
          <td data-label="Monto" class="font-semibold text-navy dark:text-white tnum"><?= money($p['amount']) ?></td>
          <td data-label="Método" class="text-slate-500"><?= $methods[$p['method']] ?? $p['method'] ?></td>
          <td data-label="Fecha" class="text-slate-500 tnum"><?= format_date($p['payment_date']) ?></td>
          <td data-label="Estado"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($p['status']) ?>"><?= status_label($p['status']) ?></span></td>
          <td class="k-td-actions text-right whitespace-nowrap">
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
        <tr><td colspan="7" class="text-center text-slate-400 py-12">
          <i data-lucide="credit-card" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
          <p>No hay pagos</p>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
