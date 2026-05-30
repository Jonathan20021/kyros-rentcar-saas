<?php
function sval($t,$k,$d=''){ return e($t[$k] ?? $d); }
$publicUrl = rtrim(\App\Core\Config::get('app.url'),'/').'/r/'.$tenant['slug'];
?>
<div class="max-w-3xl mx-auto" x-data="{tab:'general'}">
  <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1">Configuración</h1>
  <p class="text-sm text-slate-500 mb-6">Datos de tu empresa y página pública</p>

  <!-- Public URL banner -->
  <div class="card p-4 mb-5 flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
      <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand grid place-items-center shrink-0"><i data-lucide="link" class="w-5 h-5"></i></div>
      <div class="min-w-0"><p class="text-sm font-medium text-navy">Tu página pública</p><a href="<?= url('/r/'.$tenant['slug']) ?>" target="_blank" class="text-sm text-brand hover:underline truncate block"><?= e($publicUrl) ?></a></div>
    </div>
    <a href="<?= url('/r/'.$tenant['slug']) ?>" target="_blank" class="k-btn k-btn-outline shrink-0"><i data-lucide="external-link" class="w-4 h-4"></i> Ver</a>
  </div>

  <!-- Tabs -->
  <div class="flex gap-1 p-1 rounded-xl bg-paper border hairline w-fit mb-5">
    <?php foreach (['general'=>'General','brand'=>'Marca','billing'=>'Facturación'] as $k=>$lbl): ?>
    <button type="button" @click="tab='<?= $k ?>'" :class="tab==='<?= $k ?>'?'bg-white shadow-xs text-navy':'text-slate-500'" class="px-4 py-2 rounded-lg text-sm font-semibold transition"><?= $lbl ?></button>
    <?php endforeach; ?>
  </div>

  <form method="POST" action="<?= url('/admin/settings') ?>" enctype="multipart/form-data" class="space-y-5" id="settingsForm">
    <?= csrf_field() ?>

    <!-- General -->
    <div x-show="tab==='general'" class="card p-6">
      <h2 class="font-display font-bold text-navy mb-4">Información general</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Nombre comercial *</label><input name="name" required value="<?= sval($tenant,'name') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Razón social</label><input name="legal_name" value="<?= sval($tenant,'legal_name') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Email</label><input type="email" name="email" value="<?= sval($tenant,'email') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Teléfono</label><input name="phone" value="<?= sval($tenant,'phone') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">WhatsApp</label><input name="whatsapp" value="<?= sval($tenant,'whatsapp') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">RNC</label><input name="rnc" value="<?= sval($tenant,'rnc') ?>" class="fld"></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Dirección</label><input name="address" value="<?= sval($tenant,'address') ?>" class="fld"></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Descripción</label><textarea name="description" rows="3" class="fld"><?= sval($tenant,'description') ?></textarea></div>
      </div>
    </div>

    <!-- Brand -->
    <div x-show="tab==='brand'" x-cloak class="card p-6">
      <h2 class="font-display font-bold text-navy mb-4">Identidad de marca</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Color primario</label><input type="color" name="primary_color" value="<?= sval($tenant,'primary_color','#F23645') ?>" class="w-full h-11 rounded-xl border hairline cursor-pointer"></div>
        <div><label class="block text-sm font-medium mb-1.5">Color secundario</label><input type="color" name="secondary_color" value="<?= sval($tenant,'secondary_color','#1C2433') ?>" class="w-full h-11 rounded-xl border hairline cursor-pointer"></div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Logo</label>
          <input type="file" name="logo" accept="image/svg+xml,image/png,image/jpeg,image/webp" class="fld">
          <p class="text-[11px] text-slate-400 mt-1">SVG recomendado para máxima nitidez en contratos PDF.</p>
          <?php if(!empty($tenant['logo'])): ?><img src="<?= e(media($tenant['logo'])) ?>" class="mt-2 h-10"><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Imagen de portada</label>
          <input type="file" name="cover_image" accept="image/png,image/jpeg,image/webp" class="fld">
          <?php if(!empty($tenant['cover_image'])): ?><img src="<?= e(media($tenant['cover_image'])) ?>" class="mt-2 h-16 w-full object-cover rounded-lg"><?php endif; ?>
        </div>
      </div>
      <p class="text-xs text-slate-400 mt-3">Estos colores y logo se aplican en tu página pública de reservas y en los contratos PDF.</p>
    </div>

    <!-- Billing -->
    <div x-show="tab==='billing'" x-cloak class="card p-6">
      <h2 class="font-display font-bold text-navy mb-4">Facturación y moneda</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Moneda</label><input name="currency" value="<?= sval($tenant,'currency','DOP') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Impuesto (%)</label><input type="number" step="0.01" name="tax_rate" value="<?= sval($tenant,'tax_rate','18') ?>" class="fld"></div>
      </div>
      <p class="text-xs text-slate-400 mt-3">El impuesto se aplica automáticamente al calcular reservas y contratos.</p>
    </div>

    <div class="flex items-center gap-3">
      <button type="submit" class="k-btn k-btn-grad !px-6">Guardar configuración</button>
    </div>
  </form>
</div>
<?php \App\Core\View::push('scripts', '<script>
/**
 * Before the settings form submits, raster image inputs (PNG/WEBP/GIF/BMP)
 * are re-encoded as JPEG via Canvas. dompdf cannot render PNG/WEBP without
 * the GD extension, but JPEG renders natively via addJpegFromFile — so we
 * normalize the upload at the browser layer where Canvas is always present.
 * SVG and JPEG pass through unchanged.
 */
(function(){
  var form = document.getElementById("settingsForm");
  if (!form) return;
  var rasterInputs = form.querySelectorAll("input[type=\"file\"][name=\"logo\"], input[type=\"file\"][name=\"cover_image\"]");
  if (!rasterInputs.length) return;

  function needsConvert(file){
    return /^image\/(png|webp|gif|bmp)$/i.test(file.type);
  }

  function fileToJpegFile(file, quality){
    return new Promise(function(resolve, reject){
      var reader = new FileReader();
      reader.onload = function(e){
        var img = new Image();
        img.onload = function(){
          var cw = img.naturalWidth, ch = img.naturalHeight;
          // Cap dimensions so the JPEG stays reasonable for a logo / cover.
          var MAX = 1600;
          if (cw > MAX || ch > MAX){
            var s = Math.min(MAX/cw, MAX/ch);
            cw = Math.round(cw*s); ch = Math.round(ch*s);
          }
          var canvas = document.createElement("canvas");
          canvas.width = cw; canvas.height = ch;
          var ctx = canvas.getContext("2d");
          // White background — JPEGs have no alpha, so flatten transparency.
          ctx.fillStyle = "#FFFFFF";
          ctx.fillRect(0,0,cw,ch);
          ctx.drawImage(img, 0, 0, cw, ch);
          canvas.toBlob(function(blob){
            if (!blob) return reject(new Error("toBlob failed"));
            var newName = (file.name||"image").replace(/\.[a-z0-9]+$/i, "") + ".jpg";
            try {
              resolve(new File([blob], newName, { type: "image/jpeg" }));
            } catch (e) {
              // Safari fallback: blob with a name shim
              blob.name = newName;
              resolve(blob);
            }
          }, "image/jpeg", quality || 0.92);
        };
        img.onerror = reject;
        img.src = e.target.result;
      };
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  }

  var converting = false;
  form.addEventListener("submit", function(ev){
    if (converting) return; // already in flight from a previous click
    var tasks = [];
    rasterInputs.forEach(function(inp){
      if (!inp.files || !inp.files.length) return;
      var f = inp.files[0];
      if (needsConvert(f)) {
        tasks.push(fileToJpegFile(f, 0.92).then(function(jpg){
          var dt = new DataTransfer();
          dt.items.add(jpg);
          inp.files = dt.files;
        }));
      }
    });
    if (!tasks.length) return;
    ev.preventDefault();
    converting = true;
    Promise.all(tasks).then(function(){
      form.submit();
    }).catch(function(err){
      converting = false;
      console.error("Logo conversion failed:", err);
      // Fall back to letting the original file through.
      form.submit();
    });
  });
})();
</script>'); ?>
