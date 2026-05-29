<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Contratos</h1>
    <p class="text-sm text-slate-500"><?= count($contracts) ?> contratos</p>
  </div>
</div>

<form method="GET" class="card p-4 mb-5 flex gap-3 items-end">
  <div class="min-w-[160px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !py-2 !text-[13px]">
      <option value="">Todos</option>
      <?php foreach (['draft','active','finished','cancelled','overdue','claim'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status']===$s)?'selected':'' ?>><?= status_label($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="k-btn k-btn-dark !py-2">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-slate-50 dark:bg-slate-800/50">
        <tr>
          <th class="px-6 py-3 font-medium">Numero</th><th class="px-6 py-3 font-medium">Cliente</th>
          <th class="px-6 py-3 font-medium">Vehiculo</th><th class="px-6 py-3 font-medium">Periodo</th>
          <th class="px-6 py-3 font-medium">Total</th><th class="px-6 py-3 font-medium">Balance</th>
          <th class="px-6 py-3 font-medium">Estado</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#EAECEF] dark:divide-slate-800">
        <?php foreach ($contracts as $c): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="px-6 py-3 font-mono text-xs font-medium"><a href="<?= url('/admin/contracts/show/'.$c['id']) ?>" class="text-brand hover:underline"><?= e($c['contract_number']) ?></a></td>
          <td class="px-6 py-3"><?= e($c['customer_name']) ?></td>
          <td class="px-6 py-3 text-slate-500"><?= e($c['brand'].' '.$c['model']) ?><div class="text-xs text-slate-400"><?= e($c['plate_number']) ?></div></td>
          <td class="px-6 py-3 text-slate-500 text-xs"><?= format_date($c['start_datetime']) ?> → <?= format_date($c['end_datetime']) ?></td>
          <td class="px-6 py-3 font-semibold"><?= money($c['total_amount']) ?></td>
          <td class="px-6 py-3 <?= $c['balance_due'] > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' ?>"><?= money($c['balance_due']) ?></td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($c['status']) ?>"><?= status_label($c['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($contracts)): ?>
        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><i data-lucide="file-text" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>No hay contratos</p></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
