<?php
$initial = $prefill
  ? [['description'=>$prefill['description'],'quantity'=>1,'unit_price'=>$prefill['amount']]]
  : [['description'=>'','quantity'=>1,'unit_price'=>0]];
?>
<div class="max-w-3xl mx-auto" x-data='invForm(<?= json_encode([
  "tax"=>(float)$taxRate, "symbol"=>\App\Core\Config::get("app.currency_symbol","RD$"), "rows"=>$initial
], JSON_UNESCAPED_UNICODE) ?>)'>
  <h1 class="font-display text-2xl font-bold text-navy mb-1">Nueva factura</h1>
  <p class="text-sm text-slate-500 mb-6">Emite una factura con uno o varios conceptos.</p>

  <form method="POST" action="<?= url('/admin/invoices') ?>" class="space-y-5">
    <?= csrf_field() ?>
    <?php if ($prefill): ?><input type="hidden" name="contract_id" value="<?= (int)$prefill['contract_id'] ?>"><?php endif; ?>

    <div class="card p-6">
      <div class="grid sm:grid-cols-3 gap-4">
        <div class="sm:col-span-1">
          <label class="block text-sm font-medium mb-1.5">Cliente</label>
          <select name="customer_id" class="fld">
            <option value="">Sin cliente</option>
            <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>" <?= (($prefill['customer_id']??null)==$c['id'])?'selected':'' ?>><?= e(trim($c['first_name'].' '.$c['last_name'])) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div><label class="block text-sm font-medium mb-1.5">Fecha de emisión</label><input type="date" name="issue_date" value="<?= date('Y-m-d') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Vencimiento</label><input type="date" name="due_date" class="fld"></div>
      </div>
    </div>

    <div class="card p-6">
      <div class="flex items-center justify-between mb-4"><h2 class="font-semibold text-navy">Conceptos</h2><button type="button" @click="add()" class="k-btn k-btn-outline !h-9"><i data-lucide="plus" class="w-4 h-4"></i> Agregar</button></div>
      <div class="space-y-2.5">
        <div class="hidden sm:grid grid-cols-[1fr_90px_120px_120px_36px] gap-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400 px-1"><span>Descripción</span><span>Cant.</span><span>Precio</span><span class="text-right">Total</span><span></span></div>
        <template x-for="(row,idx) in rows" :key="idx">
          <div class="grid grid-cols-[1fr_90px_120px_120px_36px] gap-2 items-center">
            <input type="text" :name="'items['+idx+'][description]'" x-model="row.description" placeholder="Concepto" class="fld !h-10">
            <input type="number" min="0" step="1" :name="'items['+idx+'][quantity]'" x-model.number="row.quantity" @input="calc()" class="fld !h-10">
            <input type="number" min="0" step="0.01" :name="'items['+idx+'][unit_price]'" x-model.number="row.unit_price" @input="calc()" class="fld !h-10">
            <div class="text-right font-semibold text-navy tnum text-sm" x-text="fmt((row.quantity||0)*(row.unit_price||0))"></div>
            <button type="button" @click="remove(idx)" class="icon-btn !w-9 !h-9 hover:!text-brand" x-show="rows.length>1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
          </div>
        </template>
      </div>
      <div class="mt-5 flex justify-end">
        <div class="w-full sm:w-72 space-y-2 text-sm">
          <div class="flex justify-between text-slate-500"><span>Subtotal</span><span class="font-medium text-navy tnum" x-text="fmt(subtotal)"></span></div>
          <div class="flex justify-between items-center text-slate-500"><span>Descuento</span><input type="number" min="0" step="0.01" name="discount_amount" x-model.number="discount" @input="calc()" value="0" class="fld !h-9 !w-28 text-right"></div>
          <div class="flex justify-between text-slate-500"><span x-text="'Impuesto ('+tax+'%)'"></span><span class="font-medium text-navy tnum" x-text="fmt(taxAmount)"></span></div>
          <div class="flex justify-between pt-2 border-t hairline text-base"><span class="font-bold text-navy">Total</span><span class="font-extrabold text-brand tnum" x-text="fmt(total)"></span></div>
        </div>
      </div>
    </div>

    <div class="k-sticky flex gap-2">
      <button type="submit" class="k-btn k-btn-grad">Emitir factura</button>
      <a href="<?= url('/admin/invoices') ?>" class="k-btn k-btn-outline">Cancelar</a>
    </div>
  </form>
</div>

<?php \App\Core\View::push('scripts', '<script>
function invForm(cfg){
  return {
    tax:cfg.tax, symbol:cfg.symbol, rows:cfg.rows, discount:0, subtotal:0, taxAmount:0, total:0,
    init(){ this.calc(); },
    fmt(n){ return this.symbol+" "+Number(n||0).toLocaleString("en-US",{minimumFractionDigits:2,maximumFractionDigits:2}); },
    add(){ this.rows.push({description:"",quantity:1,unit_price:0}); },
    remove(i){ this.rows.splice(i,1); this.calc(); },
    calc(){ var s=0; this.rows.forEach(r=>s+=(r.quantity||0)*(r.unit_price||0)); this.subtotal=s; var base=Math.max(0,s-(this.discount||0)); this.taxAmount=base*(this.tax/100); this.total=base+this.taxAmount; }
  }
}
</script>'); ?>