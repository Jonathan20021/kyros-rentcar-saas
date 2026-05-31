<?php use App\Services\StorageService; ?>
<div class="max-w-7xl mx-auto">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
    <div>
      <h1 class="font-display text-2xl font-extrabold text-navy dark:text-white tracking-tight">Aprobaciones</h1>
      <p class="text-[13px] text-slate-500 mt-1">Activaciones de empresas nuevas y solicitudes de almacenamiento extra.</p>
    </div>
  </div>

  <!-- KPI row -->
  <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-6">
    <div class="card p-4 sm:p-5">
      <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 grid place-items-center mb-2.5"><i data-lucide="building-2" class="w-4 h-4"></i></div>
      <p class="font-display text-2xl font-extrabold text-navy dark:text-white tnum"><?= (int)$stats['pending_tenants'] ?></p>
      <p class="text-[12px] text-slate-500 mt-0.5">Empresas pendientes</p>
    </div>
    <div class="card p-4 sm:p-5">
      <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand grid place-items-center mb-2.5"><i data-lucide="hard-drive-upload" class="w-4 h-4"></i></div>
      <p class="font-display text-2xl font-extrabold text-navy dark:text-white tnum"><?= (int)$stats['pending_storage'] ?></p>
      <p class="text-[12px] text-slate-500 mt-0.5">Solicitudes de storage</p>
    </div>
    <div class="card p-4 sm:p-5">
      <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center mb-2.5"><i data-lucide="check-circle-2" class="w-4 h-4"></i></div>
      <p class="font-display text-2xl font-extrabold text-navy dark:text-white tnum"><?= (int)$stats['approved_today'] ?></p>
      <p class="text-[12px] text-slate-500 mt-0.5">Aprobadas hoy</p>
    </div>
  </div>

  <!-- TENANT ACTIVATIONS -->
  <div class="card p-5 sm:p-7 mb-6">
    <div class="flex items-center gap-3 mb-5">
      <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 grid place-items-center"><i data-lucide="building-2" class="w-5 h-5"></i></div>
      <div>
        <h2 class="font-display font-bold text-navy dark:text-white text-lg">Empresas pendientes de activación</h2>
        <p class="text-[12px] text-slate-500">Registros nuevos esperando aprobación.</p>
      </div>
    </div>

    <?php if (empty($pendingTenants)): ?>
      <div class="rounded-2xl border-2 border-dashed border-slate-200 p-10 text-center">
        <div class="inline-flex w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 items-center justify-center"><i data-lucide="check" class="w-6 h-6"></i></div>
        <p class="text-slate-500 font-medium mt-3">Sin solicitudes pendientes</p>
      </div>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($pendingTenants as $t): ?>
        <div class="rounded-2xl border hairline p-5 hover:border-brand/40 transition">
          <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <div class="flex items-start gap-4 flex-1 min-w-0">
              <div class="w-12 h-12 rounded-2xl grad-bg grid place-items-center text-white font-extrabold text-lg shrink-0"><?= e(strtoupper(mb_substr($t['name'], 0, 1))) ?></div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="font-display font-bold text-navy text-[15.5px]"><?= e($t['name']) ?></p>
                  <span class="text-[10.5px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">Pendiente</span>
                  <?php if (!empty($t['plan_name'])): ?>
                    <span class="text-[10.5px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"><?= e($t['plan_name']) ?> · <?= number_format((int)$t['storage_mb']) ?> MB</span>
                  <?php endif; ?>
                </div>
                <p class="text-[12px] text-slate-500 mt-0.5 truncate"><?= e($t['email']) ?> <?php if (!empty($t['phone'])): ?>· <?= e($t['phone']) ?><?php endif; ?></p>
                <p class="text-[11px] text-slate-400 mt-0.5 tnum">Solicitada el <?= e(date('d/m/Y H:i', strtotime($t['created_at']))) ?> · slug <code class="text-slate-500"><?= e($t['slug']) ?></code></p>
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <form method="POST" action="<?= url('/super-admin/approvals/tenant/reject/' . $t['id']) ?>"
                    data-confirm="Rechazar y eliminar esta empresa? Esta acción no se puede deshacer." data-confirm-variant="danger">
                <?= csrf_field() ?>
                <button class="k-btn k-btn-outline !h-10 !px-4 !text-red-600 hover:!bg-red-50"><i data-lucide="x" class="w-4 h-4"></i> Rechazar</button>
              </form>
              <form method="POST" action="<?= url('/super-admin/approvals/tenant/approve/' . $t['id']) ?>">
                <?= csrf_field() ?>
                <button class="k-btn k-btn-grad !h-10 !px-4"><i data-lucide="check" class="w-4 h-4"></i> Aprobar</button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- STORAGE REQUESTS -->
  <div class="card p-5 sm:p-7">
    <div class="flex items-center gap-3 mb-5">
      <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand grid place-items-center"><i data-lucide="hard-drive-upload" class="w-5 h-5"></i></div>
      <div>
        <h2 class="font-display font-bold text-navy dark:text-white text-lg">Solicitudes de almacenamiento extra</h2>
        <p class="text-[12px] text-slate-500">Tenants que necesitan más espacio del incluido en su plan.</p>
      </div>
    </div>

    <?php if (empty($requests)): ?>
      <div class="rounded-2xl border-2 border-dashed border-slate-200 p-10 text-center">
        <div class="inline-flex w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 items-center justify-center"><i data-lucide="hard-drive" class="w-6 h-6"></i></div>
        <p class="text-slate-500 font-medium mt-3">Sin solicitudes pendientes</p>
      </div>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach ($requests as $r):
          $snap = $r['snapshot'];
          $levelColor = $snap['level']==='block'?'bg-red-500':($snap['level']==='warn'?'bg-amber-500':'bg-emerald-500');
        ?>
        <div class="rounded-2xl border hairline p-5 hover:border-brand/40 transition" x-data="{open:false}">
          <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <div class="flex items-start gap-4 flex-1 min-w-0">
              <div class="w-12 h-12 rounded-2xl bg-brand/10 text-brand grid place-items-center shrink-0"><i data-lucide="database" class="w-5 h-5"></i></div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="font-display font-bold text-navy text-[15.5px]"><?= e($r['tenant_name']) ?></p>
                  <span class="text-[10.5px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">Pendiente</span>
                  <span class="text-[10.5px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"><?= e($r['plan_name'] ?? '—') ?></span>
                </div>
                <p class="text-[12.5px] text-slate-600 mt-1">
                  Pide <span class="font-bold text-brand tnum">+<?= number_format((int)$r['requested_mb']) ?> MB</span>
                  · Uso actual <span class="font-semibold tnum"><?= e($snap['used_human']) ?> / <?= e($snap['quota_human']) ?> (<?= $snap['percent'] ?>%)</span>
                </p>
                <div class="mt-2 h-1.5 max-w-md rounded-full bg-slate-100 overflow-hidden">
                  <div class="h-full <?= $levelColor ?>" style="width: <?= max(2, $snap['percent']) ?>%"></div>
                </div>
                <?php if (!empty($r['reason'])): ?>
                  <p class="text-[12px] text-slate-500 mt-2 italic">"<?= e($r['reason']) ?>"</p>
                <?php endif; ?>
                <p class="text-[11px] text-slate-400 mt-1 tnum">Enviada <?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></p>
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <button @click="open=!open" class="k-btn k-btn-outline !h-10 !px-4 !text-red-600 hover:!bg-red-50" x-show="!open"><i data-lucide="x" class="w-4 h-4"></i> Rechazar</button>
              <button @click="open=!open" class="k-btn k-btn-grad !h-10 !px-4" x-show="!open"><i data-lucide="check" class="w-4 h-4"></i> Aprobar</button>
            </div>
          </div>
          <!-- Decision panel (inline) -->
          <div x-show="open" x-cloak x-transition class="mt-4 pt-4 border-t hairline grid sm:grid-cols-2 gap-4">
            <form method="POST" action="<?= url('/super-admin/approvals/storage/approve/' . $r['id']) ?>" class="space-y-2">
              <?= csrf_field() ?>
              <label class="text-[11.5px] font-medium text-slate-500 block">Aprobar — MB a otorgar</label>
              <input type="number" name="granted_mb" min="1" max="100000" value="<?= (int)$r['requested_mb'] ?>" class="fld !text-[13px]">
              <input type="text" name="note" maxlength="200" placeholder="Nota interna (opcional)" class="fld !text-[12px]">
              <button class="k-btn k-btn-grad w-full !h-10"><i data-lucide="check" class="w-4 h-4"></i> Aprobar</button>
            </form>
            <form method="POST" action="<?= url('/super-admin/approvals/storage/reject/' . $r['id']) ?>" class="space-y-2">
              <?= csrf_field() ?>
              <label class="text-[11.5px] font-medium text-slate-500 block">Rechazar — motivo (se muestra al tenant)</label>
              <textarea name="note" rows="3" maxlength="500" class="fld !text-[12px]" placeholder="Ej: Tu uso actual no justifica este aumento. Borra archivos antiguos primero."></textarea>
              <button class="k-btn k-btn-outline w-full !h-10 !text-red-600 hover:!bg-red-50"><i data-lucide="x" class="w-4 h-4"></i> Rechazar solicitud</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
