<?php
$stBadge=['draft'=>'bg-slate-100 text-slate-600','issued'=>'bg-indigo-100 text-indigo-700','paid'=>'bg-emerald-100 text-emerald-700','void'=>'bg-red-100 text-brand'];
$stLabel=['draft'=>'Borrador','issued'=>'Emitida','paid'=>'Pagada','void'=>'Anulada'];
?>
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Facturas</h1>
    <p class="text-sm text-slate-500"><?= count($invoices) ?> facturas · <?= money($monthTotal) ?> este mes</p>
  </div>
  <?php if (can('invoices.create')): ?><a href="<?= url('/admin/invoices/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nueva factura</a><?php endif; ?>
</div>

<form method="GET" class="card p-4 mb-5 flex gap-3 items-end">
  <div class="min-w-[160px]"><label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !h-10"><option value="">Todas</option><?php foreach ($stLabel as $k=>$lbl): ?><option value="<?= $k ?>" <?= $filters['status']===$k?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?></select>
  </div>
  <button class="k-btn k-btn-dark !h-10">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-paper"><tr><th class="px-6 py-3 font-medium">Número</th><th class="px-6 py-3 font-medium">Cliente</th><th class="px-6 py-3 font-medium">Emitida</th><th class="px-6 py-3 font-medium">Vence</th><th class="px-6 py-3 font-medium">Total</th><th class="px-6 py-3 font-medium">Estado</th></tr></thead>
      <tbody class="divide-y hairline">
        <?php foreach ($invoices as $i): ?>
        <tr class="hover:bg-paper">
          <td class="px-6 py-3 font-mono text-xs font-medium"><a href="<?= url('/admin/invoices/show/'.$i['id']) ?>" class="text-brand hover:underline"><?= e($i['invoice_number']) ?></a></td>
          <td class="px-6 py-3"><?= e($i['customer_name'] ?? '-') ?></td>
          <td class="px-6 py-3 text-slate-500"><?= format_date($i['issue_date']) ?></td>
          <td class="px-6 py-3 text-slate-500"><?= $i['due_date'] ? format_date($i['due_date']) : '-' ?></td>
          <td class="px-6 py-3 font-semibold text-navy tnum"><?= money($i['total']) ?></td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $stBadge[$i['status']]??'' ?>"><?= $stLabel[$i['status']]??$i['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($invoices)): ?><tr><td colspan="6" class="px-6 py-12 text-center text-slate-400"><i data-lucide="receipt" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>No hay facturas</p></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
