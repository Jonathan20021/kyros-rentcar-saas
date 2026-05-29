<?php
/** Print/PDF layout — clean, no app chrome. Expects $title, $content. */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Documento · Kyros') ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body{ font-family:'Inter',sans-serif; -webkit-font-smoothing:antialiased; }
  .tnum{ font-variant-numeric:tabular-nums; }
  @media print{ .no-print{ display:none !important; } body{ background:#fff !important; } @page{ margin:14mm; } }
</style>
</head>
<body class="bg-slate-100 text-slate-800">
  <div class="no-print sticky top-0 bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between">
    <a href="<?= e($backUrl ?? url('/admin/contracts')) ?>" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900">&larr; Volver</a>
    <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">Imprimir / Guardar PDF</button>
  </div>
  <div class="max-w-[820px] mx-auto my-6 bg-white shadow-sm print:shadow-none print:my-0 rounded-xl print:rounded-none overflow-hidden">
    <?= $content ?>
  </div>
</body>
</html>
