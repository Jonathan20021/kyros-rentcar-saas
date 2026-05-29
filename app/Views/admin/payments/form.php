<div class="max-w-2xl mx-auto">
  <h1 class="font-display text-2xl font-bold text-navy mb-1">Registrar pago</h1>
  <p class="text-sm text-slate-500 mb-6">Aplica un pago a un contrato o registra un ingreso.</p>

  <form method="POST" action="<?= url('/admin/payments') ?>" class="card p-6 space-y-5"
        x-data="{cid: '<?= (int)($contract['id'] ?? 0) ?>', balances: <?= htmlspecialchars(json_encode(array_column($contracts,'balance_due','id')), ENT_QUOTES) ?>}">
    <?= csrf_field() ?>
    <div>
      <label class="block text-sm font-medium mb-1.5">Contrato</label>
      <select name="contract_id" x-model="cid" class="fld">
        <option value="">Sin contrato (ingreso directo)</option>
        <?php foreach ($contracts as $ct): ?>
          <option value="<?= $ct['id'] ?>" <?= (($contract['id'] ?? null)==$ct['id'])?'selected':'' ?>><?= e($ct['contract_number']) ?> · <?= e($ct['customer_name']) ?> (balance <?= money($ct['balance_due']) ?>)</option>
        <?php endforeach; ?>
      </select>
      <p x-show="cid && balances[cid]>0" x-cloak class="text-xs text-amber-600 mt-1.5">Balance pendiente: <b x-text="'<?= \App\Core\Config::get('app.currency_symbol') ?> '+Number(balances[cid]).toLocaleString()"></b></p>
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div><label class="block text-sm font-medium mb-1.5">Monto *</label><input type="number" step="0.01" name="amount" required class="fld" placeholder="0.00"></div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Método *</label>
        <select name="method" class="fld">
          <?php foreach (['cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta','paypal'=>'PayPal','stripe'=>'Stripe','azul'=>'Azul','cardnet'=>'CardNet','other'=>'Otro'] as $k=>$lbl): ?>
            <option value="<?= $k ?>"><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label class="block text-sm font-medium mb-1.5">Referencia</label><input name="reference" class="fld" placeholder="No. transacción / autorización"></div>
      <div><label class="block text-sm font-medium mb-1.5">Fecha</label><input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" class="fld"></div>
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Notas</label><textarea name="notes" rows="2" class="fld"></textarea></div>
    </div>
    <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="allow_overpay" value="1" class="rounded text-brand focus:ring-brand/30"> Permitir monto mayor al balance</label>
    <div class="flex gap-2">
      <button type="submit" class="k-btn k-btn-grad">Registrar pago</button>
      <a href="<?= url('/admin/payments') ?>" class="k-btn k-btn-outline">Cancelar</a>
    </div>
  </form>
</div>
