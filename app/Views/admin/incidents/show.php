<?php
use App\Models\Incident;
$i = $incident;
$typeLabels = [
  'traffic_fine'=>'Multa de tránsito','exterior_damage'=>'Daño exterior','interior_damage'=>'Daño interior',
  'accident'=>'Accidente','theft'=>'Robo','late'=>'Devolución tardía','fuel'=>'Combustible',
  'cleaning'=>'Limpieza','key_loss'=>'Pérdida de llave','other'=>'Otro',
];
$statusLabels = ['open'=>'Abierta','review'=>'En revisión','charged'=>'Cobrada','cancelled'=>'Cancelada','closed'=>'Cerrada'];
$statusColors = [
  'open'=>'bg-amber-50 text-amber-600','review'=>'bg-indigo-50 text-indigo-600',
  'charged'=>'bg-emerald-50 text-emerald-600','cancelled'=>'bg-slate-100 text-slate-500','closed'=>'bg-slate-100 text-slate-600',
];
$evidence = $i['evidence_files'] ? (json_decode($i['evidence_files'], true) ?: []) : [];
?>
<div class="max-w-4xl mx-auto">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
      <div class="flex items-center gap-3 flex-wrap">
        <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Incidencia #<?= (int)$i['id'] ?></h1>
        <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $statusColors[$i['status']] ?>"><?= e($statusLabels[$i['status']] ?? $i['status']) ?></span>
      </div>
      <p class="text-sm text-slate-500 mt-1">
        Tipo: <b class="text-navy dark:text-white"><?= e($typeLabels[$i['type']] ?? $i['type']) ?></b>
        · creada <?= format_datetime($i['created_at']) ?>
        <?php if (!empty($i['created_by_name'])): ?>por <b class="text-navy dark:text-white"><?= e($i['created_by_name']) ?></b><?php endif; ?>
      </p>
    </div>
    <div class="flex gap-2 flex-wrap">
      <?php if (can('incidents.edit')): ?>
        <?php foreach (['review'=>'En revisión','charged'=>'Cobrada','cancelled'=>'Cancelar','closed'=>'Cerrar'] as $st => $lbl):
          if ($st === $i['status']) continue; ?>
          <form method="POST" action="<?= url('/admin/incidents/status/'.$i['id']) ?>" class="inline">
            <?= csrf_field() ?><input type="hidden" name="status" value="<?= $st ?>">
            <button class="k-btn k-btn-outline !h-9"><?= e($lbl) ?></button>
          </form>
        <?php endforeach; ?>
      <?php endif; ?>
      <a href="<?= url('/admin/incidents') ?>" class="k-btn k-btn-ghost">Volver</a>
    </div>
  </div>

  <div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
      <!-- Details -->
      <div class="card p-6">
        <h2 class="font-display font-bold text-navy dark:text-white mb-4">Detalle</h2>
        <div class="grid sm:grid-cols-2 gap-4 text-sm">
          <?php if (!empty($i['vehicle'])): ?>
          <div>
            <p class="text-slate-400">Vehículo</p>
            <a href="<?= url('/admin/vehicles/show/'.$i['vehicle']['id']) ?>" class="font-medium text-navy dark:text-white hover:text-brand">
              <?= e($i['vehicle']['brand'].' '.$i['vehicle']['model']) ?>
              <?php if (!empty($i['vehicle']['plate_number'])): ?>· <?= e($i['vehicle']['plate_number']) ?><?php endif; ?>
            </a>
          </div>
          <?php endif; ?>
          <?php if (!empty($i['customer'])): ?>
          <div>
            <p class="text-slate-400">Cliente</p>
            <a href="<?= url('/admin/customers/show/'.$i['customer']['id']) ?>" class="font-medium text-navy dark:text-white hover:text-brand">
              <?= e(trim($i['customer']['first_name'].' '.($i['customer']['last_name'] ?? ''))) ?>
            </a>
          </div>
          <?php endif; ?>
          <?php if (!empty($i['contract'])): ?>
          <div>
            <p class="text-slate-400">Contrato</p>
            <a href="<?= url('/admin/contracts/show/'.$i['contract']['id']) ?>" class="font-medium text-navy dark:text-white hover:text-brand tnum">
              <?= e($i['contract']['contract_number']) ?>
            </a>
          </div>
          <?php endif; ?>
          <div>
            <p class="text-slate-400">Monto</p>
            <p class="font-bold text-navy dark:text-white tnum"><?= money($i['amount']) ?></p>
          </div>
        </div>
        <?php if (!empty($i['description'])): ?>
        <div class="mt-5 pt-5 border-t hairline">
          <p class="text-xs uppercase tracking-wider font-bold text-slate-400 mb-2">Descripción</p>
          <p class="text-sm text-navy dark:text-slate-200 whitespace-pre-line"><?= e($i['description']) ?></p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Evidence -->
      <?php if (!empty($evidence)): ?>
      <div class="card p-6">
        <h2 class="font-display font-bold text-navy dark:text-white mb-4">Evidencia</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <?php foreach ($evidence as $url): ?>
            <a href="<?= e(media($url)) ?>" target="_blank" class="block rounded-xl overflow-hidden border hairline hover:opacity-90">
              <img src="<?= e(media($url)) ?>" class="w-full h-32 object-cover">
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Sidebar — status workflow -->
    <div class="card p-6 h-fit">
      <h3 class="font-display font-bold text-navy dark:text-white mb-4">Flujo de estado</h3>
      <ol class="space-y-3 text-sm">
        <?php foreach (['open','review','charged','closed'] as $st):
          $isCurrent = $i['status'] === $st;
          $isPast = array_search($i['status'], ['open','review','charged','closed'], true) > array_search($st, ['open','review','charged','closed'], true);
        ?>
        <li class="flex items-center gap-3">
          <span class="w-7 h-7 rounded-full grid place-items-center shrink-0 <?= $isCurrent ? 'bg-brand text-white' : ($isPast ? 'bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-400') ?>">
            <i data-lucide="<?= $isPast ? 'check' : 'circle' ?>" class="w-3.5 h-3.5"></i>
          </span>
          <span class="<?= $isCurrent ? 'font-bold text-navy dark:text-white' : ($isPast ? 'text-slate-500' : 'text-slate-400') ?>">
            <?= e($statusLabels[$st]) ?>
          </span>
        </li>
        <?php endforeach; ?>
      </ol>
      <?php if ($i['status'] === 'cancelled'): ?>
        <p class="mt-4 pt-4 border-t hairline text-xs text-slate-400">Esta incidencia fue cancelada.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
