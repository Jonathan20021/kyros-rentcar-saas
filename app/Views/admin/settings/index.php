<?php
use App\Services\LocaleService;
function sval($t,$k,$d=''){ return e($t[$k] ?? $d); }
$publicUrl = rtrim(\App\Core\Config::get('app.url'),'/').'/r/'.$tenant['slug'];
$curCode = strtoupper($tenant['currency'] ?? 'DOP');
$curMeta = LocaleService::currencyMeta($curCode);
$currencyOptions = LocaleService::currencyOptions();
$tabs = ['general'=>['General','building-2'], 'brand'=>['Marca','palette'], 'billing'=>['Facturación','receipt']];
?>
<div class="max-w-3xl mx-auto" x-data="{tab:'general'}">
  <div class="mb-6">
    <h1 class="font-display text-2xl font-bold text-navy dark:text-white mb-1">Configuración</h1>
    <p class="text-sm text-slate-500">Datos de tu empresa, marca y facturación · se reflejan en tu página pública.</p>
  </div>

  <!-- Public URL banner -->
  <div class="card p-4 mb-5 flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
      <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand grid place-items-center shrink-0"><i data-lucide="link" class="w-5 h-5"></i></div>
      <div class="min-w-0"><p class="text-sm font-medium text-navy dark:text-white">Tu página pública</p><a href="<?= url('/r/'.$tenant['slug']) ?>" target="_blank" class="text-sm text-brand hover:underline truncate block"><?= e($publicUrl) ?></a></div>
    </div>
    <a href="<?= url('/r/'.$tenant['slug']) ?>" target="_blank" class="k-btn k-btn-outline shrink-0"><i data-lucide="external-link" class="w-4 h-4"></i> Ver</a>
  </div>

  <!-- Tabs -->
  <div class="flex gap-1 p-1 rounded-2xl bg-paper border hairline w-full sm:w-fit mb-5">
    <?php foreach ($tabs as $k=>$meta): ?>
    <button type="button" @click="tab='<?= $k ?>'" :class="tab==='<?= $k ?>'?'bg-white shadow-xs text-navy':'text-slate-500 hover:text-navy'"
            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition">
      <i data-lucide="<?= $meta[1] ?>" class="w-4 h-4"></i> <?= $meta[0] ?>
    </button>
    <?php endforeach; ?>
  </div>

  <form method="POST" action="<?= url('/admin/settings') ?>" enctype="multipart/form-data" class="space-y-5" id="settingsForm">
    <?= csrf_field() ?>

    <!-- ===================== GENERAL ===================== -->
    <div x-show="tab==='general'" class="card p-6">
      <h2 class="font-display font-bold text-navy dark:text-white mb-1 flex items-center gap-2"><i data-lucide="building-2" class="w-4 h-4 text-brand"></i> Información general</h2>
      <p class="text-xs text-slate-400 mb-5">Nombre y datos de contacto que verán tus clientes.</p>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-medium mb-1.5">Nombre comercial *</label><input name="name" required value="<?= sval($tenant,'name') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Razón social</label><input name="legal_name" value="<?= sval($tenant,'legal_name') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Email</label><input type="email" name="email" value="<?= sval($tenant,'email') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">Teléfono</label><input name="phone" value="<?= sval($tenant,'phone') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5">WhatsApp</label><input name="whatsapp" value="<?= sval($tenant,'whatsapp') ?>" class="fld"></div>
        <div><label class="block text-sm font-medium mb-1.5"><?= e($tenant['tax_id_label'] ?? 'RNC') ?></label><input name="rnc" value="<?= sval($tenant,'rnc') ?>" class="fld"></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Dirección</label><input name="address" value="<?= sval($tenant,'address') ?>" class="fld"></div>
        <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1.5">Descripción</label><textarea name="description" rows="3" class="fld" placeholder="Una línea que describa tu rent car (aparece en el hero de tu página pública)."><?= sval($tenant,'description') ?></textarea></div>
      </div>
    </div>

    <!-- ===================== MARCA ===================== -->
    <div x-show="tab==='brand'" x-cloak class="card p-6"
         x-data="{ p:'<?= sval($tenant,'primary_color','#F23645') ?>', s:'<?= sval($tenant,'secondary_color','#1C2433') ?>' }"
         x-init="$nextTick(()=>window.lucide&&lucide.createIcons())">
      <h2 class="font-display font-bold text-navy dark:text-white mb-1 flex items-center gap-2"><i data-lucide="palette" class="w-4 h-4 text-brand"></i> Identidad de marca</h2>
      <p class="text-xs text-slate-400 mb-5">El color de acento y el logo se aplican a tu página pública y a los contratos PDF.</p>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1.5">Color primario (acento)</label>
          <div class="flex items-center gap-2">
            <input type="color" name="primary_color" x-model="p" class="h-11 w-14 rounded-xl border hairline cursor-pointer shrink-0">
            <input type="text" x-model="p" class="fld font-mono tnum uppercase">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Color secundario</label>
          <div class="flex items-center gap-2">
            <input type="color" name="secondary_color" x-model="s" class="h-11 w-14 rounded-xl border hairline cursor-pointer shrink-0">
            <input type="text" x-model="s" class="fld font-mono tnum uppercase">
          </div>
        </div>
      </div>

      <!-- Live accent preview (dark, like the public storefront) -->
      <div class="mt-4 rounded-2xl p-5 border hairline" style="background:#0f0f10">
        <p class="text-[11px] font-bold uppercase tracking-[.22em]" :style="`color:${p}`">Vista previa</p>
        <p class="mt-2 font-display text-xl font-extrabold text-white">Renta el auto correcto. <span :style="`color:${p}`">Conduce la experiencia.</span></p>
        <div class="mt-4 flex flex-wrap items-center gap-2.5">
          <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold" :style="`background:${p};color:#0a0a0a`">Reservar</span>
          <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold text-white border" style="border-color:#3a3a3a">WhatsApp</span>
          <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border" :style="`color:${p};border-color:${p}55;background:${p}1a`">Premium</span>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-4 mt-5">
        <div>
          <label class="block text-sm font-medium mb-1.5">Logo</label>
          <input type="file" name="logo" accept="image/svg+xml,image/png,image/jpeg,image/webp" class="fld">
          <p class="text-[11px] text-slate-400 mt-1">SVG recomendado para máxima nitidez en contratos PDF.</p>
          <?php if(!empty($tenant['logo'])): ?><img src="<?= e(media($tenant['logo'])) ?>" class="mt-2 h-10"><?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1.5">Imagen de portada</label>
          <input type="file" name="cover_image" accept="image/png,image/jpeg,image/webp" class="fld">
          <p class="text-[11px] text-slate-400 mt-1">Fondo del hero de tu página pública (paisaje, 1600px+).</p>
          <?php if(!empty($tenant['cover_image'])): ?><img src="<?= e(media($tenant['cover_image'])) ?>" class="mt-2 h-16 w-full object-cover rounded-lg"><?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ===================== FACTURACIÓN ===================== -->
    <div x-show="tab==='billing'" x-cloak class="card p-6"
         x-data="localeBox('<?= e($tenant['country'] ?? 'DO') ?>', '<?= e($curCode) ?>', <?= htmlspecialchars(json_encode($currencyOptions), ENT_QUOTES) ?>)"
         x-init="$nextTick(()=>window.lucide&&lucide.createIcons())">
      <h2 class="font-display font-bold text-navy dark:text-white mb-1 flex items-center gap-2"><i data-lucide="receipt" class="w-4 h-4 text-brand"></i> País, facturación y moneda</h2>
      <p class="text-xs text-slate-400 mb-5">La moneda re-etiqueta y reformatea todos los precios de tu catálogo. No convierte montos.</p>

      <!-- Country picker — drives the dependent defaults below -->
      <label class="block text-sm font-medium mb-2">País de operación</label>
      <div class="grid sm:grid-cols-2 gap-3 mb-5">
        <?php foreach ([
          'DO' => ['República Dominicana', '🇩🇴', 'ITBIS 18 % · DOP · RNC · Marbete'],
          'CO' => ['Colombia',              '🇨🇴', 'IVA 19 % · COP · NIT · SOAT'],
        ] as $code => $info): ?>
          <label class="relative cursor-pointer">
            <input type="radio" name="country" value="<?= $code ?>" x-model="country" @change="applyCountry()"
                   class="peer sr-only" <?= ($tenant['country'] ?? 'DO') === $code ? 'checked' : '' ?>>
            <div class="p-4 rounded-2xl border-2 hairline transition peer-checked:border-brand peer-checked:bg-brand/[0.04]">
              <div class="flex items-center gap-3">
                <span class="text-2xl"><?= $info[1] ?></span>
                <div class="flex-1 min-w-0">
                  <p class="font-display font-bold text-navy dark:text-white text-[14.5px] truncate"><?= e($info[0]) ?></p>
                  <p class="text-[11.5px] text-slate-500 dark:text-slate-400 mt-0.5 truncate"><?= e($info[2]) ?></p>
                </div>
                <i data-lucide="check-circle-2" class="w-5 h-5 text-brand opacity-0 peer-checked:opacity-100"></i>
              </div>
            </div>
          </label>
        <?php endforeach; ?>
      </div>

      <!-- Currency combobox (searchable, all currencies) -->
      <div class="grid sm:grid-cols-2 gap-4">
        <div class="relative" @click.away="currencyOpen=false">
          <label class="block text-sm font-medium mb-1.5" id="currencyLabel">Moneda del catálogo</label>
          <input type="hidden" name="currency" :value="currency">
          <button type="button" @click="toggleCurrency()" @keydown.down.prevent="openAndFocus()"
                  role="combobox" :aria-expanded="currencyOpen" aria-controls="currencyListbox" aria-haspopup="listbox" aria-labelledby="currencyLabel"
                  class="fld flex items-center justify-between gap-2 text-left">
            <span class="flex items-center gap-2 min-w-0">
              <span class="font-mono font-bold text-navy dark:text-white" x-text="currency"></span>
              <span class="text-slate-400 truncate" x-text="meta(currency).name"></span>
            </span>
            <span class="flex items-center gap-1.5 shrink-0">
              <span class="text-slate-500 tnum" x-text="meta(currency).symbol"></span>
              <i data-lucide="chevrons-up-down" class="w-4 h-4 text-slate-400"></i>
            </span>
          </button>
          <div x-show="currencyOpen" x-cloak x-transition.opacity
               class="absolute z-30 mt-1.5 w-full card p-2 shadow-lift max-h-80 overflow-hidden flex flex-col">
            <div class="relative mb-2">
              <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
              <input x-ref="cq" x-model="query" aria-label="Buscar moneda" aria-controls="currencyListbox" :aria-activedescendant="filtered[activeIndex] ? 'cur-'+filtered[activeIndex].code : null"
                     @keydown.escape.stop="currencyOpen=false"
                     @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)"
                     @keydown.enter.prevent="if(filtered.length) pickCurrency(filtered[activeIndex])"
                     placeholder="Buscar por código o nombre…" class="fld !pl-9">
            </div>
            <div id="currencyListbox" role="listbox" aria-labelledby="currencyLabel" class="overflow-y-auto -mx-1 px-1">
              <template x-for="(c,i) in filtered" :key="c.code">
                <div>
                  <p x-show="i===0 || filtered[i-1].group !== c.group" class="px-2 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400" x-text="c.group"></p>
                  <button type="button" :id="'cur-'+c.code" role="option" :aria-selected="c.code===currency"
                          @click="pickCurrency(c)" @mousemove="activeIndex=i"
                          class="w-full text-left px-3 py-2 rounded-lg flex items-center justify-between gap-2 transition"
                          :class="i===activeIndex ? 'bg-brand/10 text-brand' : (c.code===currency ? 'text-brand' : 'text-navy dark:text-white')">
                    <span class="flex items-center gap-2 min-w-0"><span class="font-mono font-semibold w-12 shrink-0" x-text="c.code"></span><span class="truncate text-sm" x-text="c.name"></span></span>
                    <span class="flex items-center gap-1.5 shrink-0"><span class="text-slate-400 tnum" x-text="c.symbol"></span><i data-lucide="check" class="w-3.5 h-3.5" x-show="c.code===currency"></i></span>
                  </button>
                </div>
              </template>
              <p x-show="!filtered.length" class="px-3 py-4 text-sm text-slate-400 text-center">Sin resultados para “<span x-text="query"></span>”.</p>
            </div>
          </div>
        </div>

        <!-- Live money preview -->
        <div>
          <label class="block text-sm font-medium mb-1.5">Vista previa</label>
          <div class="fld !h-auto py-3 flex items-center justify-between bg-paper">
            <div>
              <p class="font-display text-xl font-extrabold text-navy dark:text-white tnum" x-text="previewMoney(1500)"></p>
              <p class="text-[11px] text-slate-400">por día · en tu catálogo</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-white border hairline text-xs font-mono font-bold text-slate-500" x-text="currency"></span>
          </div>
        </div>
      </div>

      <div class="grid sm:grid-cols-3 gap-3 mt-4">
        <div>
          <label class="block text-[12px] font-medium mb-1.5">Impuesto (%)</label>
          <input type="number" step="0.01" name="tax_rate" x-model="taxRate" class="fld tnum">
        </div>
        <div>
          <label class="block text-[12px] font-medium mb-1.5">Etiqueta de impuesto</label>
          <input name="tax_label" x-model="taxLabel" class="fld">
        </div>
        <div>
          <label class="block text-[12px] font-medium mb-1.5">Etiqueta de ID fiscal</label>
          <input name="tax_id_label" x-model="taxIdLabel" class="fld">
        </div>
      </div>

      <div class="mt-4 p-3.5 rounded-xl bg-brand/[0.04] border border-brand/15 text-[12.5px] text-slate-600 dark:text-slate-300 flex items-start gap-2.5">
        <i data-lucide="info" class="w-4 h-4 text-brand mt-0.5 shrink-0"></i>
        <div>
          <p class="font-semibold text-navy dark:text-white" x-text="hint.title"></p>
          <p class="text-slate-500 dark:text-slate-400 mt-0.5" x-text="hint.body"></p>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3 pt-1">
      <button type="submit" class="k-btn k-btn-grad !px-6">Guardar configuración</button>
    </div>
  </form>
</div>
<?php \App\Core\View::push('scripts', '<script>
/**
 * localeBox — country presets + searchable currency picker + live money preview.
 * Switching country snaps the dependent fields to the country defaults; manual
 * edits afterwards survive on submit. Currency is chosen from the full registry.
 */
function localeBox(initialCountry, initialCurrency, currencies){
  var DEFAULTS = {
    DO: { currency:"DOP", taxRate:18, taxLabel:"ITBIS", taxIdLabel:"RNC",
          hint:{ title:"República Dominicana", body:"DGII NCF requerido en facturación legal · marbete e inspección por vehículo · ITBIS 18 %." } },
    CO: { currency:"COP", taxRate:19, taxLabel:"IVA",   taxIdLabel:"NIT",
          hint:{ title:"Colombia",              body:"Resolución DIAN para facturación electrónica · SOAT y tecnomecánica obligatorios · IVA 19 %." } }
  };
  var d0 = DEFAULTS[initialCountry] || DEFAULTS.DO;
  return {
    country: initialCountry || "DO",
    currency: (initialCurrency || d0.currency || "DOP").toUpperCase(),
    currencies: currencies || [],
    currencyOpen: false,
    query: "",
    filtered: currencies || [],   // reactive: recomputed on query change (see init)
    activeIndex: 0,
    taxRate:  d0.taxRate,
    taxLabel: d0.taxLabel,
    taxIdLabel: d0.taxIdLabel,
    hint: d0.hint,
    init(){
      var self = this;
      this.filtered = this.computeFiltered();
      this.$watch("query", function(){ self.filtered = self.computeFiltered(); self.activeIndex = 0; });
    },
    meta(code){
      var c = (this.currencies || []).find(function(x){ return x.code === code; });
      return c || { code: code, name: code, symbol: code + " ", decimals: 2 };
    },
    computeFiltered(){
      var q = (this.query || "").trim().toLowerCase();
      if (!q) return this.currencies;
      return this.currencies.filter(function(c){
        return c.code.toLowerCase().indexOf(q) !== -1 || c.name.toLowerCase().indexOf(q) !== -1;
      });
    },
    openAndFocus(){ if(!this.currencyOpen) this.toggleCurrency(); },
    toggleCurrency(){
      this.currencyOpen = !this.currencyOpen;
      if (this.currencyOpen){
        this.query = ""; this.filtered = this.computeFiltered();
        var idx = this.filtered.findIndex((c)=>c.code===this.currency);
        this.activeIndex = idx >= 0 ? idx : 0;
        var self = this; this.$nextTick(function(){ self.$refs.cq && self.$refs.cq.focus(); window.lucide && lucide.createIcons(); });
      }
    },
    move(dir){
      if (!this.filtered.length) return;
      this.activeIndex = (this.activeIndex + dir + this.filtered.length) % this.filtered.length;
      var el = document.getElementById("cur-" + this.filtered[this.activeIndex].code);
      if (el) el.scrollIntoView({ block: "nearest" });
    },
    pickCurrency(c){ if(!c) return; this.currency = c.code; this.currencyOpen = false; },
    previewMoney(n){
      var m = this.meta(this.currency);
      return m.symbol + " " + Number(n).toLocaleString("en-US", { minimumFractionDigits: m.decimals, maximumFractionDigits: m.decimals });
    },
    applyCountry(){
      var d = DEFAULTS[this.country] || DEFAULTS.DO;
      this.currency = d.currency;
      this.taxRate  = d.taxRate;
      this.taxLabel = d.taxLabel;
      this.taxIdLabel = d.taxIdLabel;
      this.hint = d.hint;
    }
  };
}

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
          var MAX = 1600;
          if (cw > MAX || ch > MAX){
            var s = Math.min(MAX/cw, MAX/ch);
            cw = Math.round(cw*s); ch = Math.round(ch*s);
          }
          var canvas = document.createElement("canvas");
          canvas.width = cw; canvas.height = ch;
          var ctx = canvas.getContext("2d");
          ctx.fillStyle = "#FFFFFF";
          ctx.fillRect(0,0,cw,ch);
          ctx.drawImage(img, 0, 0, cw, ch);
          canvas.toBlob(function(blob){
            if (!blob) return reject(new Error("toBlob failed"));
            var newName = (file.name||"image").replace(/\.[a-z0-9]+$/i, "") + ".jpg";
            try {
              resolve(new File([blob], newName, { type: "image/jpeg" }));
            } catch (e) {
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
    if (converting) return;
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
      form.submit();
    });
  });
})();
</script>'); ?>
