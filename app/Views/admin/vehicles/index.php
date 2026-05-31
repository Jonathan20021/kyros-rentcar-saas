<?php
$sc = $statusCounts;
$kpis = [
  ['Total', array_sum($sc), 'car', 'bg-navy/5 text-navy'],
  ['Disponibles', $sc['available'], 'circle-check-big', 'bg-emerald-50 text-emerald-600'],
  ['Rentados', $sc['rented'], 'key-round', 'bg-indigo-50 text-indigo-600'],
  ['Mantenimiento', $sc['maintenance'], 'wrench', 'bg-amber-50 text-amber-600'],
];
?>
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Flotilla</h1>
    <p class="text-sm text-slate-500"><?= count($vehicles) ?> vehículos · <?= $sc['available'] ?> disponibles</p>
  </div>
  <?php if (can('vehicles.create')): ?>
  <a href="<?= url('/admin/vehicles/create') ?>" class="k-btn k-btn-grad"><i data-lucide="plus" class="w-4 h-4"></i> Nuevo vehículo</a>
  <?php endif; ?>
</div>

<!-- KPI strip -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
  <?php foreach ($kpis as $k): ?>
  <div class="card p-4 reveal">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl grid place-items-center <?= $k[3] ?>"><i data-lucide="<?= $k[2] ?>" class="w-[18px] h-[18px]"></i></div>
      <p class="text-[13px] text-slate-400 font-medium"><?= $k[0] ?></p>
    </div>
    <p class="mt-2 text-[24px] leading-none font-extrabold text-navy dark:text-white tnum" data-count="<?= (int)$k[1] ?>">0</p>
  </div>
  <?php endforeach; ?>
</div>

<div x-data="{view: localStorage.getItem('kyros-units-view') || 'grid', set(v){ this.view=v; localStorage.setItem('kyros-units-view',v); }}">
  <!-- Filters + toggle -->
  <form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[180px]">
      <label class="block text-xs font-medium text-slate-500 mb-1">Buscar</label>
      <input name="search" value="<?= e($filters['search']) ?>" placeholder="Marca, modelo o placa" class="fld !h-10">
    </div>
    <div class="min-w-[150px]">
      <label class="block text-xs font-medium text-slate-500 mb-1">Categoría</label>
      <select name="category_id" class="fld !h-10">
        <option value="">Todas</option>
        <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($filters['category_id']==$c['id'])?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="min-w-[150px]">
      <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
      <select name="status" class="fld !h-10">
        <option value="">Todos</option>
        <?php foreach (['available','reserved','rented','maintenance','cleaning','out_of_service'] as $s): ?><option value="<?= $s ?>" <?= ($filters['status']===$s)?'selected':'' ?>><?= status_label($s) ?></option><?php endforeach; ?>
      </select>
    </div>
    <?php if (!empty($locations)): ?>
    <div class="min-w-[150px]">
      <label class="block text-xs font-medium text-slate-500 mb-1">Sucursal</label>
      <select name="location_id" class="fld !h-10">
        <option value="">Todas</option>
        <?php foreach ($locations as $loc): ?><option value="<?= $loc['id'] ?>" <?= (($filters['location_id'] ?? 0)==$loc['id'])?'selected':'' ?>><?= e($loc['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <button class="k-btn k-btn-dark !h-10">Filtrar</button>
    <a href="<?= url('/admin/vehicles') ?>" class="k-btn k-btn-outline !h-10">Limpiar</a>
    <div class="ml-auto flex items-center gap-1 p-1 rounded-xl bg-paper border hairline">
      <button type="button" @click="set('grid')" :class="view==='grid'?'bg-white shadow-xs text-navy':'text-slate-400'" class="w-9 h-8 rounded-lg grid place-items-center transition"><i data-lucide="layout-grid" class="w-4 h-4"></i></button>
      <button type="button" @click="set('list')" :class="view==='list'?'bg-white shadow-xs text-navy':'text-slate-400'" class="w-9 h-8 rounded-lg grid place-items-center transition"><i data-lucide="list" class="w-4 h-4"></i></button>
    </div>
  </form>

  <?php if (empty($vehicles)): ?>
    <div class="card p-16 text-center">
      <div class="w-14 h-14 rounded-2xl bg-paper grid place-items-center mx-auto"><i data-lucide="car" class="w-7 h-7 text-slate-300"></i></div>
      <h3 class="font-semibold text-navy mt-4">No hay vehículos</h3>
      <p class="text-sm text-slate-400 mt-1">Agrega tu primer vehículo a la flotilla.</p>
      <?php if (can('vehicles.create')): ?><a href="<?= url('/admin/vehicles/create') ?>" class="k-btn k-btn-grad mt-4"><i data-lucide="plus" class="w-4 h-4"></i> Nuevo vehículo</a><?php endif; ?>
    </div>
  <?php else: ?>

  <!-- GRID VIEW -->
  <div x-show="view==='grid'" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    <?php foreach ($vehicles as $v): ?>
    <div class="card overflow-hidden group reveal-s">
      <a href="<?= url('/admin/vehicles/show/'.$v['id']) ?>" class="relative aspect-[16/10] bg-paper grid place-items-center overflow-hidden">
        <?php if (!empty($v['main_image'])): ?>
          <img src="<?= e(media($v['main_image'])) ?>" alt="<?= e($v['brand'].' '.$v['model']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        <?php else: ?><div class="text-slate-200"><i data-lucide="car" class="w-14 h-14"></i></div><?php endif; ?>
        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge($v['status']) ?>"><?= status_label($v['status']) ?></span>
        <?php if ($v['is_featured']): ?><span class="absolute top-3 right-3 px-2 py-1 rounded-full text-[11px] font-bold bg-amber-400 text-amber-900">★</span><?php endif; ?>
      </a>
      <div class="p-4">
        <h3 class="font-display font-bold text-navy dark:text-white leading-tight"><a href="<?= url('/admin/vehicles/show/'.$v['id']) ?>" class="hover:text-brand transition"><?= e($v['brand'].' '.$v['model']) ?></a></h3>
        <p class="text-xs text-slate-400 mt-0.5"><?= e($v['year']) ?> · <?= e($v['category_name'] ?? 'Sin categoría') ?> · <?= e($v['plate_number'] ?? 's/placa') ?></p>
        <?php if (!empty($v['location_name'])): ?><p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3 text-brand/70"></i><?= e($v['location_name']) ?></p><?php endif; ?>
        <div class="flex items-center gap-3 mt-3 text-xs text-slate-500">
          <span class="flex items-center gap-1"><i data-lucide="users" class="w-3.5 h-3.5"></i><?= $v['passengers'] ?></span>
          <span class="flex items-center gap-1"><i data-lucide="cog" class="w-3.5 h-3.5"></i><?= $v['transmission']==='automatic'?'Auto':'Manual' ?></span>
          <span class="flex items-center gap-1"><i data-lucide="fuel" class="w-3.5 h-3.5"></i><?= ucfirst($v['fuel_type']) ?></span>
        </div>
        <div class="flex items-center justify-between mt-4 pt-3 border-t hairline">
          <p class="text-lg font-extrabold text-navy tnum"><?= money($v['daily_price']) ?><span class="text-xs font-normal text-slate-400">/día</span></p>
          <div class="flex items-center gap-1">
            <?php if (can('reservations.create') && $v['status']==='available'): ?><a href="<?= url('/admin/reservations/create?vehicle='.$v['id']) ?>" class="px-3 h-8 grid place-items-center rounded-lg bg-brand text-white text-xs font-semibold hover:opacity-90">Reservar</a><?php endif; ?>
            <?php if (can('vehicles.edit')): ?><a href="<?= url('/admin/vehicles/edit/'.$v['id']) ?>" class="icon-btn !w-8 !h-8" title="Editar"><i data-lucide="pencil" class="w-4 h-4"></i></a><?php endif; ?>
            <?php if (can('vehicles.delete')): ?><form method="POST" action="<?= url('/admin/vehicles/delete/'.$v['id']) ?>" data-confirm="Esta acción es permanente. El historial de reservas y contratos se conservará." data-confirm-title="¿Eliminar vehículo?"><?= csrf_field() ?><button class="icon-btn !w-8 !h-8 hover:!text-brand" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- LIST VIEW -->
  <div x-show="view==='list'" x-cloak class="card overflow-hidden divide-y hairline">
    <?php foreach ($vehicles as $v): ?>
    <div class="flex items-center gap-4 p-3.5 hover:bg-paper transition">
      <div class="w-24 h-16 rounded-xl bg-paper grid place-items-center overflow-hidden shrink-0">
        <?php if (!empty($v['main_image'])): ?><img src="<?= e(media($v['main_image'])) ?>" class="w-full h-full object-cover" alt="<?= e($v['brand'].' '.$v['model']) ?>"><?php else: ?><i data-lucide="car" class="w-7 h-7 text-slate-200"></i><?php endif; ?>
      </div>
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
          <h3 class="font-display font-bold text-navy dark:text-white truncate"><a href="<?= url('/admin/vehicles/show/'.$v['id']) ?>" class="hover:text-brand transition"><?= e($v['brand'].' '.$v['model']) ?></a></h3>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-medium <?= status_badge($v['status']) ?>"><?= status_label($v['status']) ?></span>
        </div>
        <p class="text-xs text-slate-400 mt-0.5"><?= e($v['category_name'] ?? '') ?> · <?= e($v['plate_number'] ?? 's/placa') ?> · <?= e($v['year']) ?><?php if (!empty($v['location_name'])): ?> · <span class="text-brand/70"><?= e($v['location_name']) ?></span><?php endif; ?></p>
      </div>
      <div class="hidden md:flex items-center gap-6 text-xs text-slate-500 shrink-0">
        <span class="flex items-center gap-1.5"><i data-lucide="cog" class="w-4 h-4 text-slate-300"></i><?= $v['transmission']==='automatic'?'Automática':'Manual' ?></span>
        <span class="flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4 text-slate-300"></i><?= $v['passengers'] ?> asientos</span>
        <span class="flex items-center gap-1.5"><i data-lucide="fuel" class="w-4 h-4 text-slate-300"></i><?= ucfirst($v['fuel_type']) ?></span>
      </div>
      <div class="text-right shrink-0 w-24">
        <p class="text-lg font-extrabold text-navy tnum"><?= money($v['daily_price']) ?></p>
        <p class="text-[11px] text-slate-400">por día</p>
      </div>
      <div class="flex items-center gap-1.5 shrink-0">
        <?php if (can('reservations.create') && $v['status']==='available'): ?><a href="<?= url('/admin/reservations/create?vehicle='.$v['id']) ?>" class="k-btn k-btn-grad !h-9 !px-4">Reservar</a><?php endif; ?>
        <?php if (can('vehicles.edit')): ?><a href="<?= url('/admin/vehicles/edit/'.$v['id']) ?>" class="icon-btn !w-9 !h-9" title="Editar"><i data-lucide="pencil" class="w-4 h-4"></i></a><?php endif; ?>
        <?php if (can('vehicles.delete')): ?><form method="POST" action="<?= url('/admin/vehicles/delete/'.$v['id']) ?>" data-confirm="Esta acción es permanente. El historial de reservas y contratos se conservará." data-confirm-title="¿Eliminar vehículo?"><?= csrf_field() ?><button class="icon-btn !w-9 !h-9 hover:!text-brand" title="Eliminar"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
