<?php
$stBadge=['draft'=>'bg-slate-100 text-slate-600','issued'=>'bg-indigo-100 text-indigo-700','paid'=>'bg-emerald-100 text-emerald-700','void'=>'bg-red-100 text-brand'];
$stLabel=['draft'=>'Borrador','issued'=>'Emitida','paid'=>'Pagada','void'=>'Anulada'];
$cu=$inv['customer'];
?>
<div class="max-w-4xl mx-auto">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
      <div class="flex items-center gap-3"><h1 class="font-display text-2xl font-bold text-navy"><?= e($inv['invoice_number']) ?></h1><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $stBadge[$inv['status']]??'' ?>"><?= $stLabel[$inv['status']]??$inv['status'] ?></span></div>
      <p class="text-sm text-slate-500 mt-1"><?= e($cu ? trim($cu['first_name'].' '.$cu['last_name']) : 'Sin cliente') ?> · emitida <?= format_date($inv['issue_date']) ?></p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="<?= url('/admin/invoices/pdf/'.$inv['id']) ?>" target="_blank" class="k-btn k-btn-outline"><i data-lucide="printer" class="w-4 h-4"></i> Imprimir / PDF</a>
      <?php if (can('invoices.edit') && $inv['status']!=='paid' && $inv['status']!=='void'): ?>
        <form method="POST" action="<?= url('/admin/invoices/status/'.$inv['id']) ?>"><?= csrf_field() ?><input type="hidden" name="status" value="paid"><button class="k-btn k-btn-grad"><i data-lucide="check" class="w-4 h-4"></i> Marcar pagada</button></form>
        <form method="POST" action="<?= url('/admin/invoices/status/'.$inv['id']) ?>" data-confirm="Esta acción cambia el estado de la factura a anulada." data-confirm-title="¿Anular factura?" data-confirm-label="Sí, anular"><?= csrf_field() ?><input type="hidden" name="status" value="void"><button class="k-btn k-btn-ghost">Anular</button></form>
      <?php endif; ?>
    </div>
  </div>

  <div class="card overflow-hidden">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-paper"><tr><th class="px-6 py-3 font-medium">Concepto</th><th class="px-6 py-3 font-medium text-right">Cant.</th><th class="px-6 py-3 font-medium text-right">Precio</th><th class="px-6 py-3 font-medium text-right">Total</th></tr></thead>
      <tbody class="divide-y hairline">
        <?php foreach ($inv['items'] as $it): ?>
        <tr><td class="px-6 py-3 text-navy"><?= e($it['description']) ?></td><td class="px-6 py-3 text-right text-slate-500 tnum"><?= rtrim(rtrim(number_format($it['quantity'],2),'0'),'.') ?></td><td class="px-6 py-3 text-right text-slate-500 tnum"><?= money($it['unit_price']) ?></td><td class="px-6 py-3 text-right font-semibold text-navy tnum"><?= money($it['line_total']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="flex justify-end p-6 border-t hairline">
      <div class="w-full sm:w-72 space-y-2 text-sm">
        <div class="flex justify-between text-slate-500"><span>Subtotal</span><span class="font-medium text-navy tnum"><?= money($inv['subtotal']) ?></span></div>
        <?php if ($inv['discount_amount']>0): ?><div class="flex justify-between text-slate-500"><span>Descuento</span><span class="text-brand tnum">-<?= money($inv['discount_amount']) ?></span></div><?php endif; ?>
        <div class="flex justify-between text-slate-500"><span>Impuesto</span><span class="font-medium text-navy tnum"><?= money($inv['tax_amount']) ?></span></div>
        <div class="flex justify-between pt-2 border-t hairline text-base"><span class="font-bold text-navy">Total</span><span class="font-extrabold text-navy tnum"><?= money($inv['total']) ?></span></div>
      </div>
    </div>
  </div>
</div>
