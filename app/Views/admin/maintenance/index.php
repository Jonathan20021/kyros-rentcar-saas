<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Mantenimiento</h1>
    <p class="text-sm text-slate-500"><?= count($records) ?> registros</p>
  </div>
  <?php if (can('maintenance.create')): ?><a href="<?= url('/admin/maintenance/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nuevo mantenimiento</a><?php endif; ?>
</div>

<form method="GET" class="card p-4 mb-5 flex gap-3 items-end">
  <div class="min-w-[160px]">
    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !py-2 !text-[13px]">
      <option value="">Todos</option>
      <?php foreach (['scheduled'=>'Programado','in_progress'=>'En proceso','completed'=>'Completado','cancelled'=>'Cancelado'] as $k=>$lbl): ?>
        <option value="<?= $k ?>" <?= ($filters['status']===$k)?'selected':'' ?>><?= $lbl ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="k-btn k-btn-dark !py-2">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-slate-50 dark:bg-slate-800/50">
        <tr><th class="px-6 py-3 font-medium">Vehículo</th><th class="px-6 py-3 font-medium">Tipo</th><th class="px-6 py-3 font-medium">Proveedor</th><th class="px-6 py-3 font-medium">Costo</th><th class="px-6 py-3 font-medium">Fecha</th><th class="px-6 py-3 font-medium">Próximo</th><th class="px-6 py-3 font-medium">Estado</th><th class="px-6 py-3 font-medium text-right">Acciones</th></tr>
      </thead>
      <tbody class="divide-y divide-[#EAECEF] dark:divide-slate-800">
        <?php $types=['oil'=>'Aceite','tires'=>'Gomas','brakes'=>'Frenos','battery'=>'Batería','alignment'=>'Alineación','mechanical'=>'Mecánica','deep_clean'=>'Limpieza','paint'=>'Pintura','inspection'=>'Inspección','other'=>'Otro'];
        $stLabels=['scheduled'=>'bg-indigo-50 text-indigo-700','in_progress'=>'bg-amber-50 text-amber-700','completed'=>'bg-emerald-50 text-emerald-700','cancelled'=>'bg-slate-100 text-slate-600'];
        $stText=['scheduled'=>'Programado','in_progress'=>'En proceso','completed'=>'Completado','cancelled'=>'Cancelado']; ?>
        <?php foreach ($records as $m): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
          <td class="px-6 py-3"><?= e($m['brand'].' '.$m['model']) ?><div class="text-xs text-slate-400"><?= e($m['plate_number'] ?? 's/p') ?></div></td>
          <td class="px-6 py-3 text-slate-500"><?= $types[$m['maintenance_type']] ?? $m['maintenance_type'] ?></td>
          <td class="px-6 py-3 text-slate-500"><?= e($m['provider'] ?? '—') ?></td>
          <td class="px-6 py-3 font-semibold tnum"><?= money($m['cost']) ?></td>
          <td class="px-6 py-3 text-slate-500 tnum"><?= format_date($m['start_date']) ?></td>
          <td class="px-6 py-3 text-slate-500 tnum"><?= $m['next_due_date'] ? format_date($m['next_due_date']) : '—' ?></td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $stLabels[$m['status']] ?? '' ?>"><?= $stText[$m['status']] ?? $m['status'] ?></span></td>
          <td class="px-6 py-3 text-right whitespace-nowrap">
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
        <tr><td colspan="8" class="px-6 py-12 text-center text-slate-400"><i data-lucide="wrench" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>No hay registros de mantenimiento</p></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
