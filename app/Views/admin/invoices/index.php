<?php
$stBadge=['draft'=>'bg-slate-100 text-slate-600','issued'=>'bg-indigo-50 text-indigo-700','paid'=>'bg-emerald-50 text-emerald-700','void'=>'bg-red-50 text-brand'];
$stLabel=['draft'=>'Borrador','issued'=>'Emitida','paid'=>'Pagada','void'=>'Anulada'];
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
  <div>
    <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Facturas</h1>
    <p class="text-sm text-slate-500"><?= count($invoices) ?> facturas · <?= money($monthTotal) ?> este mes</p>
  </div>
  <?php if (can('invoices.create')): ?><a href="<?= url('/admin/invoices/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nueva factura</a><?php endif; ?>
</div>

<form method="GET" class="card p-3 sm:p-4 mb-5 flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-end">
  <div class="flex-1 min-w-[160px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !h-10 !text-[13px]">
      <option value="">Todas</option>
      <?php foreach ($stLabel as $k=>$lbl): ?><option value="<?= $k ?>" <?= $filters['status']===$k?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?>
    </select>
  </div>
  <button class="k-btn k-btn-dark !h-10 self-end">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto sm:overflow-x-visible">
    <table class="k-table">
      <thead>
        <tr>
          <th>Número</th><th>Cliente</th><th>Emitida</th><th>Vence</th><th>Total</th><th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invoices as $i): ?>
        <tr>
          <td data-label="Número" class="k-td-primary">
            <a href="<?= url('/admin/invoices/show/'.$i['id']) ?>" class="font-mono text-xs font-semibold text-brand hover:underline"><?= e($i['invoice_number']) ?></a>
          </td>
          <td data-label="Cliente"><span class="text-navy dark:text-white truncate"><?= e($i['customer_name'] ?? '—') ?></span></td>
          <td data-label="Emitida" class="text-slate-500 tnum"><?= format_date($i['issue_date']) ?></td>
          <td data-label="Vence" class="text-slate-500 tnum"><?= $i['due_date'] ? format_date($i['due_date']) : '—' ?></td>
          <td data-label="Total" class="font-semibold text-navy dark:text-white tnum"><?= money($i['total']) ?></td>
          <td data-label="Estado"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $stBadge[$i['status']]??'' ?>"><?= $stLabel[$i['status']]??$i['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($invoices)): ?>
        <tr><td colspan="6" class="text-center text-slate-400 py-12">
          <i data-lucide="receipt" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
          <p>No hay facturas</p>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
