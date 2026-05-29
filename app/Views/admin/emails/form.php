<?php
$sampleJson = htmlspecialchars(json_encode([
  'tenant'=>$tenant['name'] ?? ($_auth['name'] ?? 'Tu empresa'),
  'customer'=>'Cliente Demo','name'=>'Usuario Demo','vehicle'=>'Toyota Corolla 2023','code'=>'RSV-2026-0001',
  'start'=>'01/06/2026','end'=>'05/06/2026','total'=>'RD$ 18,880.00','balance'=>'RD$ 5,000.00',
  'amount'=>'RD$ 10,000.00','method'=>'Tarjeta','date'=>date('d/m/Y'),'email'=>'cliente@correo.com','password'=>'••••••••',
], JSON_UNESCAPED_UNICODE), ENT_QUOTES);
?>
<div class="max-w-5xl mx-auto"
     x-data="emailTpl(<?= htmlspecialchars(json_encode($tpl['subject']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($tpl['body']), ENT_QUOTES) ?>, <?= $sampleJson ?>)">
  <div class="flex items-start justify-between gap-3 mb-6">
    <div>
      <h1 class="font-display text-2xl font-bold text-navy dark:text-white"><?= e($tpl['label']) ?></h1>
      <p class="text-sm text-slate-500 dark:text-slate-400"><?= e($tpl['desc']) ?></p>
    </div>
    <a href="<?= url('/admin/emails') ?>" class="k-btn k-btn-outline shrink-0"><i data-lucide="arrow-left" class="w-4 h-4"></i> Volver</a>
  </div>

  <div class="grid lg:grid-cols-2 gap-5">
    <!-- editor -->
    <form method="POST" action="<?= url('/admin/emails/update/'.$tpl['code']) ?>" class="card p-6 space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Asunto *</label>
        <input name="subject" x-model="subject" required maxlength="200" class="fld">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Cuerpo (HTML permitido)</label>
        <textarea name="body" x-model="body" x-ref="body" rows="12" class="fld font-mono text-[12.5px] leading-relaxed"></textarea>
      </div>
      <div>
        <p class="text-xs font-medium text-slate-500 mb-1.5">Variables disponibles (clic para insertar)</p>
        <div class="flex flex-wrap gap-1.5">
          <?php foreach ($tpl['vars'] as $v): ?>
          <button type="button" @click="insert('<?= $v ?>')" class="px-2 py-1 rounded-lg bg-paper dark:bg-slate-800 border hairline text-[11px] font-mono text-slate-600 dark:text-slate-300 hover:border-brand/40 hover:text-brand">{{<?= $v ?>}}</button>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Estado</label>
        <select name="status" class="fld">
          <option value="active" <?= $tpl['status']==='active'?'selected':'' ?>>Activa (se envía)</option>
          <option value="inactive" <?= $tpl['status']==='inactive'?'selected':'' ?>>Inactiva (no se envía)</option>
        </select>
      </div>
      <div class="flex items-center gap-2 pt-1">
        <button type="submit" class="k-btn k-btn-grad"><i data-lucide="save" class="w-4 h-4"></i> Guardar</button>
        <?php if ($tpl['customized']): ?>
        <button type="submit" formaction="<?= url('/admin/emails/reset/'.$tpl['code']) ?>" onclick="return confirm('¿Restablecer a la plantilla por defecto? Se perderán tus cambios.')" class="k-btn k-btn-outline">Restablecer</button>
        <?php endif; ?>
      </div>
    </form>

    <!-- preview + test -->
    <div class="space-y-5">
      <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b hairline flex items-center justify-between">
          <span class="font-display font-bold text-navy dark:text-white text-sm">Vista previa</span>
          <span class="text-xs text-slate-400 flex items-center gap-1.5"><i data-lucide="eye" class="w-3.5 h-3.5"></i> con datos de ejemplo</span>
        </div>
        <div class="p-5 bg-paper dark:bg-slate-950">
          <div class="rounded-xl bg-white shadow-sm border hairline overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
              <p class="text-[11px] text-slate-400">Asunto</p>
              <p class="text-sm font-semibold text-slate-900" x-text="renderVars(subject)"></p>
            </div>
            <div class="px-4 py-4 text-[13px] leading-relaxed text-slate-700 email-preview" x-html="renderVars(body)"></div>
          </div>
        </div>
      </div>

      <form method="POST" action="<?= url('/admin/emails/test/'.$tpl['code']) ?>" class="card p-5 flex items-end gap-3">
        <?= csrf_field() ?>
        <div class="flex-1">
          <label class="block text-sm font-medium mb-1.5">Enviar prueba a</label>
          <input type="email" name="to" placeholder="tu@correo.com" class="fld">
        </div>
        <button type="submit" class="k-btn k-btn-dark"><i data-lucide="send" class="w-4 h-4"></i> Enviar prueba</button>
      </form>
    </div>
  </div>
</div>

<style>.email-preview p{ margin:0 0 .6rem } .email-preview strong{ color:#1c2433 }</style>

<?php \App\Core\View::push('scripts', '<script>
function emailTpl(subject, body, sample){
  return {
    subject, body, sample,
    renderVars(str){ return (str||"").replace(/\{\{\s*(\w+)\s*\}\}/g, (m,k)=> (k in this.sample) ? this.sample[k] : m); },
    insert(v){
      var el=this.$refs.body, tok="{{"+v+"}}";
      if(!el){ this.body+=tok; return; }
      var s=el.selectionStart||this.body.length, e=el.selectionEnd||s;
      this.body=this.body.slice(0,s)+tok+this.body.slice(e);
      this.$nextTick(()=>{ el.focus(); el.selectionStart=el.selectionEnd=s+tok.length; });
    }
  }
}
</script>'); ?>
