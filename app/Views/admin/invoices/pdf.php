<?php $t=$inv['tenant']; $cu=$inv['customer']; ?>
<div class="p-10 text-[13px] text-slate-700">
  <div class="flex items-start justify-between pb-6 border-b-2 border-slate-900">
    <div class="flex items-center gap-2.5">
      <div class="w-10 h-10 rounded-lg grid place-items-center text-white font-black" style="background:<?= e($t['primary_color']??'#F23645') ?>"><?= e(mb_substr($t['name'],0,1)) ?></div>
      <div><p class="font-bold text-lg text-slate-900"><?= e($t['name']) ?></p><p class="text-xs text-slate-500"><?= e($t['legal_name'] ?? '') ?> <?= $t['rnc']?'· RNC '.e($t['rnc']):'' ?></p></div>
    </div>
    <div class="text-right">
      <p class="text-xs uppercase tracking-wider text-slate-400">Factura</p>
      <p class="font-bold text-xl text-slate-900 tnum"><?= e($inv['invoice_number']) ?></p>
      <p class="text-xs text-slate-500 mt-1">Emisión: <?= format_date($inv['issue_date']) ?><?= $inv['due_date']?' · Vence: '.format_date($inv['due_date']):'' ?></p>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-6 mt-6 text-xs">
    <div><p class="uppercase tracking-wider text-slate-400 mb-1">Facturar a</p><p class="font-semibold text-slate-900 text-sm"><?= e($cu ? trim($cu['first_name'].' '.$cu['last_name']) : 'Consumidor final') ?></p><p class="text-slate-500"><?= e($cu['document_number'] ?? '') ?><?= !empty($cu['email'])?' · '.e($cu['email']):'' ?></p></div>
    <div class="text-right"><p class="uppercase tracking-wider text-slate-400 mb-1">Emisor</p><p class="text-slate-500"><?= e($t['address'] ?? '') ?><br><?= e($t['phone'] ?? '') ?></p></div>
  </div>

  <table class="w-full mt-6 text-sm border border-slate-200 rounded overflow-hidden">
    <thead class="bg-slate-50 text-slate-500 text-xs"><tr><th class="text-left px-3 py-2 font-semibold">Concepto</th><th class="text-right px-3 py-2 font-semibold">Cant.</th><th class="text-right px-3 py-2 font-semibold">Precio</th><th class="text-right px-3 py-2 font-semibold">Total</th></tr></thead>
    <tbody>
      <?php foreach ($inv['items'] as $it): ?>
      <tr class="border-t border-slate-200"><td class="px-3 py-2"><?= e($it['description']) ?></td><td class="px-3 py-2 text-right tnum"><?= rtrim(rtrim(number_format($it['quantity'],2),'0'),'.') ?></td><td class="px-3 py-2 text-right tnum"><?= money($it['unit_price']) ?></td><td class="px-3 py-2 text-right tnum font-medium"><?= money($it['line_total']) ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="mt-5 flex justify-end">
    <table class="w-64 text-sm">
      <tbody>
        <tr><td class="py-1.5 text-slate-500">Subtotal</td><td class="py-1.5 text-right tnum"><?= money($inv['subtotal']) ?></td></tr>
        <?php if ($inv['discount_amount']>0): ?><tr><td class="py-1.5 text-slate-500">Descuento</td><td class="py-1.5 text-right tnum">-<?= money($inv['discount_amount']) ?></td></tr><?php endif; ?>
        <tr><td class="py-1.5 text-slate-500">Impuesto</td><td class="py-1.5 text-right tnum"><?= money($inv['tax_amount']) ?></td></tr>
        <tr class="border-t border-slate-300"><td class="py-2 font-bold text-slate-900">Total</td><td class="py-2 text-right font-bold text-slate-900 tnum"><?= money($inv['total']) ?></td></tr>
      </tbody>
    </table>
  </div>

  <p class="text-center text-[10px] text-slate-300 mt-12">Gracias por su preferencia · <?= e($t['name']) ?> · Generado por Kyros Rent Car</p>
</div>
