<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-white">Calendario de flotilla</h1>
    <p class="text-sm text-slate-500">Disponibilidad y reservas</p>
  </div>
  <a href="<?= url('/admin/reservations') ?>" class="k-btn k-btn-outline">
    <i data-lucide="list" class="w-4 h-4"></i> Ver lista
  </a>
</div>

<div class="card p-6">
  <div id="calendar"></div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<?php \App\Core\View::push('scripts', '
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){
  const el = document.getElementById("calendar");
  if(!el || typeof FullCalendar === "undefined") return;
  const cal = new FullCalendar.Calendar(el, {
    initialView: "dayGridMonth",
    locale: "es",
    height: "auto",
    headerToolbar: { left:"prev,next today", center:"title", right:"dayGridMonth,timeGridWeek,timeGridDay" },
    buttonText: { today:"Hoy", month:"Mes", week:"Semana", day:"Dia" },
    events: "' . url('/admin/reservations/events') . '"
  });
  cal.render();
});
</script>'); ?>
