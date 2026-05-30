<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
  <div>
    <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Contratos</h1>
    <p class="text-sm text-slate-500"><?= count($contracts) ?> contratos</p>
  </div>
</div>

<form method="GET" class="card p-3 sm:p-4 mb-5 flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-3 sm:items-end">
  <div class="flex-1 min-w-[160px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !h-10 !text-[13px]">
      <option value="">Todos</option>
      <?php foreach (['draft','active','finished','cancelled','overdue','claim'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status']===$s)?'selected':'' ?>><?= status_label($s) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="k-btn k-btn-dark !h-10 self-end">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto sm:overflow-x-visible">
    <table class="k-table">
      <thead>
        <tr>
          <th>Número</th><th>Cliente</th><th>Vehículo</th><th>Período</th>
          <th>Total</th><th>Balance</th><th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contracts as $c): ?>
        <tr>
          <td data-label="Número" class="k-td-primary">
            <a href="<?= url('/admin/contracts/show/'.$c['id']) ?>" class="font-mono text-xs font-semibold text-brand hover:underline"><?= e($c['contract_number']) ?></a>
          </td>
          <td data-label="Cliente"><span class="text-navy dark:text-white font-medium truncate"><?= e($c['customer_name']) ?></span></td>
          <td data-label="Vehículo"><span class="text-slate-600 dark:text-slate-300 truncate"><?= e($c['brand'].' '.$c['model']) ?> <span class="text-slate-400 text-xs"><?= e($c['plate_number'] ?? '') ?></span></span></td>
          <td data-label="Período"><span class="text-slate-500 text-xs tnum"><?= format_date($c['start_datetime']) ?> → <?= format_date($c['end_datetime']) ?></span></td>
          <td data-label="Total" class="font-semibold text-navy dark:text-white tnum"><?= money($c['total_amount']) ?></td>
          <td data-label="Balance" class="<?= $c['balance_due'] > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' ?> tnum"><?= money($c['balance_due']) ?></td>
          <td data-label="Estado"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($c['status']) ?>"><?= status_label($c['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($contracts)): ?>
        <tr><td colspan="7" class="text-center text-slate-400 py-12">
          <i data-lucide="file-text" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
          <p>No hay contratos</p>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
