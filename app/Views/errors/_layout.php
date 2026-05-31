<?php
/**
 * Shared error-page layout. Each specific error view (404/403/500) sets:
 *   $code     — string number to display ("404")
 *   $title    — short human title
 *   $message  — sentence describing what happened
 *   $icon     — Lucide icon name
 *   $tone     — 'amber' | 'red' | 'indigo'
 *   $backUrl  — where the primary CTA goes (default: /)
 *   $extras   — optional HTML inserted under the CTA (debug trace in dev)
 */
$code    = $code    ?? '404';
$title   = $title   ?? 'Página no encontrada';
$message = $message ?? 'El recurso solicitado no existe.';
$icon    = $icon    ?? 'compass';
$tone    = $tone    ?? 'indigo';
$backUrl = $backUrl ?? url('/');
$extras  = $extras  ?? '';

$tones = [
  'amber'   => ['from'=>'#FBBF24','to'=>'#F59E0B','soft'=>'bg-amber-50 text-amber-600','ring'=>'ring-amber-200'],
  'red'     => ['from'=>'#FB7185','to'=>'#F23645','soft'=>'bg-red-50 text-red-600','ring'=>'ring-red-200'],
  'indigo'  => ['from'=>'#818CF8','to'=>'#6366F1','soft'=>'bg-indigo-50 text-indigo-600','ring'=>'ring-indigo-200'],
];
$t = $tones[$tone] ?? $tones['indigo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($code) ?> · <?= e($title) ?> · Kyros Rent Car</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<style>
  * { font-family: 'Inter', sans-serif; }
  .font-display { font-family: 'Plus Jakarta Sans', sans-serif; }
  @keyframes blob { 0%,100% { transform: translate(0,0) scale(1) } 33% { transform: translate(20px,-30px) scale(1.05) } 66% { transform: translate(-15px,15px) scale(0.95) } }
  .blob-1 { animation: blob 14s ease-in-out infinite; }
  .blob-2 { animation: blob 18s ease-in-out infinite reverse; }
  body { background: radial-gradient(60rem 40rem at 50% -10%, rgba(99,102,241,.08), transparent 60%), #F8FAFC; }
</style>
</head>
<body class="min-h-screen flex items-center justify-center p-5 sm:p-8 text-slate-800 relative overflow-hidden">

  <!-- Decorative blobs -->
  <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full opacity-30 blur-3xl blob-1" style="background:linear-gradient(135deg,<?= $t['from'] ?>,<?= $t['to'] ?>)"></div>
  <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full opacity-20 blur-3xl blob-2" style="background:linear-gradient(135deg,<?= $t['from'] ?>,<?= $t['to'] ?>)"></div>

  <div class="relative max-w-lg w-full text-center">
    <!-- Floating glass card -->
    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-[0_10px_60px_-20px_rgba(15,23,42,0.15)] border border-white/60 p-8 sm:p-12">
      <div class="inline-flex w-16 h-16 sm:w-20 sm:h-20 rounded-3xl ring-8 <?= $t['ring'] ?> items-center justify-center <?= $t['soft'] ?>">
        <i data-lucide="<?= e($icon) ?>" class="w-8 h-8 sm:w-10 sm:h-10"></i>
      </div>
      <p class="mt-6 font-display text-[64px] sm:text-[88px] font-black leading-none tracking-tight tnum"
         style="background:linear-gradient(135deg,<?= $t['from'] ?>,<?= $t['to'] ?>);-webkit-background-clip:text;background-clip:text;color:transparent;"><?= e($code) ?></p>
      <h1 class="mt-3 font-display text-xl sm:text-2xl font-extrabold text-slate-900"><?= e($title) ?></h1>
      <p class="mt-3 text-[14.5px] text-slate-500 leading-relaxed max-w-md mx-auto"><?= e($message) ?></p>

      <div class="mt-7 flex flex-col sm:flex-row gap-2.5 justify-center">
        <a href="<?= e($backUrl) ?>"
           class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl text-white font-semibold transition hover:shadow-lg hover:-translate-y-0.5"
           style="background:linear-gradient(135deg,<?= $t['from'] ?>,<?= $t['to'] ?>)">
          <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al inicio
        </a>
        <button type="button" onclick="history.back()"
           class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-white text-slate-600 font-semibold border border-slate-200 hover:bg-slate-50 transition">
          <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Página anterior
        </button>
      </div>

      <?= $extras ?>
    </div>

    <p class="mt-6 text-[12px] text-slate-400">
      <span class="font-semibold text-slate-500">Kyros Rent Car</span>
      · El sistema operativo de tu rent car
    </p>
  </div>

  <script>lucide.createIcons()</script>
</body>
</html>
