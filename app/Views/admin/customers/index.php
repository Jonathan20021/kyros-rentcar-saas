<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
  <div>
    <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Clientes</h1>
    <p class="text-sm text-slate-500"><?= count($customers) ?> clientes registrados</p>
  </div>
  <?php if (can('customers.create')): ?>
  <a href="<?= url('/admin/customers/create') ?>" class="k-btn k-btn-grad">
    <i data-lucide="user-plus" class="w-4 h-4"></i> Nuevo cliente
  </a>
  <?php endif; ?>
</div>

<form method="GET" class="card p-3 sm:p-4 mb-5 flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-3 sm:items-end">
  <div class="flex-1 min-w-[200px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
    <input name="search" value="<?= e($filters['search']) ?>" placeholder="Nombre, documento, teléfono o email" class="fld !h-10 !text-[13px]">
  </div>
  <div class="flex gap-2">
    <div class="flex-1 min-w-[150px]">
      <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
      <select name="status" class="fld !h-10 !text-[13px]">
        <option value="">Todos</option>
        <?php foreach (['active','blocked','blacklist','pending'] as $s): ?>
          <option value="<?= $s ?>" <?= ($filters['status']===$s)?'selected':'' ?>><?= status_label($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="k-btn k-btn-dark !h-10 self-end">Filtrar</button>
  </div>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto sm:overflow-x-visible">
    <table class="k-table">
      <thead>
        <tr>
          <th>Cliente</th><th>Documento</th><th>Contacto</th>
          <th>Riesgo</th><th>Estado</th><th class="text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $rc = ['low'=>'bg-emerald-50 text-emerald-700','medium'=>'bg-amber-50 text-amber-700','high'=>'bg-red-50 text-red-700'];
        foreach ($customers as $c): ?>
        <tr>
          <td data-label="Cliente" class="k-td-primary">
            <div class="flex items-center gap-3 w-full">
              <div class="w-9 h-9 rounded-lg bg-navy/5 dark:bg-white/5 text-navy dark:text-white grid place-items-center text-xs font-bold shrink-0"><?= e(initials($c['first_name'].' '.($c['last_name'] ?? ''))) ?></div>
              <div class="min-w-0">
                <div class="font-medium text-navy dark:text-white truncate"><a href="<?= url('/admin/customers/show/'.$c['id']) ?>" class="hover:text-brand transition"><?= e(trim($c['first_name'].' '.($c['last_name'] ?? ''))) ?></a></div>
                <div class="text-xs text-slate-400 truncate"><?= e($c['email'] ?? '') ?></div>
              </div>
            </div>
          </td>
          <td data-label="Documento" class="text-slate-500 tnum"><?= e($c['document_number'] ?? '—') ?></td>
          <td data-label="Contacto" class="text-slate-500 tnum"><?= e($c['phone'] ?? '—') ?></td>
          <td data-label="Riesgo">
            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $rc[$c['risk_level']] ?? '' ?>"><?= ucfirst($c['risk_level']) ?></span>
          </td>
          <td data-label="Estado"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($c['status']) ?>"><?= status_label($c['status']) ?></span></td>
          <td class="k-td-actions text-right">
            <div class="flex items-center justify-end gap-1.5">
              <a href="<?= url('/admin/customers/show/' . $c['id']) ?>" class="icon-btn !w-8 !h-8" title="Ver"><i data-lucide="eye" class="w-4 h-4"></i></a>
              <?php if (can('customers.edit')): ?>
              <a href="<?= url('/admin/customers/edit/' . $c['id']) ?>" class="icon-btn !w-8 !h-8" title="Editar"><i data-lucide="pencil" class="w-4 h-4"></i></a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($customers)): ?>
        <tr><td colspan="6" class="text-center text-slate-400 py-12">
          <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
          <p>No hay clientes</p>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
