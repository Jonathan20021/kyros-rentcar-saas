<?php $canEdit = can('locations.edit'); $canDelete = can('locations.delete'); ?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Sucursales</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400">Gestiona tus ubicaciones y la flotilla asignada a cada una</p>
  </div>
  <?php if (can('locations.create')): ?>
  <a href="<?= url('/admin/locations/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nueva sucursal</a>
  <?php endif; ?>
</div>

<?php if (empty($locations)): ?>
<div class="card p-12 text-center">
  <div class="w-14 h-14 rounded-2xl bg-brand/10 text-brand grid place-items-center mx-auto mb-4"><i data-lucide="map-pin" class="w-7 h-7"></i></div>
  <p class="font-display font-bold text-navy dark:text-white text-lg">Aún no tienes sucursales</p>
  <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Crea sucursales para organizar tu flotilla y tu equipo por ubicación.</p>
  <?php if (can('locations.create')): ?><a href="<?= url('/admin/locations/create') ?>" class="k-btn k-btn-grad mt-5"><i data-lucide="plus" class="w-4 h-4"></i> Crear la primera</a><?php endif; ?>
</div>
<?php else: ?>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
  <?php foreach ($locations as $l): $active = $l['status']==='active'; ?>
  <div class="card p-5 reveal group">
    <div class="flex items-start justify-between gap-3">
      <div class="w-11 h-11 rounded-xl grid place-items-center shrink-0 <?= $active ? 'bg-brand/10 text-brand' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' ?>"><i data-lucide="map-pin" class="w-5 h-5"></i></div>
      <?php if ($active): ?><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 text-xs font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activa</span>
      <?php else: ?><span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs font-semibold">Inactiva</span><?php endif; ?>
    </div>
    <h3 class="font-display font-bold text-navy dark:text-white mt-4 text-[17px]"><?= e($l['name']) ?></h3>
    <?php if (!empty($l['address'])): ?><p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5 flex items-start gap-1.5"><i data-lucide="navigation" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-slate-400"></i> <?= e($l['address']) ?></p><?php endif; ?>
    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-slate-500 dark:text-slate-400">
      <?php if (!empty($l['phone'])): ?><span class="flex items-center gap-1.5"><i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i> <?= e($l['phone']) ?></span><?php endif; ?>
      <?php if (!empty($l['manager_name'])): ?><span class="flex items-center gap-1.5"><i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i> <?= e($l['manager_name']) ?></span><?php endif; ?>
    </div>

    <div class="grid grid-cols-2 gap-2.5 mt-4">
      <div class="rounded-xl bg-paper dark:bg-slate-800/50 p-3">
        <p class="text-[22px] leading-none font-extrabold text-navy dark:text-white tnum"><?= (int)$l['vehicle_count'] ?></p>
        <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1"><i data-lucide="car" class="w-3 h-3"></i> Vehículos</p>
      </div>
      <div class="rounded-xl bg-paper dark:bg-slate-800/50 p-3">
        <p class="text-[22px] leading-none font-extrabold text-navy dark:text-white tnum"><?= (int)$l['staff_count'] ?></p>
        <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1"><i data-lucide="users" class="w-3 h-3"></i> Equipo</p>
      </div>
    </div>

    <div class="flex items-center gap-2 mt-4 pt-4 border-t hairline">
      <a href="<?= url('/admin/vehicles?location_id='.$l['id']) ?>" class="text-xs font-medium text-slate-500 hover:text-brand flex items-center gap-1"><i data-lucide="list" class="w-3.5 h-3.5"></i> Ver flotilla</a>
      <div class="ml-auto flex items-center gap-1">
        <?php if ($canEdit): ?><a href="<?= url('/admin/locations/edit/'.$l['id']) ?>" class="p-2 rounded-lg hover:bg-paper dark:hover:bg-slate-800 text-slate-400 hover:text-navy dark:hover:text-white" title="Editar"><i data-lucide="pencil" class="w-4 h-4"></i></a><?php endif; ?>
        <?php if ($canDelete): ?>
        <form method="POST" action="<?= url('/admin/locations/delete/'.$l['id']) ?>" data-confirm="Los vehículos asignados a esta sucursal quedarán sin sucursal." data-confirm-title="¿Eliminar sucursal?">
          <?= csrf_field() ?>
          <button class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-slate-400 hover:text-red-600" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
