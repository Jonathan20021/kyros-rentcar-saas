<?php
use App\Core\View;
$vehJs = array_map(fn($v)=>['id'=>(int)$v['id'],'label'=>$v['brand'].' '.$v['model'].' · '.($v['plate_number']??'s/p'),'price'=>(float)$v['daily_price'],'deposit'=>(float)$v['deposit_amount'],'status'=>$v['status']], $vehicles);
$exJs  = array_map(fn($e)=>['id'=>(int)$e['id'],'name'=>$e['name'],'price'=>(float)$e['price'],'ct'=>$e['charge_type']], $extras);
?>
<div class="max-w-5xl mx-auto" x-data='resForm(<?= json_encode([
  "tax"=>(float)$tenant["tax_rate"],"symbol"=>\App\Services\LocaleService::currencySymbol($tenant["currency"] ?? "DOP"),"decimals"=>\App\Services\LocaleService::currencyDecimals($tenant["currency"] ?? "DOP"),
  "vehicles"=>$vehJs,"extras"=>$exJs,"availUrl"=>url("/admin/reservations/availability"),"preVehicle"=>(int)($preVehicle ?? 0)
], JSON_UNESCAPED_UNICODE) ?>)' x-init="if(preVehicle){ vehicleId=preVehicle; onVehicle(); }">
  <h1 class="font-display text-2xl font-bold text-navy mb-1">Nueva reserva</h1>
  <p class="text-sm text-slate-500 mb-6">Crea una reserva interna para un cliente.</p>

  <form method="POST" action="<?= url('/admin/reservations') ?>" class="grid lg:grid-cols-3 gap-5">
    <?= csrf_field() ?>
    <div class="lg:col-span-2 space-y-5">
      <!-- Customer -->
      <div class="card p-6">
        <h2 class="font-semibold text-navy mb-4 flex items-center gap-2"><i data-lucide="user" class="w-4 h-4 text-brand"></i> Cliente</h2>
        <div class="flex gap-2 mb-3">
          <button type="button" @click="newClient=false" :class="!newClient?'k-btn-dark':'k-btn-outline'" class="k-btn !h-9 flex-1">Cliente existente</button>
          <button type="button" @click="newClient=true" :class="newClient?'k-btn-dark':'k-btn-outline'" class="k-btn !h-9 flex-1">Nuevo cliente</button>
        </div>
        <div x-show="!newClient">
          <select name="customer_id" class="fld">
            <option value="">Selecciona un cliente…</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e(trim($c['first_name'].' '.$c['last_name'])) ?> <?= $c['phone']?'· '.e($c['phone']):'' ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div x-show="newClient" x-cloak class="grid sm:grid-cols-3 gap-3">
          <input name="new_customer_name" placeholder="Nombre completo" class="fld">
          <input name="new_customer_phone" placeholder="Teléfono" class="fld">
          <input name="new_customer_email" placeholder="Email" class="fld">
        </div>
      </div>

      <!-- Vehicle + dates -->
      <div class="card p-6">
        <h2 class="font-semibold text-navy mb-4 flex items-center gap-2"><i data-lucide="car" class="w-4 h-4 text-brand"></i> Vehículo y fechas</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium mb-1.5">Vehículo *</label>
            <select name="vehicle_id" required x-model.number="vehicleId" @change="onVehicle()" class="fld">
              <option value="">Selecciona…</option>
              <?php foreach ($vehicles as $v): ?>
                <option value="<?= $v['id'] ?>"><?= e($v['brand'].' '.$v['model']) ?> · <?= e($v['plate_number'] ?? 's/p') ?> — <?= money($v['daily_price']) ?>/día (<?= status_label($v['status']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div><label class="block text-sm font-medium mb-1.5">Inicio *</label><input type="date" name="start_date" required x-model="start" @change="check()" class="fld"></div>
          <div><label class="block text-sm font-medium mb-1.5">Hora inicio</label><input type="time" name="start_time" value="09:00" class="fld"></div>
          <div><label class="block text-sm font-medium mb-1.5">Devolución *</label><input type="date" name="end_date" required x-model="end" @change="check()" class="fld"></div>
          <div><label class="block text-sm font-medium mb-1.5">Hora devolución</label><input type="time" name="end_time" value="09:00" class="fld"></div>
          <div><label class="block text-sm font-medium mb-1.5">Lugar de entrega</label><input name="pickup_location" class="fld"></div>
          <div><label class="block text-sm font-medium mb-1.5">Lugar de devolución</label><input name="return_location" class="fld"></div>
        </div>
        <div x-show="avail!==null" x-cloak class="mt-3 text-sm font-medium" :class="avail?'text-emerald-600':'text-brand'">
          <span x-show="avail">✓ Vehículo disponible en ese rango</span>
          <span x-show="!avail">✕ No disponible en esas fechas</span>
        </div>
      </div>

      <!-- Extras + pricing inputs -->
      <div class="card p-6">
        <h2 class="font-semibold text-navy mb-4 flex items-center gap-2"><i data-lucide="plus-circle" class="w-4 h-4 text-brand"></i> Extras y ajustes</h2>
        <div class="grid sm:grid-cols-2 gap-3 mb-4">
          <?php foreach ($extras as $ex): $ctl=['per_day'=>'/día','one_time'=>'único','per_reservation'=>'/reserva'][$ex['charge_type']]??''; ?>
          <label class="flex items-center gap-3 p-3 rounded-xl border hairline cursor-pointer hover:border-brand/40">
            <input type="checkbox" name="extras[]" value="<?= $ex['id'] ?>" @change="toggleExtra(<?= $ex['id'] ?>)" class="rounded text-brand focus:ring-brand/30">
            <span class="flex-1 text-sm"><?= e($ex['name']) ?></span>
            <span class="text-sm font-semibold text-navy tnum"><?= money($ex['price']) ?> <span class="text-xs font-normal text-slate-400"><?= $ctl ?></span></span>
          </label>
          <?php endforeach; ?>
        </div>
        <div class="grid sm:grid-cols-3 gap-4">
          <div><label class="block text-sm font-medium mb-1.5">Tarifa/día (override)</label><input type="number" step="0.01" name="daily_rate" x-model.number="rateOverride" @input="recalc()" placeholder="auto" class="fld"></div>
          <div><label class="block text-sm font-medium mb-1.5">Descuento</label><input type="number" step="0.01" name="discount_amount" x-model.number="discount" @input="recalc()" value="0" class="fld"></div>
          <div><label class="block text-sm font-medium mb-1.5">Estado</label>
            <select name="status" class="fld"><option value="confirmed">Confirmada</option><option value="pending">Pendiente</option></select>
          </div>
          <div class="sm:col-span-3"><label class="block text-sm font-medium mb-1.5">Notas</label><textarea name="notes" rows="2" class="fld"></textarea></div>
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div>
      <div class="card p-6 sticky top-24">
        <h2 class="font-semibold text-navy mb-4">Resumen</h2>
        <div class="space-y-2.5 text-sm">
          <div class="flex justify-between text-slate-500"><span>Días</span><span class="font-semibold text-navy tnum" x-text="days"></span></div>
          <div class="flex justify-between text-slate-500"><span x-text="fmt(rate)+' × '+days"></span><span class="font-semibold text-navy tnum" x-text="fmt(subtotal)"></span></div>
          <div class="flex justify-between text-slate-500" x-show="discount>0"><span>Descuento</span><span class="font-semibold text-brand tnum" x-text="'-'+fmt(discount)"></span></div>
          <div class="flex justify-between text-slate-500" x-show="extrasTotal>0"><span>Extras</span><span class="font-semibold text-navy tnum" x-text="fmt(extrasTotal)"></span></div>
          <div class="flex justify-between text-slate-500"><span x-text="'Impuesto ('+tax+'%)'"></span><span class="font-semibold text-navy tnum" x-text="fmt(taxAmount)"></span></div>
          <div class="flex justify-between text-slate-400 text-xs"><span>Depósito</span><span class="tnum" x-text="fmt(deposit)"></span></div>
          <div class="flex justify-between pt-3 border-t hairline text-base"><span class="font-bold text-navy">Total</span><span class="font-extrabold text-brand tnum" x-text="fmt(total)"></span></div>
        </div>
        <button type="submit" class="k-btn k-btn-grad w-full mt-5">Crear reserva</button>
        <a href="<?= url('/admin/reservations') ?>" class="k-btn k-btn-ghost w-full mt-2">Cancelar</a>
      </div>
    </div>
  </form>
</div>

<?php View::push('scripts', '<script>
function resForm(cfg){
  return {
    tax:cfg.tax, symbol:cfg.symbol, decimals:(cfg.decimals??2), vehicles:cfg.vehicles, extrasDefs:cfg.extras, availUrl:cfg.availUrl, preVehicle:cfg.preVehicle||0,
    newClient:false, vehicleId:"", start:"", end:"", chosen:[], rateOverride:null, discount:0, avail:null,
    days:1, rate:0, deposit:0, subtotal:0, extrasTotal:0, taxAmount:0, total:0,
    fmt(n){ return this.symbol+" "+Number(n||0).toLocaleString("en-US",{minimumFractionDigits:this.decimals,maximumFractionDigits:this.decimals}); },
    onVehicle(){ var v=this.vehicles.find(x=>x.id===this.vehicleId); if(v){ this.deposit=v.deposit; if(!this.rateOverride) this.rate=v.price; } this.recalc(); this.check(); },
    toggleExtra(id){ var i=this.chosen.indexOf(id); if(i>=0)this.chosen.splice(i,1); else this.chosen.push(id); this.recalc(); },
    recalc(){
      var d=1; if(this.start&&this.end){ d=Math.ceil((new Date(this.end)-new Date(this.start))/86400000); if(!d||d<1)d=1; } this.days=d;
      var v=this.vehicles.find(x=>x.id===this.vehicleId);
      this.rate=this.rateOverride||(v?v.price:0);
      this.subtotal=this.rate*d;
      var ex=0; this.chosen.forEach(id=>{var def=this.extrasDefs.find(x=>x.id===id); if(def) ex+=def.ct==="per_day"?def.price*d:def.price;}); this.extrasTotal=ex;
      var taxable=Math.max(0,this.subtotal-(this.discount||0))+ex;
      this.taxAmount=taxable*(this.tax/100);
      this.total=Math.max(0,this.subtotal-(this.discount||0))+ex+this.taxAmount;
    },
    check(){ this.recalc(); if(this.vehicleId&&this.start&&this.end){ var u=this.availUrl+"?vehicle_id="+this.vehicleId+"&start="+this.start+"&end="+this.end;
      fetch(u,{headers:{"X-Requested-With":"XMLHttpRequest"}}).then(r=>r.json()).then(j=>this.avail=!!j.available).catch(()=>this.avail=null);
    } }
  }
}
</script>'); ?>
