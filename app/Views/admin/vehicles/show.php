<?php $v=$vehicle; $docs=[
  ['Seguro',$v['insurance_expires']], ['Marbete',$v['marbete_expires']],
  ['Matrícula',$v['plate_expires']], ['Inspección',$v['inspection_expires']],
]; ?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <div class="flex items-center gap-3">
      <h1 class="font-display text-2xl font-bold text-navy"><?= e($v['brand'].' '.$v['model']) ?></h1>
      <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($v['status']) ?>"><?= status_label($v['status']) ?></span>
    </div>
    <p class="text-sm text-slate-500 mt-1"><?= e($v['version'] ?? '') ?> · <?= e($v['year']) ?> · <?= e($v['plate_number'] ?? 's/placa') ?></p>
  </div>
  <div class="flex gap-2">
    <?php if (can('reservations.create') && $v['status']==='available'): ?><a href="<?= url('/admin/reservations/create?vehicle='.$v['id']) ?>" class="k-btn k-btn-grad"><i data-lucide="calendar-plus" class="w-4 h-4"></i> Reservar</a><?php endif; ?>
    <?php if (can('vehicles.edit')): ?><a href="<?= url('/admin/vehicles/edit/'.$v['id']) ?>" class="k-btn k-btn-outline"><i data-lucide="pencil" class="w-4 h-4"></i> Editar</a><?php endif; ?>
    <a href="<?= url('/admin/vehicles') ?>" class="k-btn k-btn-ghost">Volver</a>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  <!-- LEFT: gallery + specs -->
  <div class="lg:col-span-2 space-y-5" x-data="{ main: <?= json_encode($gallery[0] ?? '') ?> }">
    <div class="card p-5">
      <div class="aspect-[16/10] bg-paper rounded-xl grid place-items-center overflow-hidden">
        <?php if (!empty($gallery)): ?><img :src="main" class="w-full h-full object-contain p-4">
        <?php else: ?><i data-lucide="car" class="w-20 h-20 text-slate-200"></i><?php endif; ?>
      </div>
      <?php if (count($gallery) > 1): ?>
      <div class="flex gap-2 mt-3">
        <?php foreach ($gallery as $g): ?>
          <button type="button" @click="main=<?= json_encode($g) ?>" class="w-20 h-14 rounded-lg overflow-hidden border-2 bg-paper" :class="main===<?= json_encode($g) ?>?'border-brand':'border-transparent'"><img src="<?= e($g) ?>" class="w-full h-full object-contain p-1"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="card p-6">
      <h2 class="font-display font-bold text-navy mb-4">Especificaciones</h2>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <?php foreach ([['users',$v['passengers'].' pasajeros'],['cog',$v['transmission']==='automatic'?'Automática':'Manual'],['fuel',ucfirst($v['fuel_type'])],['door-open',$v['doors'].' puertas'],['briefcase',$v['luggage_capacity'].' maletas'],['gauge',number_format((int)$v['mileage']).' km'],['palette',$v['color']??'—'],['tag',$v['category_name']??'—']] as $s): ?>
        <div class="bg-paper rounded-xl p-3 text-center"><i data-lucide="<?= $s[0] ?>" class="w-5 h-5 mx-auto text-slate-400"></i><p class="text-[12px] text-navy mt-1.5 font-medium"><?= e($s[1]) ?></p></div>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($features)): ?>
      <h3 class="font-semibold text-navy mt-6 mb-2.5 text-sm">Características</h3>
      <div class="flex flex-wrap gap-2"><?php foreach ($features as $f): ?><span class="px-3 py-1.5 rounded-lg bg-paper border hairline text-sm text-slate-600 flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500"></i><?= e($f) ?></span><?php endforeach; ?></div>
      <?php endif; ?>
    </div>

    <!-- Reservations history -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b hairline font-display font-bold text-navy">Historial de reservas</div>
      <table class="w-full text-sm"><tbody class="divide-y hairline">
        <?php foreach ($reservations as $r): ?>
        <tr class="hover:bg-paper">
          <td class="px-6 py-3 font-mono text-xs text-brand"><?= e($r['reservation_code']) ?></td>
          <td class="px-6 py-3 text-slate-600"><?= e($r['customer_name'] ?? '-') ?></td>
          <td class="px-6 py-3 text-slate-400 text-xs"><?= format_date($r['start_datetime']) ?> → <?= format_date($r['end_datetime']) ?></td>
          <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-[11px] font-medium <?= status_badge($r['status']) ?>"><?= status_label($r['status']) ?></span></td>
          <td class="px-6 py-3 text-right font-semibold text-navy tnum"><?= money($r['total_amount']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($reservations)): ?><tr><td class="px-6 py-8 text-center text-slate-400">Sin reservas</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>

  <!-- RIGHT: pricing, docs, activity -->
  <div class="space-y-5">
    <div class="card p-6">
      <p class="text-[13px] text-slate-400">Precio diario</p>
      <p class="text-3xl font-extrabold text-navy tnum mt-1"><?= money($v['daily_price']) ?></p>
      <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
        <div class="bg-paper rounded-xl p-3"><p class="text-xs text-slate-400">Semanal</p><p class="font-semibold text-navy tnum"><?= $v['weekly_price']?money($v['weekly_price']):'—' ?></p></div>
        <div class="bg-paper rounded-xl p-3"><p class="text-xs text-slate-400">Mensual</p><p class="font-semibold text-navy tnum"><?= $v['monthly_price']?money($v['monthly_price']):'—' ?></p></div>
        <div class="bg-paper rounded-xl p-3"><p class="text-xs text-slate-400">Depósito</p><p class="font-semibold text-navy tnum"><?= money($v['deposit_amount']) ?></p></div>
        <div class="bg-paper rounded-xl p-3"><p class="text-xs text-slate-400">Seguro/día</p><p class="font-semibold text-navy tnum"><?= money($v['insurance_price']) ?></p></div>
      </div>
    </div>

    <div class="card p-6">
      <h2 class="font-display font-bold text-navy mb-3">Resumen</h2>
      <div class="space-y-2.5 text-sm">
        <div class="flex justify-between"><span class="text-slate-500">Contratos</span><span class="font-semibold text-navy tnum"><?= $stats['rentals'] ?></span></div>
        <div class="flex justify-between"><span class="text-slate-500">Ingresos generados</span><span class="font-semibold text-emerald-600 tnum"><?= money($stats['revenue']) ?></span></div>
        <div class="flex justify-between"><span class="text-slate-500">Costo mantenimiento</span><span class="font-semibold text-brand tnum"><?= money($stats['maint_cost']) ?></span></div>
      </div>
    </div>

    <div class="card p-6">
      <h2 class="font-display font-bold text-navy mb-3">Documentos</h2>
      <div class="space-y-2.5">
        <?php foreach ($docs as $d): $exp=$d[1]; $cls='text-slate-400'; $lbl='Sin fecha';
          if ($exp){ $days=(strtotime($exp)-time())/86400; $lbl=format_date($exp); $cls = $days<0?'text-red-600':($days<=30?'text-amber-600':'text-emerald-600'); } ?>
        <div class="flex items-center justify-between text-sm">
          <span class="text-slate-500"><?= $d[0] ?></span>
          <span class="font-medium tnum <?= $cls ?>"><?= $lbl ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="px-5 py-3.5 border-b hairline font-display font-bold text-navy text-[15px]">Mantenimiento</div>
      <div class="divide-y hairline">
        <?php $types=['oil'=>'Aceite','tires'=>'Gomas','brakes'=>'Frenos','battery'=>'Batería','alignment'=>'Alineación','mechanical'=>'Mecánica','deep_clean'=>'Limpieza','paint'=>'Pintura','inspection'=>'Inspección','other'=>'Otro'];
        foreach ($maintenance as $m): ?>
        <div class="px-5 py-3 flex items-center justify-between">
          <div><p class="text-sm font-medium text-navy"><?= $types[$m['maintenance_type']] ?? $m['maintenance_type'] ?></p><p class="text-xs text-slate-400"><?= format_date($m['start_date']) ?></p></div>
          <span class="font-semibold text-navy tnum text-sm"><?= money($m['cost']) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($maintenance)): ?><div class="px-5 py-6 text-center text-sm text-slate-400">Sin registros</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>
