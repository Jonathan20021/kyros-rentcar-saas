<?php
$v=$c['vehicle']; $cu=$c['customer']; $custName=trim($cu['first_name'].' '.$cu['last_name']);
$shareUrl  = !empty($c['share_token']) ? abs_url('/contrato/' . $c['share_token']) : null;
$openShare = isset($_GET['share']) && $shareUrl;
?>
<div class="max-w-5xl mx-auto" x-data="{shareOpen: <?= $openShare ? 'true' : 'false' ?>, copied:false}">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
      <div class="flex items-center gap-3 flex-wrap">
        <h1 class="font-display text-2xl font-bold text-navy dark:text-white"><?= e($c['contract_number']) ?></h1>
        <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($c['status']) ?>"><?= status_label($c['status']) ?></span>
        <?php if (!empty($c['signed_at'])): ?>
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 ring-1 ring-emerald-500/20">
            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i> Firmado por el cliente
          </span>
        <?php endif; ?>
      </div>
      <p class="text-sm text-slate-500 mt-1"><?= e($custName) ?> · <?= e($v['brand'].' '.$v['model']) ?></p>
    </div>
    <div class="flex flex-wrap gap-2">
      <?php if (can('contracts.edit')): ?>
        <?php if ($shareUrl): ?>
          <button type="button" @click="shareOpen=true" class="k-btn k-btn-outline">
            <i data-lucide="link" class="w-4 h-4"></i> Enlace de firma
          </button>
        <?php else: ?>
          <form method="POST" action="<?= url('/admin/contracts/share/'.$c['id']) ?>" class="inline">
            <?= csrf_field() ?>
            <button type="submit" class="k-btn k-btn-outline">
              <i data-lucide="link" class="w-4 h-4"></i> Generar enlace
            </button>
          </form>
        <?php endif; ?>
      <?php endif; ?>
      <a href="<?= url('/admin/contracts/pdf/'.$c['id']) ?>" target="_blank" class="k-btn k-btn-outline"><i data-lucide="file-down" class="w-4 h-4"></i> Descargar PDF</a>
      <?php if (can('invoices.create')): ?><a href="<?= url('/admin/invoices/create?contract_id='.$c['id']) ?>" class="k-btn k-btn-outline"><i data-lucide="receipt" class="w-4 h-4"></i> Facturar</a><?php endif; ?>
      <?php if (can('payments.create') && $c['balance_due'] > 0): ?>
        <a href="<?= url('/admin/payments/create?contract_id='.$c['id']) ?>" class="k-btn k-btn-dark"><i data-lucide="credit-card" class="w-4 h-4"></i> Registrar pago</a>
      <?php endif; ?>
      <?php if (can('contracts.edit') && $c['status'] !== 'finished'): ?>
        <a href="<?= url('/admin/contracts/close/'.$c['id']) ?>" class="k-btn k-btn-grad"><i data-lucide="check-check" class="w-4 h-4"></i> Cerrar / Devolución</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- =================== SHARE MODAL =================== -->
  <?php if ($shareUrl):
    $message = sprintf(
      "Hola %s, te compartimos tu contrato de alquiler %s (%s) — puedes revisarlo y firmarlo aquí:\n\n%s",
      $custName, $c['contract_number'], $v['brand'].' '.$v['model'], $shareUrl
    );
    $waMessage = rawurlencode($message);
    $waNumber  = preg_replace('/[^0-9]/', '', $cu['whatsapp'] ?? $cu['phone'] ?? '');
    $emailSubject = rawurlencode('Tu contrato de alquiler ' . $c['contract_number']);
    $emailBody    = rawurlencode($message);
    $emailTo      = $cu['email'] ?? '';
  ?>
  <div x-show="shareOpen" x-cloak x-transition.opacity.duration.150ms class="fixed inset-0 z-[70] bg-ink/40 backdrop-blur-sm" @click="shareOpen=false"></div>
  <div x-show="shareOpen" x-cloak x-transition.duration.150ms
       class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-[80] w-[94%] max-w-lg bg-white dark:bg-slate-900 rounded-3xl shadow-lift border hairline overflow-hidden">
    <div class="p-6">
      <div class="flex items-start gap-3 mb-5">
        <div class="w-11 h-11 rounded-2xl bg-brand/10 text-brand grid place-items-center shrink-0">
          <i data-lucide="link" class="w-5 h-5"></i>
        </div>
        <div class="min-w-0">
          <h3 class="font-display font-bold text-navy dark:text-white text-lg">Enlace para el cliente</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Envíalo por WhatsApp o email. El cliente podrá ver y firmar el contrato desde su teléfono.</p>
        </div>
      </div>

      <div class="rounded-xl bg-paper dark:bg-slate-800/40 p-3 flex items-center gap-2 mb-4">
        <i data-lucide="link-2" class="w-4 h-4 text-slate-400 shrink-0 ml-1"></i>
        <input readonly value="<?= e($shareUrl) ?>"
               class="flex-1 bg-transparent outline-none text-[13px] font-mono text-navy dark:text-white truncate" onclick="this.select()">
        <button type="button"
                @click="navigator.clipboard.writeText('<?= e($shareUrl) ?>'); copied=true; setTimeout(()=>copied=false,1800)"
                class="k-btn k-btn-dark !h-9 !px-3 shrink-0">
          <span x-show="!copied" class="flex items-center gap-1"><i data-lucide="copy" class="w-3.5 h-3.5"></i> Copiar</span>
          <span x-show="copied" x-cloak class="text-emerald-300 flex items-center gap-1"><i data-lucide="check" class="w-3.5 h-3.5"></i> Copiado</span>
        </button>
      </div>

      <div class="grid grid-cols-2 gap-2 mb-5">
        <a href="https://wa.me/<?= e($waNumber) ?>?text=<?= $waMessage ?>" target="_blank"
           class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-[#25D366]/10 text-[#1B7841] hover:bg-[#25D366]/15 font-semibold text-sm transition">
          <i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp
        </a>
        <a href="mailto:<?= e($emailTo) ?>?subject=<?= $emailSubject ?>&body=<?= $emailBody ?>"
           class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-semibold text-sm transition">
          <i data-lucide="mail" class="w-4 h-4"></i> Email
        </a>
      </div>

      <?php if (!empty($c['signed_at'])): ?>
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-500/10 p-3 text-[13px] text-emerald-700 dark:text-emerald-300 flex items-center gap-2 mb-4">
          <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
          Firmado el <b><?= e(date('d/m/Y H:i', strtotime($c['signed_at']))) ?></b><?php if (!empty($c['signed_ip'])): ?> · IP <?= e($c['signed_ip']) ?><?php endif; ?>
        </div>
      <?php else: ?>
        <p class="text-[11.5px] text-slate-400 mb-4">El enlace permanece activo hasta que lo revoques. Al firmar el cliente, queda confirmado y no puede modificarse.</p>
      <?php endif; ?>

      <div class="flex items-center justify-between gap-2 pt-4 border-t hairline">
        <form method="POST" action="<?= url('/admin/contracts/share-revoke/'.$c['id']) ?>"
              data-confirm="El enlace actual dejará de funcionar inmediatamente. Tendrás que generar uno nuevo si lo necesitas."
              data-confirm-title="¿Revocar enlace?" data-confirm-label="Sí, revocar" data-confirm-variant="warning">
          <?= csrf_field() ?>
          <button class="k-btn k-btn-ghost !text-red-600 hover:!bg-red-50 !h-9">
            <i data-lucide="link-2-off" class="w-4 h-4"></i> Revocar
          </button>
        </form>
        <button type="button" @click="shareOpen=false" class="k-btn k-btn-outline !h-9">Cerrar</button>
      </div>
    </div>
  </div>
  <?php endif; ?>

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
        <div class="rounded-xl border hairline bg-white p-3"><img src="<?= e(url($c['customer_signature'])) ?>" class="h-28 mx-auto object-contain" alt="Firma del cliente"></div>
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
          <?php foreach ($c['photos_delivery'] as $ph): ?><a href="<?= e(media($ph['path'])) ?>" target="_blank" class="aspect-square rounded-lg overflow-hidden bg-paper"><img src="<?= e(media($ph['path'])) ?>" class="w-full h-full object-cover hover:scale-105 transition"></a><?php endforeach; ?>
        </div>
      <?php else: ?><p class="text-sm text-slate-400 py-6 text-center">Sin fotos de entrega</p><?php endif; ?>
    </div>

    <!-- Return photos -->
    <div class="card p-6">
      <h2 class="font-display font-bold text-navy mb-3">Fotos de devolución</h2>
      <?php if (!empty($c['photos_return'])): ?>
        <div class="grid grid-cols-3 gap-2">
          <?php foreach ($c['photos_return'] as $ph): ?><a href="<?= e(media($ph['path'])) ?>" target="_blank" class="aspect-square rounded-lg overflow-hidden bg-paper"><img src="<?= e(media($ph['path'])) ?>" class="w-full h-full object-cover hover:scale-105 transition"></a><?php endforeach; ?>
        </div>
      <?php else: ?><p class="text-sm text-slate-400 py-6 text-center">Sin fotos de devolución</p><?php endif; ?>
    </div>
  </div>
</div>

<?php \App\Core\View::push('scripts', '<script>
function sigPad(){
  return {
    open:false, drawing:false, ctx:null, strokes:[], current:null,
    init(){},
    setup(){ var c=this.$refs.canvas; if(!c||this.ctx) return; this.ctx=c.getContext("2d"); this.ctx.lineWidth=2.2; this.ctx.lineCap="round"; this.ctx.lineJoin="round"; this.ctx.strokeStyle="#0E1422";
      var self=this;
      var pos=function(e){ var r=c.getBoundingClientRect(); var t=e.touches?e.touches[0]:e; return {x:(t.clientX-r.left)*(c.width/r.width), y:(t.clientY-r.top)*(c.height/r.height)}; };
      var start=function(e){ e.preventDefault(); self.drawing=true; var p=pos(e); self.current=[p]; };
      var move=function(e){ if(!self.drawing) return; e.preventDefault(); var p=pos(e); var last=self.current[self.current.length-1]; self.ctx.beginPath(); self.ctx.moveTo(last.x,last.y); self.ctx.lineTo(p.x,p.y); self.ctx.stroke(); self.current.push(p); };
      var end=function(){ if(self.current && self.current.length) self.strokes.push(self.current); self.drawing=false; self.current=null; };
      c.addEventListener("mousedown",start); c.addEventListener("mousemove",move); window.addEventListener("mouseup",end);
      c.addEventListener("touchstart",start,{passive:false}); c.addEventListener("touchmove",move,{passive:false}); c.addEventListener("touchend",end);
    },
    clear(){ this.setup(); var c=this.$refs.canvas; this.ctx.clearRect(0,0,c.width,c.height); this.strokes=[]; this.current=null; },
    prepare(e){
      this.setup();
      var c=this.$refs.canvas, W=c.width, H=c.height;
      var r=function(n){return Math.round(n*10)/10;};
      var paths=this.strokes.map(function(s){
        if(!s.length) return "";
        if(s.length===1) return "<circle cx=\""+r(s[0].x)+"\" cy=\""+r(s[0].y)+"\" r=\"1.5\" fill=\"#0E1422\"/>";
        var d=s.map(function(p,i){return (i===0?"M":"L")+r(p.x)+" "+r(p.y);}).join(" ");
        return "<path d=\""+d+"\" fill=\"none\" stroke=\"#0E1422\" stroke-width=\"2.2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>";
      }).join("");
      this.$refs.data.value = "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 "+W+" "+H+"\" width=\""+W+"\" height=\""+H+"\"><rect width=\""+W+"\" height=\""+H+"\" fill=\"#FFFFFF\"/>"+paths+"</svg>";
    }
  }
}
document.addEventListener("alpine:init",()=>{});
</script>'); ?>
