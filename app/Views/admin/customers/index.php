<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Clientes</h1>
    <p class="text-sm text-slate-500"><?= count($customers) ?> clientes registrados</p>
  </div>
  <?php if (can('customers.create')): ?>
  <a href="<?= url('/admin/customers/create') ?>" class="k-btn k-btn-grad">
    <i data-lucide="user-plus" class="w-4 h-4"></i> Nuevo cliente
  </a>
  <?php endif; ?>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
  <div class="flex-1 min-w-[200px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
    <input name="search" value="<?= e($filters['search']) ?>" placeholder="Nombre, documento, telefono o email" class="fld !py-2 !text-[13px]">
  </div>
  <div class="min-w-[150px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !py-2 !text-[13px]">
      <option value="">Todos</option>
      <?php foreach (['active','blocked','blacklist','pending'] as $s): ?>
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
          <th class="px-6 py-3 font-medium">Cliente</th><th class="px-6 py-3 font-medium">Documento</th>
          <th class="px-6 py-3 font-medium">Contacto</th><th class="px-6 py-3 font-medium">Riesgo</th>
          <th class="px-6 py-3 font-medium">Estado</th><th class="px-6 py-3 font-medium text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-[#EAECEF] dark:divide-slate-800">
        <?php foreach ($customers as $c): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="px-6 py-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-navy/5 text-navy grid place-items-center text-xs font-bold"><?= e(initials($c['first_name'].' '.$c['last_name'])) ?></div>
              <div>
                <div class="font-medium text-navy dark:text-white"><a href="<?= url('/admin/customers/show/'.$c['id']) ?>" class="hover:text-brand transition"><?= e(trim($c['first_name'].' '.$c['last_name'])) ?></a></div>
                <div class="text-xs text-slate-400"><?= e($c['email'] ?? '') ?></div>
              </div>
            </div>
          </td>
          <td class="px-6 py-3 text-slate-500"><?= e($c['document_number'] ?? '-') ?></td>
          <td class="px-6 py-3 text-slate-500"><?= e($c['phone'] ?? '-') ?></td>
          <td class="px-6 py-3">
            <?php $rc = ['low'=>'bg-emerald-100 text-emerald-700','medium'=>'bg-amber-100 text-amber-700','high'=>'bg-red-100 text-red-700']; ?>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $rc[$c['risk_level']] ?? '' ?>"><?= ucfirst($c['risk_level']) ?></span>
          </td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($c['status']) ?>"><?= status_label($c['status']) ?></span></td>
          <td class="px-6 py-3">
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
        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400"><i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>No hay clientes</p></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
