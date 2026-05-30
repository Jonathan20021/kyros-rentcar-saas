<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
  <div>
    <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Mantenimiento</h1>
    <p class="text-sm text-slate-500"><?= count($records) ?> registros</p>
  </div>
  <?php if (can('maintenance.create')): ?><a href="<?= url('/admin/maintenance/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nuevo mantenimiento</a><?php endif; ?>
</div>

<form method="GET" class="card p-3 sm:p-4 mb-5 flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-end">
  <div class="flex-1 min-w-[160px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !h-10 !text-[13px]">
      <option value="">Todos</option>
      <?php foreach (['scheduled'=>'Programado','in_progress'=>'En proceso','completed'=>'Completado','cancelled'=>'Cancelado'] as $k=>$lbl): ?>
        <option value="<?= $k ?>" <?= ($filters['status']===$k)?'selected':'' ?>><?= $lbl ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="k-btn k-btn-dark !h-10 self-end">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto sm:overflow-x-visible">
    <table class="k-table">
      <thead>
        <tr><th>Vehículo</th><th>Tipo</th><th>Proveedor</th><th>Costo</th><th>Fecha</th><th>Próximo</th><th>Estado</th><th class="text-right">Acciones</th></tr>
      </thead>
      <tbody>
        <?php $types=['oil'=>'Aceite','tires'=>'Gomas','brakes'=>'Frenos','battery'=>'Batería','alignment'=>'Alineación','mechanical'=>'Mecánica','deep_clean'=>'Limpieza','paint'=>'Pintura','inspection'=>'Inspección','other'=>'Otro'];
        $stLabels=['scheduled'=>'bg-indigo-50 text-indigo-700','in_progress'=>'bg-amber-50 text-amber-700','completed'=>'bg-emerald-50 text-emerald-700','cancelled'=>'bg-slate-100 text-slate-600'];
        $stText=['scheduled'=>'Programado','in_progress'=>'En proceso','completed'=>'Completado','cancelled'=>'Cancelado']; ?>
        <?php foreach ($records as $m): ?>
        <tr>
          <td data-label="Vehículo" class="k-td-primary"><span class="font-medium text-navy dark:text-white"><?= e($m['brand'].' '.$m['model']) ?></span> <span class="text-xs text-slate-400"><?= e($m['plate_number'] ?? 's/p') ?></span></td>
          <td data-label="Tipo" class="text-slate-500"><?= $types[$m['maintenance_type']] ?? $m['maintenance_type'] ?></td>
          <td data-label="Proveedor" class="text-slate-500 truncate"><?= e($m['provider'] ?? '—') ?></td>
          <td data-label="Costo" class="font-semibold text-navy dark:text-white tnum"><?= money($m['cost']) ?></td>
          <td data-label="Fecha" class="text-slate-500 tnum"><?= format_date($m['start_date']) ?></td>
          <td data-label="Próximo" class="text-slate-500 tnum"><?= $m['next_due_date'] ? format_date($m['next_due_date']) : '—' ?></td>
          <td data-label="Estado"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $stLabels[$m['status']] ?? '' ?>"><?= $stText[$m['status']] ?? $m['status'] ?></span></td>
          <td class="k-td-actions text-right whitespace-nowrap">
            <?php if (can('maintenance.edit') && $m['status'] !== 'completed'): ?>
              <form method="POST" action="<?= url('/admin/maintenance/complete/'.$m['id']) ?>" class="inline"
                    data-confirm="El vehículo se liberará si estaba en mantenimiento." data-confirm-title="¿Marcar como completado?" data-confirm-label="Sí, completar" data-confirm-variant="warning">
                <?= csrf_field() ?>
                <button class="p-1.5 inline-grid rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-500/10 text-slate-400 hover:text-emerald-600" title="Marcar completado">
                  <i data-lucide="check-circle" class="w-4 h-4"></i>
                </button>
              </form>
            <?php endif; ?>
            <?php if (can('maintenance.edit')): ?>
              <a href="<?= url('/admin/maintenance/edit/'.$m['id']) ?>" class="p-1.5 inline-grid rounded-lg hover:bg-paper dark:hover:bg-slate-800 text-slate-400 hover:text-navy dark:hover:text-white" title="Editar">
                <i data-lucide="pencil" class="w-4 h-4"></i>
              </a>
            <?php endif; ?>
            <?php if (can('maintenance.delete')): ?>
              <form method="POST" action="<?= url('/admin/maintenance/delete/'.$m['id']) ?>" class="inline"
                    data-confirm="El registro se borrará permanentemente." data-confirm-title="¿Eliminar este mantenimiento?">
                <?= csrf_field() ?>
                <button class="p-1.5 inline-grid rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-slate-400 hover:text-red-600" title="Eliminar">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($records)): ?>
        <tr><td colspan="8" class="text-center text-slate-400 py-12"><i data-lucide="wrench" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>No hay registros de mantenimiento</p></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
