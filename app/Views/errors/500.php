<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>500 · Kyros Rent Car</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-50 text-slate-800">
  <div class="text-center px-6">
    <p class="text-7xl font-black text-red-500">500</p>
    <h1 class="mt-4 text-2xl font-bold">Error interno</h1>
    <p class="mt-2 text-slate-500"><?= e($message ?? 'Ocurrio un error inesperado. Intenta de nuevo.') ?></p>
    <a href="<?= url('/') ?>" class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">Volver al inicio</a>
  </div>
</body>
</html>
