<?php
use App\Core\View;
$c = $contract;
$customerName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
$signed = !empty($c['signed_at']);
$paidPct = (float)$c['total_amount'] > 0 ? min(100, (int) round((float)$c['paid_amount'] / (float)$c['total_amount'] * 100)) : 0;
$deliveryPhotos = array_values(array_filter($photos, fn($p) => $p['phase'] === 'delivery'));
$returnPhotos   = array_values(array_filter($photos, fn($p) => $p['phase'] === 'return'));
$statusLabel = [
  'draft' => ['Borrador', 'bg-slate-100 text-slate-600'],
  'active'=> ['Activo', 'bg-emerald-50 text-emerald-600 ring-emerald-500/20'],
  'finished' => ['Finalizado', 'bg-indigo-50 text-indigo-600 ring-indigo-500/20'],
  'cancelled'=> ['Cancelado', 'bg-slate-100 text-slate-600 ring-slate-500/20'],
  'overdue'  => ['Atrasado', 'bg-amber-50 text-amber-600 ring-amber-500/20'],
  'claim'    => ['Reclamo', 'bg-red-50 text-red-600 ring-red-500/20'],
];
$st = $statusLabel[$c['status']] ?? ['—', 'bg-slate-100 text-slate-600'];
?>

<div class="doc-shell overflow-hidden">

  <!-- =================== HERO HEADER =================== -->
  <div class="doc-header relative px-6 sm:px-10 py-8 sm:py-10 text-white overflow-hidden">
    <div class="absolute inset-0 opacity-20" style="background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:32px 32px;"></div>
    <div class="relative flex flex-col sm:flex-row sm:items-end justify-between gap-5">
      <div>
        <p class="text-[11px] uppercase tracking-[0.18em] font-bold text-white/70">Contrato de alquiler</p>
        <p class="font-display font-extrabold text-[32px] sm:text-[42px] leading-none tnum mt-1.5"><?= e($c['contract_number']) ?></p>
        <p class="text-sm text-white/75 mt-2 font-medium"><?= e($tenant['name'] ?? '') ?></p>
      </div>
      <div class="flex flex-col items-start sm:items-end gap-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur text-[12px] font-semibold ring-1 ring-white/20"><?= $st[0] ?></span>
        <?php if ($signed): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 backdrop-blur text-emerald-200 text-[12px] font-semibold ring-1 ring-emerald-400/30">
            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Firmado <?= e(date('d/m/Y', strtotime($c['signed_at']))) ?>
          </span>
        <?php else: ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 backdrop-blur text-amber-100 text-[12px] font-semibold ring-1 ring-amber-400/30">
            <i data-lucide="pen-line" class="w-3.5 h-3.5"></i> Pendiente de firma
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- =================== BODY =================== -->
  <div class="p-6 sm:p-10 space-y-8">

    <!-- Summary cards -->
    <div class="grid sm:grid-cols-3 gap-3 sm:gap-4">
      <div class="rounded-2xl border hairline p-5">
        <div class="flex items-center gap-2.5 text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">
          <i data-lucide="user" class="w-4 h-4"></i> Cliente
        </div>
        <p class="font-display font-bold text-ink text-[17px] truncate"><?= e($customerName ?: '—') ?></p>
        <p class="text-xs text-slate-500 mt-0.5 tnum"><?= e($customer['document_number'] ?? '—') ?></p>
      </div>

      <div class="rounded-2xl border hairline p-5">
        <div class="flex items-center gap-2.5 text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">
          <i data-lucide="car" class="w-4 h-4"></i> Vehículo
        </div>
        <p class="font-display font-bold text-ink text-[17px] truncate"><?= e(($vehicle['brand'] ?? '') . ' ' . ($vehicle['model'] ?? '')) ?></p>
        <p class="text-xs text-slate-500 mt-0.5">
          <?php if (!empty($vehicle['year'])): ?><?= e($vehicle['year']) ?> · <?php endif; ?>
          <?= e($vehicle['plate_number'] ?? 'sin placa') ?>
        </p>
      </div>

      <div class="rounded-2xl border hairline p-5">
        <div class="flex items-center gap-2.5 text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2">
          <i data-lucide="calendar-range" class="w-4 h-4"></i> Período
        </div>
        <p class="font-display font-bold text-ink text-[17px] tnum"><?= format_date($c['start_datetime']) ?></p>
        <p class="text-xs text-slate-500 mt-0.5 tnum">→ <?= format_date($c['end_datetime']) ?></p>
      </div>
    </div>

    <!-- Pickup / Return locations -->
    <?php if ($reservation && ($reservation['pickup_location'] || $reservation['return_location'])): ?>
    <div class="grid sm:grid-cols-2 gap-3 sm:gap-4">
      <?php if ($reservation['pickup_location']): ?>
      <div class="rounded-2xl border hairline p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-2"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>Lugar de entrega</p>
        <p class="text-sm text-ink"><?= e($reservation['pickup_location']) ?></p>
      </div>
      <?php endif; ?>
      <?php if ($reservation['return_location']): ?>
      <div class="rounded-2xl border hairline p-5">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-2"><i data-lucide="map-pin-off" class="w-3.5 h-3.5"></i>Lugar de devolución</p>
        <p class="text-sm text-ink"><?= e($reservation['return_location']) ?></p>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Vehicle handover state -->
    <?php if ($c['start_mileage'] !== null || $c['start_fuel_level'] !== null): ?>
    <div class="rounded-2xl border hairline p-5">
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2"><i data-lucide="gauge" class="w-3.5 h-3.5"></i>Estado de entrega</p>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
        <?php if ($c['start_mileage'] !== null): ?>
        <div>
          <p class="text-slate-400 text-[11px]">Kilometraje salida</p>
          <p class="font-bold text-ink tnum"><?= number_format((int) $c['start_mileage']) ?> km</p>
        </div>
        <?php endif; ?>
        <?php if ($c['start_fuel_level'] !== null): ?>
        <div>
          <p class="text-slate-400 text-[11px]">Combustible salida</p>
          <div class="flex items-center gap-2">
            <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
              <div class="h-full rounded-full" style="width:<?= (int)$c['start_fuel_level'] ?>%; background:var(--brand)"></div>
            </div>
            <span class="font-bold text-ink tnum text-[13px]"><?= (int)$c['start_fuel_level'] ?>%</span>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($c['end_mileage'] !== null): ?>
        <div>
          <p class="text-slate-400 text-[11px]">Kilometraje llegada</p>
          <p class="font-bold text-ink tnum"><?= number_format((int) $c['end_mileage']) ?> km</p>
        </div>
        <?php endif; ?>
        <?php if ($c['end_fuel_level'] !== null): ?>
        <div>
          <p class="text-slate-400 text-[11px]">Combustible llegada</p>
          <div class="flex items-center gap-2">
            <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
              <div class="h-full rounded-full" style="width:<?= (int)$c['end_fuel_level'] ?>%; background:#10B981"></div>
            </div>
            <span class="font-bold text-ink tnum text-[13px]"><?= (int)$c['end_fuel_level'] ?>%</span>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Delivery photos -->
    <?php if (!empty($deliveryPhotos)): ?>
    <div>
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2"><i data-lucide="camera" class="w-3.5 h-3.5"></i>Fotos de entrega (<?= count($deliveryPhotos) ?>)</p>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <?php foreach ($deliveryPhotos as $ph): ?>
        <a href="<?= e(media($ph['path'])) ?>" target="_blank" class="block rounded-xl overflow-hidden border hairline hover:opacity-90 transition aspect-square">
          <img src="<?= e(media($ph['path'])) ?>" class="w-full h-full object-cover">
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Return photos -->
    <?php if (!empty($returnPhotos)): ?>
    <div>
      <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2"><i data-lucide="camera-off" class="w-3.5 h-3.5"></i>Fotos de devolución (<?= count($returnPhotos) ?>)</p>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <?php foreach ($returnPhotos as $ph): ?>
        <a href="<?= e(media($ph['path'])) ?>" target="_blank" class="block rounded-xl overflow-hidden border hairline hover:opacity-90 transition aspect-square">
          <img src="<?= e(media($ph['path'])) ?>" class="w-full h-full object-cover">
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Pricing breakdown -->
    <div class="rounded-2xl border hairline overflow-hidden">
      <div class="px-5 py-4 border-b hairline bg-paper">
        <p class="font-display font-bold text-ink">Detalle de precio</p>
      </div>
      <div class="divide-y hairline text-sm">
        <div class="flex justify-between px-5 py-3">
          <span class="text-slate-500">Tarifa diaria</span>
          <span class="font-medium tnum text-ink"><?= money($c['daily_rate']) ?></span>
        </div>
        <div class="flex justify-between px-5 py-3">
          <span class="text-slate-500">Subtotal</span>
          <span class="font-medium tnum text-ink"><?= money($c['subtotal']) ?></span>
        </div>
        <?php if ((float)$c['extras_total'] > 0): ?>
        <div class="flex justify-between px-5 py-3">
          <span class="text-slate-500">Extras</span>
          <span class="font-medium tnum text-ink"><?= money($c['extras_total']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($c['discount_amount']) && (float)$c['discount_amount'] > 0): ?>
        <div class="flex justify-between px-5 py-3 text-emerald-600">
          <span class="flex items-center gap-1"><i data-lucide="ticket-percent" class="w-3.5 h-3.5"></i>Descuento</span>
          <span class="font-medium tnum">-<?= money($c['discount_amount']) ?></span>
        </div>
        <?php endif; ?>
        <div class="flex justify-between px-5 py-3">
          <span class="text-slate-500">Impuesto</span>
          <span class="font-medium tnum text-ink"><?= money($c['tax_amount']) ?></span>
        </div>
        <?php if ((float)$c['penalties_total'] > 0): ?>
        <div class="flex justify-between px-5 py-3 text-amber-600">
          <span>Penalidades / cargos</span>
          <span class="font-medium tnum">+ <?= money($c['penalties_total']) ?></span>
        </div>
        <?php endif; ?>
        <div class="flex justify-between px-5 py-3 text-slate-400 text-xs">
          <span>Depósito (reembolsable)</span>
          <span class="tnum"><?= money($c['deposit_amount']) ?></span>
        </div>
        <div class="flex justify-between px-5 py-4 bg-paper">
          <span class="font-bold text-ink">Total contrato</span>
          <span class="font-extrabold text-ink tnum text-lg"><?= money($c['total_amount']) ?></span>
        </div>
        <div class="flex justify-between px-5 py-3 text-emerald-600">
          <span>Pagado</span>
          <span class="font-semibold tnum">- <?= money($c['paid_amount']) ?></span>
        </div>
        <div class="flex justify-between px-5 py-4" style="background:color-mix(in srgb, var(--brand) 8%, white);">
          <span class="font-bold" style="color:var(--brand)">Balance pendiente</span>
          <span class="font-extrabold tnum text-lg" style="color:var(--brand)"><?= money($c['balance_due']) ?></span>
        </div>
      </div>
    </div>

    <!-- Payments -->
    <?php if (!empty($payments)): ?>
    <div class="rounded-2xl border hairline overflow-hidden">
      <div class="px-5 py-4 border-b hairline bg-paper flex items-center justify-between">
        <p class="font-display font-bold text-ink">Historial de pagos</p>
        <span class="text-xs text-slate-400"><?= count($payments) ?> registros</span>
      </div>
      <div class="divide-y hairline">
        <?php $methods = ['cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta','paypal'=>'PayPal','stripe'=>'Stripe','azul'=>'Azul','cardnet'=>'CardNet','other'=>'Otro'];
        foreach ($payments as $p): ?>
        <div class="flex items-center justify-between px-5 py-3.5">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 grid place-items-center shrink-0">
              <i data-lucide="check" class="w-4 h-4"></i>
            </div>
            <div class="min-w-0">
              <p class="font-mono text-xs font-semibold text-ink truncate"><?= e($p['payment_code']) ?></p>
              <p class="text-[11px] text-slate-400"><?= $methods[$p['method']] ?? $p['method'] ?> · <?= format_date($p['payment_date']) ?></p>
            </div>
          </div>
          <span class="font-bold text-ink tnum"><?= money($p['amount']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Terms -->
    <?php if (!empty($c['terms'])): ?>
    <div class="rounded-2xl border hairline p-5 sm:p-6">
      <p class="font-display font-bold text-ink mb-3 flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>Términos y condiciones</p>
      <div class="prose prose-sm max-w-none text-slate-600 whitespace-pre-line"><?= e($c['terms']) ?></div>
    </div>
    <?php endif; ?>

    <!-- =================== SIGNATURE BLOCK =================== -->
    <div class="rounded-2xl border-2 p-5 sm:p-7"
         style="<?= $signed ? 'border-color:#10B981; background:linear-gradient(180deg,#ECFDF5,#fff);' : 'border-color:color-mix(in srgb,var(--brand) 25%, transparent); background:linear-gradient(180deg, color-mix(in srgb,var(--brand) 4%, white), #fff);' ?>">

      <?php if ($signed): ?>
        <!-- Already signed -->
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white grid place-items-center shrink-0">
            <i data-lucide="check" class="w-6 h-6"></i>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-display font-extrabold text-ink text-xl">Contrato firmado</p>
            <p class="text-sm text-slate-500 mt-1">
              Firmado el <b class="text-ink tnum"><?= e(date('d/m/Y H:i', strtotime($c['signed_at']))) ?></b>
              por <b class="text-ink"><?= e($customerName ?: '—') ?></b>.
            </p>
            <?php if (!empty($c['customer_signature'])): ?>
            <div class="mt-4 inline-block rounded-xl bg-white border hairline p-3 shadow-sm">
              <img src="<?= e(media($c['customer_signature'])) ?>" alt="Firma del cliente" class="max-h-28">
            </div>
            <?php endif; ?>
          </div>
        </div>

      <?php else: ?>
        <!-- Signature pad -->
        <div x-data="sigPad()" x-init="init()">
          <div class="flex items-start gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl grid place-items-center shrink-0" style="background:color-mix(in srgb,var(--brand) 12%,transparent); color:var(--brand)">
              <i data-lucide="pen-line" class="w-5 h-5"></i>
            </div>
            <div>
              <p class="font-display font-extrabold text-ink text-xl">Firma del cliente</p>
              <p class="text-sm text-slate-500 mt-1">Al firmar, aceptas los términos del contrato y confirmas que la información es correcta.</p>
            </div>
          </div>

          <!-- Canvas has FIXED internal resolution (1200×320) so drawing always works,
               independent of layout timing. CSS controls display size. -->
          <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white p-3 relative">
            <canvas x-ref="cv" width="1200" height="320"
                    class="block rounded-xl bg-white touch-none cursor-crosshair"
                    style="width:100%; height:200px; display:block;"></canvas>
            <div x-show="empty" x-cloak class="absolute inset-0 pointer-events-none grid place-items-center text-slate-400 text-[14px] font-medium select-none">
              ✍️ Firma aquí con el mouse o tu dedo
            </div>
            <!-- baseline guide line -->
            <div class="pointer-events-none absolute left-6 right-6 bottom-6 border-b-2 border-dashed border-slate-200"></div>
          </div>

          <form method="POST" action="<?= url('/contrato/'.$token.'/firmar') ?>" class="mt-5 flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-center" @submit.prevent="submit($event)">
            <?= csrf_field() ?>
            <input type="hidden" name="signature" x-ref="sig">
            <button type="button" @click="clear()" class="k-btn k-btn-outline !h-11" :disabled="empty">
              <i data-lucide="eraser" class="w-4 h-4"></i> Limpiar
            </button>
            <button type="submit" class="k-btn k-btn-grad !h-11 flex-1" :disabled="empty"
                    :class="empty ? 'opacity-50 cursor-not-allowed' : ''">
              <i data-lucide="check-circle-2" class="w-4 h-4"></i> Firmar y enviar
            </button>
          </form>

          <p class="mt-4 text-[11.5px] text-slate-400 leading-relaxed">
            Tu firma se registra junto con la fecha, hora y dirección IP de esta sesión para fines de auditoría.
            Una vez firmado, el contrato queda confirmado y no puede modificarse desde este enlace.
          </p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php View::push('scripts', "<script>
/**
 * Signature pad — fixed internal resolution 1200×320. Each pen-down session is
 * tracked as an array of {x,y} points so the final signature can be serialized
 * to a single SVG path. SVG renders natively in dompdf (no GD needed), which
 * is essential on hosts where the GD extension isn't available.
 */
function sigPad(){
  return {
    empty:true, drawing:false, _ctx:null, _strokes:[], _current:null,
    init(){
      const cv = this.\$refs.cv;
      const ctx = cv.getContext('2d');
      ctx.lineWidth = 4.5;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.strokeStyle = '#0E1422';
      this._ctx = ctx;

      const pt = e => {
        const r = cv.getBoundingClientRect();
        const t = (e.touches && e.touches[0]) || e;
        const sx = cv.width  / Math.max(1, r.width);
        const sy = cv.height / Math.max(1, r.height);
        return { x: (t.clientX - r.left) * sx, y: (t.clientY - r.top) * sy };
      };

      const start = e => {
        e.preventDefault();
        this.drawing = true;
        const p = pt(e);
        this._current = [p];
        // Tiny dot for taps that don't move
        ctx.beginPath();
        ctx.arc(p.x, p.y, ctx.lineWidth / 2.2, 0, Math.PI * 2);
        ctx.fillStyle = ctx.strokeStyle;
        ctx.fill();
        this.empty = false;
      };
      const move = e => {
        if (!this.drawing) return;
        e.preventDefault();
        const p = pt(e);
        const last = this._current[this._current.length - 1];
        ctx.beginPath();
        ctx.moveTo(last.x, last.y);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        this._current.push(p);
        this.empty = false;
      };
      const end = () => {
        if (this._current && this._current.length) this._strokes.push(this._current);
        this.drawing = false;
        this._current = null;
      };

      cv.addEventListener('mousedown', start);
      window.addEventListener('mousemove', move);
      window.addEventListener('mouseup', end);
      cv.addEventListener('touchstart', start, { passive:false });
      cv.addEventListener('touchmove',  move,  { passive:false });
      cv.addEventListener('touchend',   end);
    },
    clear(){
      const cv = this.\$refs.cv;
      this._ctx.clearRect(0, 0, cv.width, cv.height);
      this._strokes = [];
      this._current = null;
      this.empty = true;
    },
    buildSvg(){
      const cv = this.\$refs.cv;
      const W = cv.width, H = cv.height;
      const round = n => Math.round(n * 10) / 10;
      const paths = this._strokes.map(stroke => {
        if (!stroke.length) return '';
        if (stroke.length === 1) {
          // Single tap → tiny circle
          return '<circle cx=\"'+round(stroke[0].x)+'\" cy=\"'+round(stroke[0].y)+'\" r=\"2.2\" fill=\"#0E1422\"/>';
        }
        const d = stroke.map((p, i) => (i === 0 ? 'M' : 'L') + round(p.x) + ' ' + round(p.y)).join(' ');
        return '<path d=\"'+d+'\" fill=\"none\" stroke=\"#0E1422\" stroke-width=\"4.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>';
      }).join('');
      return '<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 '+W+' '+H+'\" width=\"'+W+'\" height=\"'+H+'\">'
           + '<rect width=\"'+W+'\" height=\"'+H+'\" fill=\"#FFFFFF\"/>' + paths + '</svg>';
    },
    submit(ev){
      if (this.empty || !this._strokes.length) return;
      const svg = this.buildSvg();
      if (!svg || svg.length < 200){
        alert('No se pudo capturar tu firma. Vuelve a firmar e intenta de nuevo.');
        return;
      }
      this.\$refs.sig.value = svg;
      ev.target.submit();
    }
  };
}
</script>"); ?>
