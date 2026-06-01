<?php
use App\Core\View;
echo View::renderPartial('public/storefront/_nav', ['tenant' => $tenant]);
$primary = $tenant['primary_color'];
$inputCls = 'lux-field';
$extrasJs = array_map(fn($e) => [
  'id'=>(int)$e['id'],'name'=>$e['name'],'price'=>(float)$e['price'],'charge_type'=>$e['charge_type']
], $extras);
$publicPromos = $publicPromos ?? [];
?>
<section class="relative max-w-6xl mx-auto px-4 sm:px-6 pt-28 pb-16"
  x-data='reserveForm(<?= json_encode([
    "daily"=>(float)$vehicle["daily_price"],
    "tax"=>(float)$tenant["tax_rate"],
    "deposit"=>(float)$vehicle["deposit_amount"],
    "symbol"=>\App\Services\LocaleService::currencySymbol($tenant["currency"] ?? "DOP"),
    "decimals"=>\App\Services\LocaleService::currencyDecimals($tenant["currency"] ?? "DOP"),
    "extras"=>$extrasJs,
    "start"=>$rangeStart,"end"=>$rangeEnd
  ], JSON_UNESCAPED_UNICODE) ?>)'>
  <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none"><div class="lux-orb" style="width:460px;height:460px;right:-160px;top:-120px;opacity:.13"></div></div>
  <nav class="text-sm text-[var(--lux-dim)] mb-6 flex items-center gap-1.5">
    <a href="<?= url('/r/' . $tenant['slug']) ?>" class="hover:text-white transition-colors">Vehículos</a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
    <a href="<?= url('/r/' . $tenant['slug'] . '/vehiculo/' . $vehicle['slug']) ?>" class="hover:text-white transition-colors"><?= e($vehicle['brand'].' '.$vehicle['model']) ?></a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
    <span class="text-white font-medium">Reservar</span>
  </nav>

  <h1 class="font-display text-3xl sm:text-4xl font-extrabold tracking-[-.04em] text-white mb-7">Reservar <span style="color:var(--lux-brand-text)"><?= e($vehicle['brand'].' '.$vehicle['model']) ?></span></h1>

  <?php if (!empty($publicPromos)): ?>
  <div class="lux-surface rounded-2xl p-4 mb-6 flex flex-wrap items-center gap-3" style="border-style:dashed;border-color:color-mix(in srgb,var(--brand) 40%,transparent)">
    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-white"><i data-lucide="ticket-percent" class="w-4 h-4" style="color:var(--lux-brand-text)"></i>Promociones disponibles:</span>
    <?php foreach ($publicPromos as $pp):
      $label = $pp['discount_type']==='percent' ? rtrim(rtrim(number_format((float)$pp['discount_value'],2),'0'),'.').'% off' : money((float)$pp['discount_value']).' off';
    ?>
    <button type="button" @click="applyPromoCode('<?= e($pp['code']) ?>')"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#1a1a1a] hover:bg-[#222] border border-[#363636] transition">
      <span class="font-mono font-bold text-xs text-white"><?= e($pp['code']) ?></span>
      <span class="text-xs font-semibold" style="color:var(--lux-brand-text)"><?= $label ?></span>
      <?php if (!empty($pp['valid_to'])): ?><span class="text-[10px] text-[var(--lux-dim)]">hasta <?= e(format_date($pp['valid_to'])) ?></span><?php endif; ?>
    </button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="POST" action="<?= url('/r/' . $tenant['slug'] . '/reservar/' . $vehicle['slug']) ?>" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6">
    <?= csrf_field() ?>
    <div class="lg:col-span-2 space-y-6">
      <!-- Dates -->
      <div class="lux-surface rounded-2xl p-6">
        <h2 class="font-bold text-white mb-4 flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4" style="color:var(--lux-brand-text)"></i> Fechas y horarios</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Fecha de inicio *</label><input type="date" name="start_date" required x-model="start" @change="recalc()" class="<?= $inputCls ?>"></div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Hora de inicio</label><input type="time" name="start_time" value="09:00" class="<?= $inputCls ?>"></div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Fecha de devolución *</label><input type="date" name="end_date" required x-model="end" @change="recalc()" class="<?= $inputCls ?>"></div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Hora de devolución</label><input type="time" name="end_time" value="09:00" class="<?= $inputCls ?>"></div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Lugar de entrega</label><input name="pickup_location" value="<?= e($tenant['address'] ?? '') ?>" class="<?= $inputCls ?>"></div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Lugar de devolución</label><input name="return_location" value="<?= e($tenant['address'] ?? '') ?>" class="<?= $inputCls ?>"></div>
        </div>
      </div>

      <!-- Extras -->
      <?php if (!empty($extras)): ?>
      <div class="lux-surface rounded-2xl p-6">
        <h2 class="font-bold text-white mb-4 flex items-center gap-2"><i data-lucide="plus-circle" class="w-4 h-4" style="color:var(--lux-brand-text)"></i> Extras</h2>
        <div class="grid sm:grid-cols-2 gap-3">
          <?php foreach ($extras as $ex): $ctLabel = ['per_day'=>'/día','one_time'=>'único','per_reservation'=>'/reserva'][$ex['charge_type']] ?? ''; ?>
          <label class="flex items-center gap-3 p-3 rounded-xl border border-[#363636] cursor-pointer transition hover:border-[#555]">
            <input type="checkbox" name="extras[]" value="<?= $ex['id'] ?>" @change="toggleExtra(<?= $ex['id'] ?>)" style="accent-color:var(--lux-brand-text)">
            <span class="flex-1 text-sm text-white"><?= e($ex['name']) ?></span>
            <span class="text-sm font-semibold text-white"><?= money($ex['price']) ?> <span class="text-xs font-normal text-[var(--lux-dim)]"><?= $ctLabel ?></span></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Customer -->
      <div class="lux-surface rounded-2xl p-6">
        <h2 class="font-bold text-white mb-4 flex items-center gap-2"><i data-lucide="user" class="w-4 h-4" style="color:var(--lux-brand-text)"></i> Tus datos</h2>
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2"><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Nombre completo *</label><input name="lead_name" required value="<?= old('lead_name') ?>" class="<?= $inputCls ?>"></div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Teléfono *</label><input name="lead_phone" required value="<?= old('lead_phone') ?>" class="<?= $inputCls ?>"></div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">WhatsApp</label><input name="lead_whatsapp" value="<?= old('lead_whatsapp') ?>" class="<?= $inputCls ?>"></div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Email</label><input type="email" name="lead_email" value="<?= old('lead_email') ?>" class="<?= $inputCls ?>"></div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Cédula / Pasaporte</label><input name="lead_document" value="<?= old('lead_document') ?>" class="<?= $inputCls ?>"></div>
          <div>
            <label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Contacto preferido</label>
            <select name="preferred_contact" class="<?= $inputCls ?>">
              <option value="whatsapp">WhatsApp</option><option value="phone">Teléfono</option><option value="email">Email</option>
            </select>
          </div>
          <div><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Licencia (opcional)</label><input type="file" name="lead_license" accept="image/*,application/pdf" class="<?= $inputCls ?> !py-2.5"></div>
          <div class="sm:col-span-2"><label class="block text-sm font-medium text-[var(--lux-muted)] mb-1.5">Comentarios</label><textarea name="notes" rows="2" class="<?= $inputCls ?>"><?= old('notes') ?></textarea></div>
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div class="lg:col-span-1">
      <div class="lux-surface rounded-2xl p-6 sticky top-24">
        <div class="flex items-center gap-3 pb-4 border-b border-[#262626]">
          <div class="w-16 h-12 rounded-lg bg-[#1a1a1a] overflow-hidden shrink-0">
            <?php if (!empty($vehicle['main_image'])): ?><img src="<?= e(media($vehicle['main_image'])) ?>" class="w-full h-full object-cover"><?php endif; ?>
          </div>
          <div>
            <p class="font-semibold text-white text-sm"><?= e($vehicle['brand'].' '.$vehicle['model']) ?></p>
            <p class="text-xs text-[var(--lux-dim)]"><?= e($vehicle['year']) ?></p>
          </div>
        </div>
        <div class="space-y-2.5 text-sm mt-4">
          <div class="flex justify-between text-[var(--lux-muted)]"><span>Días</span><span class="font-medium text-white" x-text="days"></span></div>
          <div class="flex justify-between text-[var(--lux-muted)]"><span x-text="fmt(daily) + ' × ' + days + ' días'"></span><span class="font-medium text-white" x-text="fmt(subtotal)"></span></div>
          <div class="flex justify-between text-[var(--lux-muted)]" x-show="extrasTotal>0"><span>Extras</span><span class="font-medium text-white" x-text="fmt(extrasTotal)"></span></div>
          <div class="flex justify-between text-emerald-400 font-medium" x-show="discount>0">
            <span class="flex items-center gap-1"><i data-lucide="ticket-percent" class="w-3.5 h-3.5"></i>Descuento (<span x-text="promoApplied"></span>)</span>
            <span x-text="'- ' + fmt(discount)"></span>
          </div>
          <div class="flex justify-between text-[var(--lux-muted)]"><span x-text="'Impuesto (' + tax + '%)'"></span><span class="font-medium text-white" x-text="fmt(taxAmount)"></span></div>
          <div class="flex justify-between text-[var(--lux-dim)] text-xs"><span>Depósito (reembolsable)</span><span x-text="fmt(deposit)"></span></div>
          <div class="flex justify-between pt-3 border-t border-[#262626] text-base">
            <span class="font-bold text-white">Total estimado</span>
            <span class="font-extrabold" style="color:var(--lux-brand-text)" x-text="fmt(total)"></span>
          </div>
        </div>

        <!-- Promo code -->
        <div class="mt-5 pt-4 border-t border-dashed border-[#363636]">
          <label class="block text-xs font-semibold text-[var(--lux-muted)] mb-1.5">¿Tienes un código promocional?</label>
          <div class="flex gap-2">
            <input type="text" name="promo_code" x-model="promoCode" @keydown.enter.prevent="applyPromoCode(promoCode)"
                   placeholder="VERANO15" class="<?= $inputCls ?> font-mono uppercase !text-sm"
                   oninput="this.value=this.value.toUpperCase()">
            <button type="button" @click="applyPromoCode(promoCode)"
                    class="px-4 rounded-xl bg-white text-[#0a0a0a] text-sm font-bold hover:bg-[#ececec] transition">Aplicar</button>
          </div>
          <p x-show="promoMsg" class="text-xs mt-1.5"
             :class="promoOk ? 'text-emerald-400' : 'text-red-400'"
             x-text="promoMsg"></p>
        </div>

        <button type="submit" class="lux-btn lux-btn-brand w-full mt-5 py-4">Confirmar reserva</button>
        <p class="text-xs text-[var(--lux-dim)] text-center mt-3">Te contactaremos para confirmar disponibilidad y pago.</p>
      </div>
    </div>
  </form>
</section>

<?php echo View::renderPartial('public/storefront/_footer', ['tenant' => $tenant]); ?>

<?php View::push('scripts', '<script>
function reserveForm(cfg){
  return {
    daily: cfg.daily, tax: cfg.tax, deposit: cfg.deposit, symbol: cfg.symbol, decimals: cfg.decimals,
    extrasDefs: cfg.extras, chosen: [], start: cfg.start || "", end: cfg.end || "",
    days: 1, subtotal: 0, extrasTotal: 0, taxAmount: 0, total: 0,
    promoCode: "", promoApplied: "", promoMsg: "", promoOk: false, promoType: "", promoValue: 0, discount: 0,
    init(){ this.recalc(); },
    fmt(n){ return this.symbol + " " + Number(n).toLocaleString("en-US", {minimumFractionDigits:this.decimals, maximumFractionDigits:this.decimals}); },
    toggleExtra(id){
      const i = this.chosen.indexOf(id);
      if(i>=0) this.chosen.splice(i,1); else this.chosen.push(id);
      this.recalc();
    },
    recalc(){
      let d = 1;
      if(this.start && this.end){
        const s = new Date(this.start), e = new Date(this.end);
        d = Math.ceil((e - s) / 86400000);
        if(!d || d < 1) d = 1;
      }
      this.days = d;
      this.subtotal = this.daily * d;
      let ex = 0;
      this.chosen.forEach(id=>{
        const def = this.extrasDefs.find(x=>x.id===id);
        if(!def) return;
        ex += def.charge_type === "per_day" ? def.price * d : def.price;
      });
      this.extrasTotal = ex;
      let base = this.subtotal + ex;
      if (this.promoApplied){
        if (this.promoType === "percent") this.discount = Math.round(base * (this.promoValue/100) * 100) / 100;
        else this.discount = Math.min(this.promoValue, base);
      } else { this.discount = 0; }
      let taxable = Math.max(0, base - this.discount);
      this.taxAmount = taxable * (this.tax/100);
      this.total = taxable + this.taxAmount;
    },
    async applyPromoCode(code){
      const c = (code||"").trim().toUpperCase();
      this.promoCode = c;
      if (!c){ this.promoApplied=""; this.promoMsg=""; this.recalc(); return; }
      try {
        const url = "' . url('/r/' . $tenant['slug'] . '/promo') . '?code=" + encodeURIComponent(c) + "&subtotal=" + (this.subtotal + this.extrasTotal);
        const r = await fetch(url, {headers:{"Accept":"application/json"}});
        const j = await r.json();
        if (!r.ok || !j.valid){
          this.promoApplied = ""; this.discount = 0;
          this.promoOk = false; this.promoMsg = j.message || "Código inválido.";
        } else {
          this.promoApplied = j.code; this.promoType = j.type; this.promoValue = j.value;
          this.promoOk = true; this.promoMsg = j.message;
        }
      } catch(e){ this.promoOk=false; this.promoMsg="Error verificando código."; }
      this.recalc();
      this.$nextTick(()=>window.lucide&&lucide.createIcons());
    }
  }
}
</script>'); ?>
