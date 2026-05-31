<?php use App\Core\View; ?>
<?php $allIds = array_map(fn($c) => (int)$c['id'], $customers); ?>
<div x-data='bulkList(<?= json_encode($allIds) ?>)' x-init="$nextTick(()=>window.lucide&&lucide.createIcons())">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
    <div>
      <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Clientes</h1>
      <p class="text-sm text-slate-500">
        <span x-show="!hasSelection()"><?= count($customers) ?> clientes registrados</span>
        <span x-show="hasSelection()" x-cloak><span class="font-bold text-navy" x-text="selected.length"></span> seleccionado<span x-show="selected.length>1">s</span></span>
      </p>
    </div>
    <div class="flex items-center gap-2">
      <!-- Bulk actions appear when there's a selection -->
      <div x-show="hasSelection()" x-cloak class="flex items-center gap-2">
        <button @click="clearSelection()" class="k-btn k-btn-outline">
          <i data-lucide="x" class="w-4 h-4"></i> Cancelar
        </button>
        <?php if (can('customers.delete')): ?>
        <form method="POST" action="<?= url('/admin/customers/bulk-delete') ?>"
              :data-confirm="`¿Eliminar ${selected.length} cliente${selected.length>1?'s':''}? Esta acción no se puede deshacer.`"
              data-confirm-variant="danger">
          <?= csrf_field() ?>
          <input type="hidden" name="ids" :value="selected.join(',')">
          <button class="k-btn k-btn-grad !bg-red-600 hover:!bg-red-700">
            <i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar (<span x-text="selected.length"></span>)
          </button>
        </form>
        <?php endif; ?>
      </div>
      <?php if (can('customers.create')): ?>
      <a href="<?= url('/admin/customers/create') ?>" class="k-btn k-btn-grad" x-show="!hasSelection()">
        <i data-lucide="user-plus" class="w-4 h-4"></i> Nuevo cliente
      </a>
      <?php endif; ?>
    </div>
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

  <?php if (empty($customers)): ?>
    <?= View::renderPartial('_partials/empty_state', [
      'icon'    => 'users',
      'title'   => $filters['search'] || $filters['status'] ? 'Sin resultados con esos filtros' : 'Aún no tienes clientes registrados',
      'message' => $filters['search'] || $filters['status']
                    ? 'Prueba con otros criterios o limpia los filtros.'
                    : 'Crea tu primer cliente para empezar a tomar reservas y emitir contratos.',
      'cta'     => can('customers.create')
                    ? ['label'=>'Crear primer cliente','url'=>url('/admin/customers/create'),'icon'=>'user-plus']
                    : null,
    ]) ?>
  <?php else: ?>
  <div class="card overflow-hidden">
    <div class="overflow-x-auto sm:overflow-x-visible">
      <table class="k-table">
        <thead>
          <tr>
            <?php if (can('customers.delete')): ?>
            <th class="!w-8 !pr-0">
              <input type="checkbox" @change="toggleAll($event)" :checked="allChecked()"
                     class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand cursor-pointer">
            </th>
            <?php endif; ?>
            <th>Cliente</th><th>Documento</th><th>Contacto</th>
            <th>Riesgo</th><th>Estado</th><th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $rc = ['low'=>'bg-emerald-50 text-emerald-700','medium'=>'bg-amber-50 text-amber-700','high'=>'bg-red-50 text-red-700'];
          foreach ($customers as $c): ?>
          <tr :class="selected.includes(<?= (int)$c['id'] ?>) ? 'bg-brand/[0.03]' : ''">
            <?php if (can('customers.delete')): ?>
            <td class="!pr-0">
              <input type="checkbox" :checked="selected.includes(<?= (int)$c['id'] ?>)"
                     @change="toggleOne(<?= (int)$c['id'] ?>)"
                     class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand cursor-pointer">
            </td>
            <?php endif; ?>
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
            <td data-label="Contacto">
              <div class="flex items-center gap-2">
                <span class="text-slate-500 tnum truncate"><?= e($c['phone'] ?? '—') ?></span>
                <?php if ($c['phone'] || $c['email']): ?>
                  <?= \App\Core\View::renderPartial('_partials/contact_actions', [
                    'phone'    => $c['phone'],
                    'whatsapp' => $c['whatsapp'] ?? $c['phone'],
                    'email'    => $c['email'],
                    'country'  => $tenant['country'] ?? 'DO',
                    'message'  => 'Hola ' . trim($c['first_name'] . ' ' . ($c['last_name'] ?? '')) . ', te escribo de ' . ($tenant['name'] ?? 'Kyros Rent Car') . '.',
                    'size'     => 'sm',
                  ]) ?>
                <?php endif; ?>
              </div>
            </td>
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
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php View::push('scripts', '<script>
function bulkList(allIds){
  return {
    allIds: allIds || [],
    selected: [],
    hasSelection(){ return this.selected.length > 0; },
    allChecked(){ return this.allIds.length > 0 && this.selected.length === this.allIds.length; },
    toggleAll(e){
      this.selected = e.target.checked ? this.allIds.slice() : [];
    },
    toggleOne(id){
      const i = this.selected.indexOf(id);
      if (i === -1) this.selected.push(id); else this.selected.splice(i, 1);
    },
    clearSelection(){ this.selected = []; },
  };
}
</script>'); ?>
