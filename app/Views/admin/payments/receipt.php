<?php $t=$p['tenant']; $cu=$p['customer']; $methods=['cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta','paypal'=>'PayPal','stripe'=>'Stripe','azul'=>'Azul','cardnet'=>'CardNet','other'=>'Otro']; ?>
<div class="p-10 text-[13px] text-slate-700">
  <div class="flex items-start justify-between pb-6 border-b-2 border-slate-900">
    <div class="flex items-center gap-2.5">
      <div class="w-10 h-10 rounded-lg grid place-items-center text-white font-black" style="background:<?= e($t['primary_color']??'#F23645') ?>"><?= e(mb_substr($t['name'],0,1)) ?></div>
      <div><p class="font-bold text-lg text-slate-900"><?= e($t['name']) ?></p><p class="text-xs text-slate-500"><?= e($t['phone'] ?? '') ?> · <?= e($t['email'] ?? '') ?></p></div>
    </div>
    <div class="text-right">
      <p class="text-xs uppercase tracking-wider text-slate-400">Recibo de pago</p>
      <p class="font-bold text-xl text-slate-900 tnum"><?= e($p['payment_code']) ?></p>
      <p class="text-xs text-slate-500 mt-1"><?= format_date($p['payment_date']) ?></p>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-6 mt-6 text-xs">
    <div><p class="uppercase tracking-wider text-slate-400 mb-1">Recibido de</p><p class="font-semibold text-slate-900 text-sm"><?= e($cu ? trim($cu['first_name'].' '.$cu['last_name']) : 'Cliente') ?></p><p class="text-slate-500"><?= e($cu['phone'] ?? '') ?></p></div>
    <div class="text-right"><p class="uppercase tracking-wider text-slate-400 mb-1">Método</p><p class="font-semibold text-slate-900 text-sm"><?= $methods[$p['method']] ?? $p['method'] ?></p><?php if($p['reference']): ?><p class="text-slate-500">Ref: <?= e($p['reference']) ?></p><?php endif; ?></div>
  </div>

  <?php if (!empty($p['contract'])): ?>
  <p class="mt-6 text-xs text-slate-500">Aplicado al contrato <b class="text-slate-900 tnum"><?= e($p['contract']['contract_number']) ?></b></p>
  <?php endif; ?>

  <div class="mt-6 rounded-xl bg-slate-50 border border-slate-200 p-6 flex items-center justify-between">
    <span class="text-sm text-slate-500">Monto recibido</span>
    <span class="text-3xl font-extrabold text-slate-900 tnum"><?= money($p['amount']) ?></span>
  </div>

  <?php if (!empty($p['contract'])): ?>
  <div class="mt-4 flex justify-end"><table class="w-64 text-sm"><tbody>
    <tr><td class="py-1 text-slate-500">Total contrato</td><td class="py-1 text-right tnum"><?= money($p['contract']['total_amount']) ?></td></tr>
    <tr><td class="py-1 text-emerald-600">Pagado acumulado</td><td class="py-1 text-right text-emerald-600 tnum"><?= money($p['contract']['paid_amount']) ?></td></tr>
    <tr class="border-t border-slate-300"><td class="py-1.5 font-semibold">Balance restante</td><td class="py-1.5 text-right font-bold tnum"><?= money($p['contract']['balance_due']) ?></td></tr>
  </tbody></table></div>
  <?php endif; ?>

  <?php if (!empty($p['notes'])): ?><p class="mt-6 text-xs text-slate-500">Notas: <?= e($p['notes']) ?></p><?php endif; ?>
  <div class="grid grid-cols-2 gap-12 mt-16">
    <div class="text-center"><div class="border-t border-slate-400 pt-2 text-xs text-slate-500">Recibido por</div></div>
    <div class="text-center"><div class="border-t border-slate-400 pt-2 text-xs text-slate-500">Cliente</div></div>
  </div>
  <p class="text-center text-[10px] text-slate-300 mt-10">Gracias por su pago · <?= e($t['name']) ?></p>
</div>
