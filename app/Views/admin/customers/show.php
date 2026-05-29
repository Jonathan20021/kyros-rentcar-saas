<?php $c=$customer; $name=trim($c['first_name'].' '.$c['last_name']);
$riskMap=['low'=>'bg-emerald-100 text-emerald-700','medium'=>'bg-amber-100 text-amber-700','high'=>'bg-red-100 text-brand']; ?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div class="flex items-center gap-4">
    <div class="w-14 h-14 rounded-2xl bg-navy text-white grid place-items-center text-lg font-bold"><?= e(initials($name)) ?></div>
    <div>
      <div class="flex items-center gap-2">
        <h1 class="font-display text-2xl font-bold text-navy"><?= e($name) ?></h1>
        <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($c['status']) ?>"><?= status_label($c['status']) ?></span>
      </div>
      <p class="text-sm text-slate-500 mt-0.5"><?= e(ucfirst($c['document_type'])) ?>: <?= e($c['document_number'] ?? '-') ?></p>
    </div>
  </div>
  <div class="flex gap-2">
    <?php if (can('reservations.create') && !in_array($c['status'],['blacklist','blocked'],true)): ?><a href="<?= url('/admin/reservations/create') ?>" class="k-btn k-btn-grad"><i data-lucide="calendar-plus" class="w-4 h-4"></i> Nueva reserva</a><?php endif; ?>
    <?php if (can('customers.edit')): ?><a href="<?= url('/admin/customers/edit/'.$c['id']) ?>" class="k-btn k-btn-outline"><i data-lucide="pencil" class="w-4 h-4"></i> Editar</a><?php endif; ?>
    <?php if (can('customers.delete')): ?>
      <form method="POST" action="<?= url('/admin/customers/delete/'.$c['id']) ?>" class="inline"
            data-confirm="Si tiene historial, se conservará. Esta acción es reversible desde la base de datos."
            data-confirm-title="¿Eliminar cliente <?= e($name) ?>?">
        <?= csrf_field() ?>
        <button class="k-btn k-btn-outline !text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar</button>
      </form>
    <?php endif; ?>
    <a href="<?= url('/admin/customers') ?>" class="k-btn k-btn-ghost">Volver</a>
  </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <?php foreach ([['Reservas',$stats['reservations'],'calendar-check','bg-indigo-50 text-indigo-600',true],['Contratos',$stats['contracts'],'file-text','bg-navy/5 text-navy',true],['Pagado',money($stats['paid']),'dollar-sign','bg-emerald-50 text-emerald-600',false],['Balance',money($stats['balance']),'wallet','bg-red-50 text-brand',false]] as $k): ?>
  <div class="card p-5">
    <div class="flex items-center gap-2.5"><div class="w-9 h-9 rounded-xl grid place-items-center <?= $k[3] ?>"><i data-lucide="<?= $k[2] ?>" class="w-[18px] h-[18px]"></i></div><p class="text-[13px] text-slate-400 font-medium"><?= $k[0] ?></p></div>
    <p class="mt-3 text-[22px] leading-none font-extrabold text-navy tnum"<?= $k[4]?' data-count="'.(int)$k[1].'"':'' ?>><?= $k[4]?'0':e($k[1]) ?></p>
  </div>
  <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-3 gap-5">
  <!-- Profile -->
  <div class="card p-6 h-fit">
    <h2 class="font-display font-bold text-navy mb-4">Información</h2>
    <div class="space-y-3 text-sm">
      <?php foreach ([['Teléfono',$c['phone']],['WhatsApp',$c['whatsapp']],['Email',$c['email']],['Dirección',$c['address']],['Nacionalidad',$c['nationality']],['Licencia',$c['license_number']],['Vence licencia',$c['license_expiration']?format_date($c['license_expiration']):null]] as $row): ?>
      <div class="flex justify-between gap-3"><span class="text-slate-400"><?= $row[0] ?></span><span class="font-medium text-navy text-right"><?= e($row[1] ?: '—') ?></span></div>
      <?php endforeach; ?>
      <div class="flex justify-between gap-3 pt-3 border-t hairline"><span class="text-slate-400">Riesgo</span><span class="px-2 py-0.5 rounded-full text-[11px] font-semibold <?= $riskMap[$c['risk_level']]??'' ?>"><?= ucfirst($c['risk_level']) ?></span></div>
    </div>
    <?php if (!empty($c['notes'])): ?><div class="mt-4 p-3 rounded-xl bg-paper text-sm text-slate-600"><?= e($c['notes']) ?></div><?php endif; ?>
  </div>

  <div class="lg:col-span-2 space-y-5">
    <!-- Reservations -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b hairline font-display font-bold text-navy">Reservas</div>
      <table class="w-full text-sm"><tbody class="divide-y hairline">
        <?php foreach ($reservations as $r): ?>
        <tr class="hover:bg-paper">
          <td class="px-6 py-3 font-mono text-xs text-brand"><?= e($r['reservation_code']) ?></td>
          <td class="px-6 py-3 text-slate-600"><?= e($r['brand'].' '.$r['model']) ?></td>
          <td class="px-6 py-3 text-slate-400 text-xs"><?= format_date($r['start_datetime']) ?></td>
          <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-[11px] font-medium <?= status_badge($r['status']) ?>"><?= status_label($r['status']) ?></span></td>
          <td class="px-6 py-3 text-right font-semibold text-navy tnum"><?= money($r['total_amount']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($reservations)): ?><tr><td class="px-6 py-8 text-center text-slate-400">Sin reservas</td></tr><?php endif; ?>
      </tbody></table>
    </div>
    <!-- Payments -->
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b hairline font-display font-bold text-navy">Pagos</div>
      <table class="w-full text-sm"><tbody class="divide-y hairline">
        <?php foreach ($payments as $p): ?>
        <tr class="hover:bg-paper">
          <td class="px-6 py-3 font-mono text-xs text-brand"><?= e($p['payment_code']) ?></td>
          <td class="px-6 py-3 text-slate-500"><?= ucfirst($p['method']) ?></td>
          <td class="px-6 py-3 text-slate-400 text-xs"><?= format_date($p['payment_date']) ?></td>
          <td class="px-6 py-3 text-right font-semibold text-emerald-600 tnum"><?= money($p['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($payments)): ?><tr><td class="px-6 py-8 text-center text-slate-400">Sin pagos</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>
</div>
