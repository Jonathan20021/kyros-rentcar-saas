<?php /* Standalone offline fallback served by the service worker. Fully self-contained (inline CSS) so it renders with no network. */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Sin conexion · Kyros Rent Car</title>
<meta name="theme-color" content="#0E1422">
<link rel="manifest" href="<?= url('/manifest.webmanifest') ?>">
<style>
  *{ box-sizing:border-box; margin:0; padding:0; -webkit-font-smoothing:antialiased; }
  html,body{ height:100%; }
  body{
    font-family:'Plus Jakarta Sans','Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    background:#0E1422; color:#fff;
    display:grid; place-items:center; padding:1.5rem;
    background-image:radial-gradient(42rem 28rem at 50% -8%, rgba(242,54,69,.28), transparent 60%);
    min-height:100dvh;
  }
  .wrap{ width:100%; max-width:430px; text-align:center; }
  .mark{
    width:84px; height:84px; margin:0 auto 1.75rem; border-radius:22px;
    background:linear-gradient(135deg,#F23645,#FF5C72);
    display:grid; place-items:center; font-weight:800; font-size:42px; color:#fff;
    box-shadow:0 18px 40px -16px rgba(242,54,69,.6);
  }
  h1{ font-size:1.6rem; letter-spacing:-.02em; margin-bottom:.65rem; }
  p{ color:rgba(255,255,255,.62); font-size:.98rem; line-height:1.6; }
  .pill{
    display:inline-flex; align-items:center; gap:.5rem; margin:1.5rem 0 .25rem;
    padding:.4rem .85rem; border-radius:99px; font-size:.8rem; font-weight:600;
    background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); color:rgba(255,255,255,.75);
  }
  .dot{ width:8px; height:8px; border-radius:50%; background:#f59e0b; box-shadow:0 0 0 4px rgba(245,158,11,.18); }
  .dot.online{ background:#10b981; box-shadow:0 0 0 4px rgba(16,185,129,.18); }
  .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:.55rem;
    margin-top:1.75rem; height:50px; padding:0 1.6rem; width:100%;
    font-weight:700; font-size:.95rem; color:#fff; cursor:pointer;
    border:0; border-radius:14px; background:linear-gradient(135deg,#F23645,#FF5C72);
    box-shadow:0 12px 30px -12px rgba(242,54,69,.65); transition:transform .15s ease, filter .15s ease;
  }
  .btn:hover{ filter:brightness(1.06); } .btn:active{ transform:translateY(1px); }
  .hint{ margin-top:1.1rem; font-size:.8rem; color:rgba(255,255,255,.4); }
</style>
</head>
<body>
  <main class="wrap">
    <div class="mark">K</div>
    <h1>Estas sin conexion</h1>
    <p>No pudimos cargar esta pagina porque no hay internet en este momento. Tus datos estan a salvo &mdash; vuelve a intentarlo cuando recuperes la conexion.</p>
    <div class="pill"><span class="dot" id="net-dot"></span><span id="net-label">Buscando conexion&hellip;</span></div>
    <button class="btn" id="retry" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9 9 0 0 0-6.4 2.6L3 8"/><path d="M3 3v5h5"/></svg>
      Reintentar
    </button>
    <p class="hint">Kyros Rent Car</p>
  </main>
  <script>
    var dot   = document.getElementById('net-dot');
    var label = document.getElementById('net-label');
    function paint(){
      if (navigator.onLine){
        dot.classList.add('online');
        label.textContent = 'Conexion restablecida';
      } else {
        dot.classList.remove('online');
        label.textContent = 'Sin conexion';
      }
    }
    document.getElementById('retry').addEventListener('click', function(){ location.reload(); });
    window.addEventListener('online', function(){ paint(); setTimeout(function(){ location.reload(); }, 600); });
    window.addEventListener('offline', paint);
    paint();
  </script>
</body>
</html>
