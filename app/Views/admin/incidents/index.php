<?php
$typeLabels=['traffic_fine'=>'Multa de tránsito','exterior_damage'=>'Daño exterior','interior_damage'=>'Daño interior','accident'=>'Accidente','theft'=>'Robo','late'=>'Retraso','fuel'=>'Combustible','cleaning'=>'Limpieza','key_loss'=>'Pérdida de llave','other'=>'Otro'];
$stLabels=['open'=>'Abierta','review'=>'En revisión','charged'=>'Cobrada','cancelled'=>'Cancelada','closed'=>'Cerrada'];
$stBadge=['open'=>'bg-amber-100 text-amber-700','review'=>'bg-indigo-100 text-indigo-700','charged'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-slate-100 text-slate-600','closed'=>'bg-slate-100 text-slate-600'];
$flow=['open'=>['review'=>'Revisar','charged'=>'Cobrar','cancelled'=>'Cancelar'],'review'=>['charged'=>'Cobrar','cancelled'=>'Cancelar'],'charged'=>['closed'=>'Cerrar']];
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 sm:mb-6">
  <div>
    <h1 class="font-display text-xl sm:text-2xl font-bold text-navy dark:text-white">Incidencias</h1>
    <p class="text-sm text-slate-500"><?= count($incidents) ?> registros · <?= $counts['open']['c'] ?> abiertas</p>
  </div>
  <?php if (can('incidents.create')): ?><a href="<?= url('/admin/incidents/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nueva incidencia</a><?php endif; ?>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
  <?php foreach ([['Abiertas','open','shield-alert','bg-amber-50 text-amber-600'],['En revisión','review','search','bg-indigo-50 text-indigo-600'],['Cobradas','charged','circle-check-big','bg-emerald-50 text-emerald-600'],['Monto total','_sum','dollar-sign','bg-red-50 text-brand']] as $k): ?>
  <div class="card p-5">
    <div class="flex items-center gap-2.5"><div class="w-9 h-9 rounded-xl grid place-items-center <?= $k[3] ?>"><i data-lucide="<?= $k[2] ?>" class="w-[18px] h-[18px]"></i></div><p class="text-[13px] text-slate-400 font-medium"><?= $k[0] ?></p></div>
    <?php if ($k[1]==='_sum'): $sum=array_sum(array_map(fn($x)=>$x['total'],$counts)); ?>
      <p class="mt-3 text-[22px] leading-none font-extrabold text-navy tnum"><?= money($sum) ?></p>
    <?php else: ?>
      <p class="mt-3 text-[24px] leading-none font-extrabold text-navy tnum" data-count="<?= (int)$counts[$k[1]]['c'] ?>">0</p>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
  <div class="min-w-[160px]"><label class="block text-xs font-medium text-slate-500 mb-1">Tipo</label>
    <select name="type" class="fld !h-10"><option value="">Todos</option><?php foreach ($typeLabels as $k=>$lbl): ?><option value="<?= $k ?>" <?= $filters['type']===$k?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?></select>
  </div>
  <div class="min-w-[160px]"><label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
    <select name="status" class="fld !h-10"><option value="">Todos</option><?php foreach ($stLabels as $k=>$lbl): ?><option value="<?= $k ?>" <?= $filters['status']===$k?'selected':'' ?>><?= $lbl ?></option><?php endforeach; ?></select>
  </div>
  <button class="k-btn k-btn-dark !h-10">Filtrar</button>
</form>

<div class="card overflow-hidden">
  <div class="overflow-x-auto sm:overflow-x-visible">
    <table class="k-table">
      <thead>
        <tr><th>Tipo</th><th>Vehículo</th><th>Cliente</th><th>Contrato</th><th>Monto</th><th>Estado</th><th class="text-right">Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($incidents as $i): ?>
        <tr>
          <td data-label="Tipo" class="k-td-primary">
            <a href="<?= url('/admin/incidents/show/'.$i['id']) ?>" class="font-medium text-navy dark:text-white hover:text-brand"><?= $typeLabels[$i['type']] ?? $i['type'] ?></a>
            <?php if($i['description']): ?><div class="text-xs text-slate-400 truncate max-w-[260px]"><?= e($i['description']) ?></div><?php endif; ?>
          </td>
          <td data-label="Vehículo" class="text-slate-500 truncate"><?= e(trim(($i['brand']??'').' '.($i['model']??'')) ?: '—') ?></td>
          <td data-label="Cliente" class="text-slate-500 truncate"><?= e($i['customer_name'] ?? '—') ?></td>
          <td data-label="Contrato" class="text-slate-400 text-xs font-mono"><?= e($i['contract_number'] ?? '—') ?></td>
          <td data-label="Monto" class="font-semibold text-navy dark:text-white tnum"><?= money($i['amount']) ?></td>
          <td data-label="Estado"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $stBadge[$i['status']]??'' ?>"><?= $stLabels[$i['status']] ?? $i['status'] ?></span></td>
          <td class="k-td-actions text-right">
            <div class="flex items-center justify-end gap-1.5 flex-wrap">
              <a href="<?= url('/admin/incidents/show/'.$i['id']) ?>" class="p-1.5 inline-grid rounded-lg hover:bg-paper dark:hover:bg-slate-800 text-slate-400 hover:text-navy dark:hover:text-white" title="Ver detalle">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </a>
              <?php if (can('incidents.edit') && !empty($flow[$i['status']])): ?>
                <?php foreach ($flow[$i['status']] as $ns=>$lbl): ?>
                <form method="POST" action="<?= url('/admin/incidents/status/'.$i['id']) ?>" class="inline"><?= csrf_field() ?><input type="hidden" name="status" value="<?= $ns ?>"><button class="px-2.5 py-1 text-xs rounded-lg border hairline hover:bg-paper dark:hover:bg-slate-800 font-medium"><?= $lbl ?></button></form>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($incidents)): ?><tr><td colspan="7" class="text-center text-slate-400 py-12"><i data-lucide="shield-check" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>Sin incidencias 🎉</p></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
