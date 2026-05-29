<?php $v=$c['vehicle']; $cu=$c['customer']; $custName=trim($cu['first_name'].' '.$cu['last_name']); ?>
<div class="max-w-5xl mx-auto">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
      <div class="flex items-center gap-3">
        <h1 class="font-display text-2xl font-bold text-navy"><?= e($c['contract_number']) ?></h1>
        <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($c['status']) ?>"><?= status_label($c['status']) ?></span>
      </div>
      <p class="text-sm text-slate-500 mt-1"><?= e($custName) ?> · <?= e($v['brand'].' '.$v['model']) ?></p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="<?= url('/admin/contracts/pdf/'.$c['id']) ?>" target="_blank" class="k-btn k-btn-outline"><i data-lucide="printer" class="w-4 h-4"></i> Imprimir / PDF</a>
      <?php if (can('invoices.create')): ?><a href="<?= url('/admin/invoices/create?contract_id='.$c['id']) ?>" class="k-btn k-btn-outline"><i data-lucide="receipt" class="w-4 h-4"></i> Facturar</a><?php endif; ?>
      <?php if (can('payments.create') && $c['balance_due'] > 0): ?>
        <a href="<?= url('/admin/payments/create?contract_id='.$c['id']) ?>" class="k-btn k-btn-dark"><i data-lucide="credit-card" class="w-4 h-4"></i> Registrar pago</a>
      <?php endif; ?>
      <?php if (can('contracts.edit') && $c['status'] !== 'finished'): ?>
        <a href="<?= url('/admin/contracts/close/'.$c['id']) ?>" class="k-btn k-btn-grad"><i data-lucide="check-check" class="w-4 h-4"></i> Cerrar / Devolución</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
      <div class="card p-6">
        <h2 class="font-semibold text-navy mb-4">Información del contrato</h2>
        <div class="grid sm:grid-cols-2 gap-4 text-sm">
          <div><p class="text-slate-400">Cliente</p><p class="font-medium text-navy"><?= e($custName) ?></p><p class="text-slate-400 text-xs"><?= e($cu['phone'] ?? '') ?> <?= e($cu['document_number'] ?? '') ?></p></div>
          <div><p class="text-slate-400">Vehículo</p><p class="font-medium text-navy"><?= e($v['brand'].' '.$v['model']) ?></p><p class="text-slate-400 text-xs"><?= e($v['plate_number'] ?? '') ?> · <?= e($v['year']) ?></p></div>
          <div><p class="text-slate-400">Inicio</p><p class="font-medium text-navy"><?= format_datetime($c['start_datetime']) ?></p></div>
          <div><p class="text-slate-400">Fin previsto</p><p class="font-medium text-navy"><?= format_datetime($c['end_datetime']) ?></p></div>
          <div><p class="text-slate-400">Km salida</p><p class="font-medium text-navy tnum"><?= $c['start_mileage']!==null?number_format((int)$c['start_mileage']):'-' ?></p></div>
          <div><p class="text-slate-400">Combustible salida</p><p class="font-medium text-navy tnum"><?= $c['start_fuel_level']!==null?$c['start_fuel_level'].'%':'-' ?></p></div>
          <?php if ($c['actual_return_datetime']): ?>
          <div><p class="text-slate-400">Devolución real</p><p class="font-medium text-navy"><?= format_datetime($c['actual_return_datetime']) ?></p></div>
          <div><p class="text-slate-400">Km llegada</p><p class="font-medium text-navy tnum"><?= $c['end_mileage']!==null?number_format((int)$c['end_mileage']):'-' ?></p></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b hairline flex items-center justify-between">
          <h2 class="font-semibold text-navy">Pagos</h2>
          <?php if (can('payments.create') && $c['balance_due'] > 0): ?><a href="<?= url('/admin/payments/create?contract_id='.$c['id']) ?>" class="text-xs font-semibold text-brand hover:underline">+ Registrar</a><?php endif; ?>
        </div>
        <table class="w-full text-sm">
          <tbody class="divide-y hairline">
            <?php foreach ($c['payments'] as $p): ?>
            <tr class="hover:bg-paper">
              <td class="px-6 py-3 font-mono text-xs text-brand"><?= e($p['payment_code']) ?></td>
              <td class="px-6 py-3 text-slate-500"><?= format_date($p['payment_date']) ?></td>
              <td class="px-6 py-3 text-slate-500"><?= ucfirst($p['method']) ?></td>
              <td class="px-6 py-3 font-semibold text-navy tnum"><?= money($p['amount']) ?></td>
              <td class="px-6 py-3 text-right"><a href="<?= url('/admin/payments/receipt/'.$p['id']) ?>" target="_blank" class="text-xs font-medium text-slate-400 hover:text-brand">Recibo</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($c['payments'])): ?><tr><td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">Sin pagos registrados</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card p-6 h-fit">
      <h2 class="font-semibold text-navy mb-4">Balance</h2>
      <div class="space-y-2.5 text-sm">
        <div class="flex justify-between text-slate-500"><span>Subtotal</span><span class="font-medium text-navy tnum"><?= money($c['subtotal']) ?></span></div>
        <?php if ($c['extras_total']>0): ?><div class="flex justify-between text-slate-500"><span>Extras</span><span class="font-medium text-navy tnum"><?= money($c['extras_total']) ?></span></div><?php endif; ?>
        <?php if ($c['penalties_total']>0): ?><div class="flex justify-between text-slate-500"><span>Penalidades</span><span class="font-medium text-brand tnum"><?= money($c['penalties_total']) ?></span></div><?php endif; ?>
        <div class="flex justify-between text-slate-500"><span>Impuesto</span><span class="font-medium text-navy tnum"><?= money($c['tax_amount']) ?></span></div>
        <div class="flex justify-between pt-2 border-t hairline"><span class="font-semibold text-navy">Total</span><span class="font-bold text-navy tnum"><?= money($c['total_amount']) ?></span></div>
        <div class="flex justify-between text-emerald-600"><span>Pagado</span><span class="font-semibold tnum"><?= money($c['paid_amount']) ?></span></div>
        <div class="flex justify-between pt-2 border-t hairline text-base"><span class="font-bold text-navy">Balance</span><span class="font-extrabold tnum <?= $c['balance_due']>0?'text-brand':'text-emerald-600' ?>"><?= money($c['balance_due']) ?></span></div>
      </div>
      <div class="mt-4 p-3 rounded-xl bg-paper text-xs text-slate-500">Depósito retenido: <span class="font-semibold text-navy tnum"><?= money($c['deposit_amount']) ?></span></div>
    </div>
  </div>

  <!-- Signature + photos -->
  <div class="grid lg:grid-cols-3 gap-5 mt-5">
    <!-- Signature -->
    <div class="card p-6" x-data="sigPad()">
      <div class="flex items-center justify-between mb-3"><h2 class="font-display font-bold text-navy">Firma del cliente</h2><?php if($c['customer_signature']): ?><span class="text-xs text-emerald-600 font-semibold flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> Firmado</span><?php endif; ?></div>
      <?php if ($c['customer_signature']): ?>
        <div class="rounded-xl border hairline bg-white p-3"><img src="<?= e($c['customer_signature']) ?>" class="h-28 mx-auto object-contain"></div>
        <?php if (can('contracts.edit')): ?><button type="button" @click="open=true; $nextTick(()=>setup())" class="k-btn k-btn-ghost w-full mt-3 !h-9">Volver a firmar</button><?php endif; ?>
      <?php elseif (can('contracts.edit')): ?>
        <button type="button" @click="open=true; $nextTick(()=>setup())" class="w-full border-2 border-dashed hairline rounded-xl py-8 text-sm text-slate-400 hover:border-brand/40 hover:text-brand transition flex flex-col items-center gap-2"><i data-lucide="pen-line" class="w-6 h-6"></i> Capturar firma</button>
      <?php else: ?><p class="text-sm text-slate-400 py-6 text-center">Sin firma</p><?php endif; ?>

      <!-- Signature modal -->
      <div x-show="open" x-cloak class="fixed inset-0 z-50 grid place-items-center p-4">
        <div class="absolute inset-0 bg-navy/40 backdrop-blur-sm" @click="open=false"></div>
        <div class="relative card p-5 w-full max-w-md">
          <h3 class="font-display font-bold text-navy mb-1">Firma del cliente</h3>
          <p class="text-xs text-slate-400 mb-3">Pide al cliente que firme dentro del recuadro.</p>
          <form method="POST" action="<?= url('/admin/contracts/sign/'.$c['id']) ?>" @submit="prepare($event)">
            <?= csrf_field() ?>
            <input type="hidden" name="signature" x-ref="data">
            <canvas x-ref="canvas" width="380" height="180" class="w-full rounded-xl border-2 hairline bg-white touch-none cursor-crosshair"></canvas>
            <div class="flex gap-2 mt-3">
              <button type="button" @click="clear()" class="k-btn k-btn-outline !h-9">Limpiar</button>
              <button type="submit" class="k-btn k-btn-grad !h-9 flex-1">Guardar firma</button>
              <button type="button" @click="open=false" class="k-btn k-btn-ghost !h-9">Cerrar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delivery photos -->
    <div class="card p-6">
      <h2 class="font-display font-bold text-navy mb-3">Fotos de entrega</h2>
      <?php if (!empty($c['photos_delivery'])): ?>
        <div class="grid grid-cols-3 gap-2">
          <?php foreach ($c['photos_delivery'] as $ph): ?><a href="<?= e($ph['path']) ?>" target="_blank" class="aspect-square rounded-lg overflow-hidden bg-paper"><img src="<?= e($ph['path']) ?>" class="w-full h-full object-cover hover:scale-105 transition"></a><?php endforeach; ?>
        </div>
      <?php else: ?><p class="text-sm text-slate-400 py-6 text-center">Sin fotos de entrega</p><?php endif; ?>
    </div>

    <!-- Return photos -->
    <div class="card p-6">
      <h2 class="font-display font-bold text-navy mb-3">Fotos de devolución</h2>
      <?php if (!empty($c['photos_return'])): ?>
        <div class="grid grid-cols-3 gap-2">
          <?php foreach ($c['photos_return'] as $ph): ?><a href="<?= e($ph['path']) ?>" target="_blank" class="aspect-square rounded-lg overflow-hidden bg-paper"><img src="<?= e($ph['path']) ?>" class="w-full h-full object-cover hover:scale-105 transition"></a><?php endforeach; ?>
        </div>
      <?php else: ?><p class="text-sm text-slate-400 py-6 text-center">Sin fotos de devolución</p><?php endif; ?>
    </div>
  </div>
</div>

<?php \App\Core\View::push('scripts', '<script>
function sigPad(){
  return {
    open:false, drawing:false, ctx:null, last:null,
    init(){},
    setup(){ var c=this.$refs.canvas; if(!c||this.ctx) return; this.ctx=c.getContext("2d"); this.ctx.lineWidth=2.2; this.ctx.lineCap="round"; this.ctx.strokeStyle="#1c2433";
      var pos=function(e){ var r=c.getBoundingClientRect(); var t=e.touches?e.touches[0]:e; return {x:(t.clientX-r.left)*(c.width/r.width), y:(t.clientY-r.top)*(c.height/r.height)}; };
      var start=function(e){ e.preventDefault(); this.drawing=true; this.last=pos(e); }.bind(this);
      var move=function(e){ if(!this.drawing) return; e.preventDefault(); var p=pos(e); this.ctx.beginPath(); this.ctx.moveTo(this.last.x,this.last.y); this.ctx.lineTo(p.x,p.y); this.ctx.stroke(); this.last=p; }.bind(this);
      var end=function(){ this.drawing=false; }.bind(this);
      c.addEventListener("mousedown",start); c.addEventListener("mousemove",move); window.addEventListener("mouseup",end);
      c.addEventListener("touchstart",start,{passive:false}); c.addEventListener("touchmove",move,{passive:false}); c.addEventListener("touchend",end);
    },
    clear(){ this.setup(); var c=this.$refs.canvas; this.ctx.clearRect(0,0,c.width,c.height); },
    prepare(e){ this.setup(); this.$refs.data.value=this.$refs.canvas.toDataURL("image/png"); }
  }
}
document.addEventListener("alpine:init",()=>{});
</script>'); ?>
