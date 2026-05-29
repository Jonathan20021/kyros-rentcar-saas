<?php
$stBadge=['expired'=>'bg-red-100 text-brand','soon'=>'bg-amber-100 text-amber-700','valid'=>'bg-emerald-100 text-emerald-700'];
$stLabel=['expired'=>'Vencido','soon'=>'Por vencer','valid'=>'Vigente'];
?>
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white">Vencimientos de documentos</h1>
    <p class="text-sm text-slate-500">Seguros, marbete, matrícula, inspección y licencias</p>
  </div>
</div>

<!-- KPIs as filters -->
<div class="grid grid-cols-3 gap-4 mb-5">
  <?php foreach ([['expired','Vencidos','alert-octagon','bg-red-50 text-brand'],['soon','Por vencer (30d)','clock','bg-amber-50 text-amber-600'],['valid','Vigentes','shield-check','bg-emerald-50 text-emerald-600']] as $k): ?>
  <a href="<?= url('/admin/documents'.($filter===$k[0]?'':'?status='.$k[0])) ?>" class="card p-5 transition hover:shadow-soft <?= $filter===$k[0]?'ring-2 ring-brand/40':'' ?>">
    <div class="flex items-center gap-2.5"><div class="w-9 h-9 rounded-xl grid place-items-center <?= $k[3] ?>"><i data-lucide="<?= $k[2] ?>" class="w-[18px] h-[18px]"></i></div><p class="text-[13px] text-slate-400 font-medium"><?= $k[1] ?></p></div>
    <p class="mt-2 text-[24px] leading-none font-extrabold text-navy dark:text-white tnum" data-count="<?= (int)$counts[$k[0]] ?>">0</p>
  </a>
  <?php endforeach; ?>
</div>

<?php if ($filter): ?><div class="mb-4"><a href="<?= url('/admin/documents') ?>" class="text-sm font-medium text-brand hover:underline">&larr; Ver todos</a></div><?php endif; ?>

<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-400 bg-paper"><tr><th class="px-6 py-3 font-medium">Documento</th><th class="px-6 py-3 font-medium">Pertenece a</th><th class="px-6 py-3 font-medium">Vence</th><th class="px-6 py-3 font-medium">Restante</th><th class="px-6 py-3 font-medium">Estado</th><th class="px-6 py-3 font-medium text-right"></th></tr></thead>
      <tbody class="divide-y hairline">
        <?php foreach ($rows as $r): ?>
        <tr class="hover:bg-paper">
          <td class="px-6 py-3 font-medium text-navy"><span class="inline-flex items-center gap-2"><i data-lucide="<?= $r['kind']==='customer'?'id-card':'file-text' ?>" class="w-4 h-4 text-slate-300"></i><?= e($r['doc']) ?></span></td>
          <td class="px-6 py-3 text-slate-500"><?= e($r['entity']) ?></td>
          <td class="px-6 py-3 text-slate-500 tnum"><?= format_date($r['date']) ?></td>
          <td class="px-6 py-3 tnum <?= $r['days']<0?'text-brand font-semibold':'text-slate-500' ?>"><?= $r['days']<0 ? abs($r['days']).' días vencido' : $r['days'].' días' ?></td>
          <td class="px-6 py-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $stBadge[$r['status']] ?>"><?= $stLabel[$r['status']] ?></span></td>
          <td class="px-6 py-3 text-right"><a href="<?= e($r['url']) ?>" class="icon-btn !w-8 !h-8 inline-grid" title="Abrir"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?><tr><td colspan="6" class="px-6 py-12 text-center text-slate-400"><i data-lucide="calendar-check" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>Sin documentos<?= $filter?' en este estado':'' ?></p></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
