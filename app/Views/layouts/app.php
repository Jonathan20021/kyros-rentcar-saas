<?php
/**
 * Authenticated shell (admin + super admin) — light premium UI.
 * Expects: $title, $content, optional $active, $panel ('admin'|'super'),
 *          $breadcrumbs, $pageScripts, $tenant, $notifications, $notifications_unread.
 */
use App\Core\View;
$panel       = $panel ?? 'admin';
$active      = $active ?? '';
$breadcrumbs = $breadcrumbs ?? [];
$auth        = $_auth ?? (function_exists('auth') ? auth() : null);
$flashes     = $_flashes ?? [];
// Brand accent: the tenant's primary color drives the panel; super admin = red.
$accent      = ($panel === 'admin' && !empty($tenant['primary_color'])) ? $tenant['primary_color'] : '#F23645';

$adminGroups = [
  ['title'=>'General','items'=>[
    ['key'=>'dashboard','label'=>'Dashboard','icon'=>'layout-dashboard','url'=>url('/admin/dashboard')],
    ['key'=>'reservations','label'=>'Reservas','icon'=>'calendar-check','url'=>url('/admin/reservations'),'feature'=>'reservations'],
  ]],
  ['title'=>'Operacion','items'=>[
    ['key'=>'vehicles','label'=>'Flotilla','icon'=>'car','url'=>url('/admin/vehicles'),'feature'=>'fleet'],
    ['key'=>'drivers','label'=>'Choferes','icon'=>'id-card','url'=>url('/admin/drivers'),'feature'=>'drivers'],
    ['key'=>'customers','label'=>'Clientes','icon'=>'users','url'=>url('/admin/customers'),'feature'=>'customers'],
    ['key'=>'contracts','label'=>'Contratos','icon'=>'file-text','url'=>url('/admin/contracts'),'feature'=>'contracts'],
    ['key'=>'maintenance','label'=>'Mantenimiento','icon'=>'wrench','url'=>url('/admin/maintenance'),'feature'=>'maintenance'],
    ['key'=>'incidents','label'=>'Incidencias','icon'=>'shield-alert','url'=>url('/admin/incidents'),'feature'=>'incidents'],
    ['key'=>'documents','label'=>'Vencimientos','icon'=>'calendar-clock','url'=>url('/admin/documents'),'feature'=>'documents'],
    ['key'=>'locations','label'=>'Sucursales','icon'=>'map-pin','url'=>url('/admin/locations'),'feature'=>'multi_location'],
  ]],
  ['title'=>'Finanzas','items'=>[
    ['key'=>'payments','label'=>'Pagos','icon'=>'credit-card','url'=>url('/admin/payments'),'feature'=>'payments'],
    ['key'=>'invoices','label'=>'Facturas','icon'=>'receipt','url'=>url('/admin/invoices'),'feature'=>'invoices'],
    ['key'=>'expenses','label'=>'Gastos','icon'=>'trending-down','url'=>url('/admin/expenses'),'feature'=>'expenses'],
    ['key'=>'cashbox','label'=>'Cierre de caja','icon'=>'calculator','url'=>url('/admin/cashbox'),'feature'=>'cashbox'],
    ['key'=>'reports','label'=>'Reportes','icon'=>'bar-chart-3','url'=>url('/admin/reports'),'feature'=>'reports'],
  ]],
  ['title'=>'Catalogo','items'=>[
    ['key'=>'categories','label'=>'Categorias','icon'=>'tags','url'=>url('/admin/categories'),'feature'=>'catalog'],
    ['key'=>'extras','label'=>'Servicios','icon'=>'sparkles','url'=>url('/admin/extras'),'feature'=>'catalog'],
    ['key'=>'promos','label'=>'Promociones','icon'=>'ticket-percent','url'=>url('/admin/promos'),'feature'=>'promos'],
  ]],
  ['title'=>'Sistema','items'=>[
    ['key'=>'users','label'=>'Equipo','icon'=>'users-round','url'=>url('/admin/users')],
    ['key'=>'activity','label'=>'Actividad','icon'=>'history','url'=>url('/admin/activity')],
    ['key'=>'emails','label'=>'Correos','icon'=>'mail','url'=>url('/admin/emails'),'feature'=>'email_templates'],
    ['key'=>'api','label'=>'API','icon'=>'plug','url'=>url('/admin/api'),'feature'=>'api'],
    ['key'=>'settings','label'=>'Configuracion','icon'=>'settings','url'=>url('/admin/settings')],
  ]],
];
$superGroups = [
  ['title'=>'Plataforma','items'=>[
    ['key'=>'dashboard','label'=>'Dashboard','icon'=>'layout-dashboard','url'=>url('/super-admin')],
    ['key'=>'tenants','label'=>'Empresas','icon'=>'building-2','url'=>url('/super-admin/tenants')],
    ['key'=>'plans','label'=>'Planes','icon'=>'package','url'=>url('/super-admin/plans')],
  ]],
  ['title'=>'Administracion','items'=>[
    ['key'=>'users','label'=>'Usuarios','icon'=>'users','url'=>url('/super-admin/users')],
    ['key'=>'logs','label'=>'Logs','icon'=>'scroll-text','url'=>url('/super-admin/logs')],
    ['key'=>'settings','label'=>'Configuracion','icon'=>'settings','url'=>url('/super-admin/settings')],
  ]],
];
$groups = $panel === 'super' ? $superGroups : $adminGroups;
$brandSub = $panel === 'super' ? 'Super Admin' : ($tenant['name'] ?? 'Rent Car');
$planName = $tenant['plan_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="es" :class="{ 'dark': dark }" x-data="shell()" x-init="init()" x-cloak>
<head>
<?= View::renderPartial('layouts/_assets', ['title' => $title ?? 'Kyros Rent Car', 'accent' => $accent]) ?>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="panel-shell bg-paper dark:bg-slate-950 text-ink dark:text-slate-200">
<div class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="fixed lg:sticky top-0 z-40 h-screen w-[260px] shrink-0 bg-white dark:bg-slate-900 border-r hairline flex flex-col transition-transform duration-200"
         :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="h-16 flex items-center gap-2.5 px-5 border-b hairline">
      <div class="w-9 h-9 rounded-xl grid place-items-center text-white font-black shadow-sm" style="background:var(--grad)">K</div>
      <div class="leading-tight min-w-0">
        <p class="font-display font-extrabold text-[15px] text-ink dark:text-white">Kyros Rent Car</p>
        <p class="text-[11px] text-slate-400 truncate"><?= e($brandSub) ?></p>
      </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-5">
      <?php foreach ($groups as $g): ?>
      <div>
        <p class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400"><?= e($g['title']) ?></p>
        <div class="space-y-0.5">
        <?php foreach ($g['items'] as $item):
          $on = $active === $item['key'];
          $locked = $panel === 'admin' && !empty($item['feature']) && !plan_has($item['feature']);
        ?>
          <a href="<?= $locked ? url('/admin/upgrade?feature=' . urlencode($item['feature'])) : e($item['url']) ?>"
             class="group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[14px] font-medium transition
                    <?= $on ? 'text-brand bg-brand/[0.08] font-semibold' : ($locked ? 'text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-ink dark:hover:text-white') ?>"
             <?php if ($locked): ?>title="Disponible en plan <?= e(plan_upgrade_required($item['feature'])) ?>"<?php endif; ?>>
            <?php if ($on): ?><span class="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-1 rounded-r-full grad-bg"></span><?php endif; ?>
            <i data-lucide="<?= e($item['icon']) ?>" class="w-[18px] h-[18px] <?= $on ? 'text-brand' : ($locked ? 'text-slate-300' : 'text-slate-400 group-hover:text-ink dark:group-hover:text-white') ?>"></i>
            <span class="flex-1 truncate"><?= e($item['label']) ?></span>
            <?php if ($locked): ?><i data-lucide="lock" class="w-3.5 h-3.5 text-slate-300"></i><?php endif; ?>
          </a>
        <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </nav>

    <!-- Promo card -->
    <?php if ($panel !== 'super'): ?>
    <div class="px-3">
      <div class="relative overflow-hidden rounded-2xl p-4 text-white" style="background:linear-gradient(150deg,var(--brand),color-mix(in srgb,var(--brand) 55%, #7a0f1c))">
        <div class="absolute -top-8 -right-8 w-24 h-24 rounded-full bg-white/15 blur-xl"></div>
        <div class="relative">
          <div class="w-8 h-8 rounded-lg bg-white/20 grid place-items-center mb-2.5"><i data-lucide="rocket" class="w-4 h-4"></i></div>
          <p class="font-display font-bold text-[13px] leading-snug">Optimiza tu operación y vende más</p>
          <a href="<?= url('/admin/reports') ?>" class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 rounded-lg bg-white text-[12px] font-semibold" style="color:var(--brand)">Ver reportes <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="p-3 border-t hairline mt-3">
      <?php if ($panel !== 'super'): ?>
      <a href="<?= url('/r/' . ($tenant['slug'] ?? '')) ?>" target="_blank"
         class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-ink dark:hover:text-white transition">
        <i data-lucide="external-link" class="w-[18px] h-[18px]"></i> Ver página pública
      </a>
      <?php endif; ?>
      <form method="POST" action="<?= url('/logout') ?>"><?= csrf_field() ?>
        <button class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] text-slate-500 hover:bg-red-50 hover:text-red-600 transition">
          <i data-lucide="log-out" class="w-[18px] h-[18px]"></i> Cerrar sesion
        </button>
      </form>
    </div>
  </aside>

  <div x-show="open" x-cloak @click="open=false" class="fixed inset-0 z-30 bg-ink/30 backdrop-blur-sm lg:hidden"></div>

  <!-- Main column -->
  <div class="flex-1 flex flex-col min-w-0">
    <header class="h-16 sticky top-0 z-20 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b hairline flex items-center gap-3 px-4 lg:px-7">
      <button @click="open=!open" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
        <i data-lucide="menu" class="w-5 h-5"></i>
      </button>

      <!-- date + context (reference-style) -->
      <div class="hidden md:flex items-center gap-4 text-[13px] text-slate-400">
        <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> <?= ucfirst(strftime_es()) ?></span>
        <?php if ($panel !== 'super' && !empty($tenant['address'])): ?>
          <span class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4"></i> <?= e(mb_strimwidth($tenant['address'],0,28,'…')) ?></span>
        <?php endif; ?>
      </div>

      <!-- Trigger for the ⌘K command palette — full bar on desktop, icon on mobile -->
      <button type="button" @click="$dispatch('kyros:cmd-open')"
              class="relative ml-auto hidden sm:flex items-center w-60 lg:w-72 pl-10 pr-2 py-2.5 text-sm rounded-xl bg-slate-100/80 dark:bg-slate-800 border border-transparent hover:bg-white hover:border-slate-200 dark:hover:border-slate-700 transition text-left text-slate-400">
        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
        <span class="truncate">Buscar reservas, vehículos, clientes…</span>
        <kbd class="ml-auto text-[10px] font-semibold text-slate-400 bg-white dark:bg-slate-700 border hairline rounded-md px-1.5 py-0.5">⌘K</kbd>
      </button>
      <button type="button" @click="$dispatch('kyros:cmd-open')"
              class="sm:hidden ml-auto p-2 -mr-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Buscar">
        <i data-lucide="search" class="w-5 h-5"></i>
      </button>

      <div class="flex items-center gap-1.5 <?= ' sm:ml-0 ml-auto' ?>">
        <?php if ($planName): ?>
          <span class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-ink text-white text-xs font-semibold"><i data-lucide="sparkles" class="w-3.5 h-3.5"></i> <?= e($planName) ?></span>
        <?php endif; ?>
        <button @click="toggleDark()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" title="Tema">
          <i data-lucide="moon" class="w-5 h-5" x-show="!dark"></i>
          <i data-lucide="sun" class="w-5 h-5" x-show="dark"></i>
        </button>
        <div class="relative" x-data="{n:false}">
          <button @click="n=!n" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 relative">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <?php if (!empty($notifications_unread)): ?><span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span><?php endif; ?>
          </button>
          <div x-show="n" x-cloak @click.outside="n=false" x-transition.origin.top.right
               class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 rounded-2xl shadow-soft border hairline overflow-hidden">
            <div class="px-4 py-3 border-b hairline font-semibold text-sm flex items-center justify-between">
              <span>Notificaciones <?php if(!empty($notifications_unread)): ?><span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600 ml-1"><?= $notifications_unread ?></span><?php endif; ?></span>
              <?php if (!empty($notifications_unread)): ?>
              <form method="POST" action="<?= url('/admin/notifications/read-all') ?>"><?= csrf_field() ?><button class="text-xs font-medium text-brand hover:underline">Marcar leídas</button></form>
              <?php endif; ?>
            </div>
            <div class="max-h-80 overflow-y-auto divide-y hairline">
              <?php foreach (($notifications ?? []) as $nt): ?>
                <form method="POST" action="<?= url('/admin/notifications/read/'.$nt['id']) ?>"><?= csrf_field() ?>
                <button type="submit" class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60 flex gap-2.5 <?= $nt['is_read']?'':'bg-brand/[0.03]' ?>">
                  <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 <?= $nt['is_read']?'bg-transparent':'bg-brand' ?>"></span>
                  <span class="min-w-0"><span class="block text-sm font-medium text-ink dark:text-white truncate"><?= e($nt['title']) ?></span><span class="block text-xs text-slate-400 mt-0.5"><?= e($nt['message']) ?></span></span>
                </button>
                </form>
              <?php endforeach; ?>
              <?php if (empty($notifications)): ?><p class="px-4 py-8 text-center text-sm text-slate-400">Sin notificaciones</p><?php endif; ?>
            </div>
            <a href="<?= url('/admin/notifications') ?>" class="block text-center px-4 py-2.5 border-t hairline text-sm font-medium text-brand hover:bg-paper">Ver todas</a>
          </div>
        </div>
        <div class="relative" x-data="{p:false}">
          <button @click="p=!p" class="flex items-center gap-2.5 pl-1.5 pr-2.5 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
            <div class="w-8 h-8 rounded-lg bg-ink text-white grid place-items-center text-xs font-bold"><?= e(initials($auth['name'] ?? 'K')) ?></div>
            <span class="hidden md:block text-sm font-semibold"><?= e(explode(' ', $auth['name'] ?? '')[0]) ?></span>
            <i data-lucide="chevron-down" class="w-4 h-4 hidden md:block text-slate-400"></i>
          </button>
          <div x-show="p" x-cloak @click.outside="p=false" x-transition.origin.top.right
               class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-2xl shadow-soft border hairline py-1.5">
            <div class="px-4 py-2.5 border-b hairline">
              <p class="text-sm font-semibold text-ink dark:text-white"><?= e($auth['name'] ?? '') ?></p>
              <p class="text-xs text-slate-400 truncate"><?= e($auth['email'] ?? '') ?></p>
            </div>
            <form method="POST" action="<?= url('/logout') ?>"><?= csrf_field() ?>
              <button class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"><i data-lucide="log-out" class="w-4 h-4"></i> Cerrar sesion</button>
            </form>
          </div>
        </div>
      </div>
    </header>

    <?php if ($panel === 'admin' && !empty($tenant['is_demo']) && !empty($tenant['demo_expires_at'])):
      $secsLeft = max(0, strtotime($tenant['demo_expires_at']) - time());
    ?>
    <div class="px-4 lg:px-7 pt-4"
         x-data="demoCountdown(<?= $secsLeft ?>)" x-init="tick()">
      <div class="rounded-2xl border border-amber-200 dark:border-amber-500/30 bg-gradient-to-r from-amber-50 to-white dark:from-amber-500/10 dark:to-slate-900 p-3.5 flex items-center gap-3 shadow-card">
        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-300 grid place-items-center shrink-0">
          <i data-lucide="hourglass" class="w-5 h-5"></i>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-[13px] font-semibold text-amber-900 dark:text-amber-100">Estás en una demo de Kyros (<?= e($tenant['plan_name'] ?? 'Plan demo') ?>)</p>
          <p class="text-[12px] text-amber-700/80 dark:text-amber-200/80 mt-0.5">
            Esta cuenta se eliminará en <span class="font-bold tnum" x-text="display"></span>.
            Todos los datos que registres se borrarán al expirar.
          </p>
        </div>
        <a href="<?= url('/register') ?>" class="hidden sm:inline-flex k-btn k-btn-dark !h-9 !px-3.5 text-[12px]">
          <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Crear cuenta real
        </a>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($breadcrumbs)): ?>
    <div class="px-4 lg:px-7 pt-5">
      <nav class="flex items-center gap-1.5 text-xs text-slate-400">
        <?php foreach ($breadcrumbs as $i => $bc): ?>
          <?php if ($i > 0): ?><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i><?php endif; ?>
          <?php if (!empty($bc['url'])): ?><a href="<?= e($bc['url']) ?>" class="hover:text-brand"><?= e($bc['label']) ?></a>
          <?php else: ?><span class="text-slate-600 dark:text-slate-300 font-medium"><?= e($bc['label']) ?></span><?php endif; ?>
        <?php endforeach; ?>
      </nav>
    </div>
    <?php endif; ?>

    <main class="flex-1 p-4 lg:p-7 max-w-[1500px] w-full mx-auto"><?= $content ?></main>
  </div>
</div>

<?php if ($panel === 'admin'):
  $dockItems = [
    ['key'=>'dashboard',   'label'=>'Inicio',     'icon'=>'home',          'url'=>url('/admin/dashboard')],
    ['key'=>'reservations','label'=>'Reservas',   'icon'=>'calendar-check','url'=>url('/admin/reservations')],
    ['key'=>'_create',     'label'=>'Crear',      'icon'=>'plus',          'url'=>url('/admin/reservations/create')],
    ['key'=>'vehicles',    'label'=>'Flotilla',   'icon'=>'car',           'url'=>url('/admin/vehicles')],
    ['key'=>'_more',       'label'=>'Más',        'icon'=>'menu',          'url'=>'#'],
  ];
?>
<nav class="mob-dock" aria-label="Acciones principales móvil">
  <?php foreach ($dockItems as $d):
    $isFab = $d['key'] === '_create';
    $isMore = $d['key'] === '_more';
    $isOn = $active === $d['key'];
  ?>
    <?php if ($isMore): ?>
      <button type="button" @click="open = !open" class="<?= $isOn ? 'is-active' : '' ?>">
        <i data-lucide="<?= e($d['icon']) ?>"></i><b><?= e($d['label']) ?></b>
      </button>
    <?php elseif ($isFab): ?>
      <a href="<?= e($d['url']) ?>" class="mob-dock-fab">
        <span><i data-lucide="<?= e($d['icon']) ?>"></i></span>
        <b><?= e($d['label']) ?></b>
      </a>
    <?php else: ?>
      <a href="<?= e($d['url']) ?>" class="<?= $isOn ? 'is-active' : '' ?>">
        <i data-lucide="<?= e($d['icon']) ?>"></i><b><?= e($d['label']) ?></b>
      </a>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>
<?php endif; ?>

<!-- Global Confirm Modal (any form with [data-confirm="msg"]) -->
<div x-data="confirmModal()" x-init="bind()" @kyros:confirm.window="open($event.detail)">
  <div x-show="visible" x-cloak x-transition.opacity.duration.150ms class="fixed inset-0 z-[80] bg-ink/45 backdrop-blur-sm" @click="cancel()"></div>
  <div x-show="visible" x-cloak x-transition.duration.150ms
       class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-[90] w-[92%] max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-lift border hairline overflow-hidden">
    <div class="p-6">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 rounded-2xl grid place-items-center shrink-0"
             :class="variant==='danger' ? 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400'">
          <i :data-lucide="variant==='danger' ? 'trash-2' : 'alert-triangle'" class="w-6 h-6"></i>
        </div>
        <div class="min-w-0 flex-1">
          <h3 class="font-display font-bold text-navy dark:text-white text-lg" x-text="title"></h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed" x-text="message"></p>
        </div>
      </div>
    </div>
    <div class="px-6 pb-6 flex gap-2 justify-end">
      <button type="button" @click="cancel()" class="k-btn k-btn-outline" x-text="cancelLabel">Cancelar</button>
      <button type="button" @click="confirm()"
              :class="variant==='danger' ? 'k-btn-grad' : 'k-btn-dark'" class="k-btn" x-text="confirmLabel"></button>
    </div>
  </div>
</div>

<!-- ⌘K Command Palette (Spotlight-style) -->
<div x-data="cmdPalette()" x-cloak
     @keydown.window.meta.k.prevent="toggle()"
     @keydown.window.ctrl.k.prevent="toggle()"
     @keydown.window.escape="open=false"
     @kyros:cmd-open.window="toggle(true)">
  <div x-show="open" x-transition.opacity.duration.150ms class="fixed inset-0 z-[60] bg-ink/40 backdrop-blur-sm" @click="open=false"></div>
  <div x-show="open"
       x-transition:enter="transition ease-out duration-150"
       x-transition:enter-start="opacity-0 scale-95"
       x-transition:enter-end="opacity-100 scale-100"
       x-transition:leave="transition ease-in duration-100"
       x-transition:leave-start="opacity-100 scale-100"
       x-transition:leave-end="opacity-0 scale-95"
       class="fixed top-[10vh] left-1/2 -translate-x-1/2 z-[70] w-[94%] max-w-2xl bg-white dark:bg-slate-900 rounded-3xl shadow-lift border hairline overflow-hidden">

    <!-- Search input -->
    <div class="flex items-center gap-3 px-5 h-16 border-b hairline">
      <i data-lucide="search" class="w-5 h-5 text-slate-400 shrink-0"></i>
      <input x-ref="cmdInput" x-model.debounce.180ms="query" @input="run()" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="go()"
             type="text" placeholder="Buscar vehículos, clientes, reservas, contratos, choferes…"
             class="flex-1 bg-transparent outline-none text-[16px] text-navy dark:text-white placeholder-slate-400 font-medium">
      <kbd class="text-[10px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-800 border hairline rounded-md px-1.5 py-0.5">ESC</kbd>
    </div>

    <div class="max-h-[60vh] overflow-y-auto py-1">

      <!-- Empty-state sections (no query): Recent + Pages + Actions -->
      <template x-if="query.length < 2">
        <div>
          <!-- Recent -->
          <template x-if="recent.length">
            <div class="py-2">
              <div class="px-5 pt-2 pb-1.5 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 flex items-center gap-1.5">
                <i data-lucide="history" class="w-3 h-3"></i> Recientes
              </div>
              <template x-for="(r, idx) in recent" :key="'recent-'+idx">
                <a :href="r.url"
                   :class="active === idx ? 'bg-brand/10 text-brand' : 'hover:bg-paper dark:hover:bg-slate-800/60 text-navy dark:text-slate-200'"
                   class="flex items-center gap-3 px-5 py-2.5 group">
                  <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 grid place-items-center shrink-0">
                    <i :data-lucide="r.icon || 'circle'" class="w-4 h-4 text-slate-500"></i>
                  </span>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium truncate" x-text="r.title"></p>
                    <p class="text-[11px] text-slate-400 truncate" x-text="r.subtitle"></p>
                  </div>
                  <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition"></i>
                </a>
              </template>
            </div>
          </template>

          <!-- Quick actions -->
          <div class="py-2">
            <div class="px-5 pt-2 pb-1.5 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 flex items-center gap-1.5">
              <i data-lucide="zap" class="w-3 h-3"></i> Acciones rápidas
            </div>
            <template x-for="(a, idx) in actions" :key="'act-'+idx">
              <a :href="a.url" @click="remember(a)"
                 :class="active === recent.length + idx ? 'bg-brand/10 text-brand' : 'hover:bg-paper dark:hover:bg-slate-800/60 text-navy dark:text-slate-200'"
                 class="flex items-center gap-3 px-5 py-2.5 group">
                <span class="w-8 h-8 rounded-lg bg-brand/10 text-brand grid place-items-center shrink-0">
                  <i :data-lucide="a.icon" class="w-4 h-4"></i>
                </span>
                <span class="text-sm font-medium flex-1" x-text="a.title"></span>
                <span x-show="a.kbd" class="text-[10px] text-slate-400 font-mono" x-text="a.kbd"></span>
              </a>
            </template>
          </div>

          <!-- Pages -->
          <div class="py-2">
            <div class="px-5 pt-2 pb-1.5 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 flex items-center gap-1.5">
              <i data-lucide="compass" class="w-3 h-3"></i> Ir a
            </div>
            <template x-for="(p, idx) in pages" :key="'page-'+idx">
              <a :href="p.url" @click="remember(p)"
                 :class="active === recent.length + actions.length + idx ? 'bg-brand/10 text-brand' : 'hover:bg-paper dark:hover:bg-slate-800/60 text-navy dark:text-slate-200'"
                 class="flex items-center gap-3 px-5 py-2.5 group">
                <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 grid place-items-center shrink-0">
                  <i :data-lucide="p.icon" class="w-4 h-4 text-slate-500"></i>
                </span>
                <span class="text-sm font-medium flex-1" x-text="p.title"></span>
              </a>
            </template>
          </div>
        </div>
      </template>

      <!-- Search results -->
      <template x-if="query.length >= 2 && !loading && total === 0">
        <div class="px-5 py-14 text-center">
          <div class="inline-flex w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 items-center justify-center mb-3">
            <i data-lucide="search-x" class="w-6 h-6 text-slate-400"></i>
          </div>
          <p class="text-sm text-slate-500">Sin resultados para "<b class="text-navy dark:text-white" x-text="query"></b>"</p>
          <p class="text-xs text-slate-400 mt-1">Intenta con otra palabra o nombre.</p>
        </div>
      </template>
      <template x-for="(g, gi) in groups" :key="g.label">
        <div class="py-2">
          <div class="px-5 pt-2 pb-1.5 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 flex items-center gap-1.5">
            <i :data-lucide="g.icon" class="w-3 h-3"></i> <span x-text="g.label"></span>
          </div>
          <template x-for="(it, ii) in g.items" :key="g.label + '-' + ii">
            <a :href="it.url" @click="remember({title:it.title, subtitle:it.subtitle, url:it.url, icon:g.icon})"
               :class="(flatIndex(gi,ii) === active) ? 'bg-brand/10 text-brand' : 'hover:bg-paper dark:hover:bg-slate-800/60 text-navy dark:text-slate-200'"
               class="flex items-start gap-3 px-5 py-2.5 group">
              <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 grid place-items-center shrink-0 mt-0.5">
                <i :data-lucide="g.icon" class="w-4 h-4 text-slate-500"></i>
              </span>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium truncate" x-text="it.title"></p>
                <p class="text-[11px] text-slate-400 truncate mt-0.5" x-text="it.subtitle"></p>
              </div>
              <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-300 opacity-0 group-hover:opacity-100 transition mt-1.5"></i>
            </a>
          </template>
        </div>
      </template>
    </div>

    <!-- Footer hint bar -->
    <div class="px-5 py-2.5 border-t hairline flex items-center justify-between text-[11px] text-slate-400 bg-paper/40 dark:bg-slate-800/30">
      <span class="flex items-center gap-3">
        <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-700 border hairline rounded font-mono">↑↓</kbd> navegar</span>
        <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-700 border hairline rounded font-mono">↵</kbd> abrir</span>
        <span class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-700 border hairline rounded font-mono">ESC</kbd> cerrar</span>
      </span>
      <span x-show="loading" class="flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse"></span> Buscando…
      </span>
    </div>
  </div>
</div>

<!-- Toasts -->
<div class="fixed bottom-5 right-5 z-50 space-y-2.5" x-data="{toasts: window.__flashes || []}">
  <template x-for="(t,i) in toasts" :key="i">
    <div x-show="t.show" x-init="setTimeout(()=>t.show=false,5000)" x-transition.opacity.duration.300ms
         class="flex items-center gap-3 pl-4 pr-3 py-3 rounded-2xl shadow-lift bg-white dark:bg-slate-900 border hairline min-w-[280px]">
      <span class="w-2 h-2 rounded-full" :class="{'bg-emerald-500':t.type==='success','bg-red-500':t.type==='error','bg-amber-500':t.type==='warning','bg-slate-400':t.type==='info'}"></span>
      <span x-text="t.message" class="text-sm font-medium text-ink dark:text-white"></span>
      <button @click="t.show=false" class="ml-auto text-slate-400 hover:text-ink">&times;</button>
    </div>
  </template>
</div>

<script>
  window.__flashes = [
    <?php foreach ($flashes as $type => $messages): foreach ((array) $messages as $m): ?>
      { type: <?= json_encode($type) ?>, message: <?= json_encode($m) ?>, show:true },
    <?php endforeach; endforeach; ?>
  ];
  function shell(){
    return {
      open:false, dark: localStorage.getItem('kyros-dark')==='1',
      init(){ this.$nextTick(()=>window.lucide&&lucide.createIcons()); },
      toggleDark(){ this.dark=!this.dark; localStorage.setItem('kyros-dark', this.dark?'1':'0'); this.$nextTick(()=>window.lucide&&lucide.createIcons()); },
    }
  }
  function confirmModal(){
    return {
      visible:false, title:'¿Confirmar?', message:'', variant:'danger',
      confirmLabel:'Sí, continuar', cancelLabel:'Cancelar', _form:null,
      bind(){
        // Intercept submits on forms that opt-in via data-confirm
        document.addEventListener('submit', (e)=>{
          const form = e.target;
          if (!form || !form.hasAttribute('data-confirm')) return;
          if (form.dataset.confirmed === '1') return; // already confirmed
          e.preventDefault();
          this._form = form;
          this.message = form.dataset.confirm || '¿Estás seguro?';
          this.title = form.dataset.confirmTitle || '¿Confirmar acción?';
          this.variant = form.dataset.confirmVariant || 'danger';
          this.confirmLabel = form.dataset.confirmLabel || (this.variant==='danger' ? 'Sí, eliminar' : 'Continuar');
          this.cancelLabel = form.dataset.cancelLabel || 'Cancelar';
          this.visible = true;
          this.$nextTick(()=>window.lucide&&lucide.createIcons());
        }, true);
      },
      open(detail){
        this.title = detail.title || '¿Confirmar?';
        this.message = detail.message || '';
        this.variant = detail.variant || 'danger';
        this.confirmLabel = detail.confirmLabel || 'Continuar';
        this.cancelLabel = detail.cancelLabel || 'Cancelar';
        this._form = null;
        this._cb = detail.onConfirm || null;
        this.visible = true;
        this.$nextTick(()=>window.lucide&&lucide.createIcons());
      },
      confirm(){
        const form = this._form;
        const cb = this._cb;
        this.visible = false;
        if (form){ form.dataset.confirmed='1'; form.submit(); }
        if (cb) try { cb(); } catch(e){}
        this._form=null; this._cb=null;
      },
      cancel(){ this.visible = false; this._form=null; this._cb=null; }
    }
  }
  function demoCountdown(initial){
    return {
      left: initial, display: '',
      tick(){
        const fmt = ()=>{
          const s = Math.max(0, this.left|0);
          const h = Math.floor(s/3600), m = Math.floor((s%3600)/60), sec = s%60;
          this.display = String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(sec).padStart(2,'0');
        };
        fmt();
        const id = setInterval(()=>{
          this.left -= 1;
          if (this.left <= 0){ clearInterval(id); window.location.href='<?= url('/login') ?>'; }
          fmt();
        }, 1000);
      }
    }
  }
  function cmdPalette(){
    return {
      open:false, query:'', groups:[], active:0, total:0, loading:false, _seq:0,
      recent: JSON.parse(localStorage.getItem('kyros-cmd-recent') || '[]').slice(0,5),
      actions: [
        { title:'Nueva reserva',      icon:'calendar-plus', url:'<?= url('/admin/reservations/create') ?>', kbd:'C R' },
        { title:'Nuevo vehículo',     icon:'car',           url:'<?= url('/admin/vehicles/create') ?>',     kbd:'C V' },
        { title:'Nuevo cliente',      icon:'user-plus',     url:'<?= url('/admin/customers/create') ?>',    kbd:'C C' },
        { title:'Registrar pago',     icon:'credit-card',   url:'<?= url('/admin/payments/create') ?>',     kbd:'C P' },
        { title:'Nuevo chofer',       icon:'id-card',       url:'<?= url('/admin/drivers/create') ?>' },
        { title:'Nuevo código promo', icon:'ticket-percent',url:'<?= url('/admin/promos/create') ?>' },
      ],
      pages: [
        { title:'Dashboard',     icon:'layout-dashboard', url:'<?= url('/admin/dashboard') ?>' },
        { title:'Reservas',      icon:'calendar-check',   url:'<?= url('/admin/reservations') ?>' },
        { title:'Calendario',    icon:'calendar',         url:'<?= url('/admin/reservations/calendar') ?>' },
        { title:'Flotilla',      icon:'car',              url:'<?= url('/admin/vehicles') ?>' },
        { title:'Choferes',      icon:'id-card',          url:'<?= url('/admin/drivers') ?>' },
        { title:'Clientes',      icon:'users',            url:'<?= url('/admin/customers') ?>' },
        { title:'Contratos',     icon:'file-text',        url:'<?= url('/admin/contracts') ?>' },
        { title:'Pagos',         icon:'credit-card',      url:'<?= url('/admin/payments') ?>' },
        { title:'Facturas',      icon:'receipt',          url:'<?= url('/admin/invoices') ?>' },
        { title:'Gastos',        icon:'trending-down',    url:'<?= url('/admin/expenses') ?>' },
        { title:'Cierre de caja',icon:'calculator',       url:'<?= url('/admin/cashbox') ?>' },
        { title:'Reportes',      icon:'bar-chart-3',      url:'<?= url('/admin/reports') ?>' },
        { title:'Sucursales',    icon:'map-pin',          url:'<?= url('/admin/locations') ?>' },
        { title:'Mantenimiento', icon:'wrench',           url:'<?= url('/admin/maintenance') ?>' },
        { title:'Incidencias',   icon:'shield-alert',     url:'<?= url('/admin/incidents') ?>' },
        { title:'Promociones',   icon:'ticket-percent',   url:'<?= url('/admin/promos') ?>' },
        { title:'Equipo',        icon:'users-round',      url:'<?= url('/admin/users') ?>' },
        { title:'Actividad',     icon:'history',          url:'<?= url('/admin/activity') ?>' },
        { title:'Configuración', icon:'settings',         url:'<?= url('/admin/settings') ?>' },
        { title:'Tu plan',       icon:'crown',            url:'<?= url('/admin/upgrade') ?>' },
      ],
      flatIndex(g,i){ let n=0; for (let k=0;k<g;k++) n += (this.groups[k]?.items?.length || 0); return n + i; },
      toggle(force){ this.open = force === true ? true : !this.open; if (this.open) this.$nextTick(()=>{ this.$refs.cmdInput.focus(); window.lucide&&lucide.createIcons(); }); },
      async run(){
        const q = this.query.trim();
        if (q.length < 2) { this.groups=[]; this.total=0; this.active=0; return; }
        const seq = ++this._seq;
        this.loading = true;
        try {
          const r = await fetch('<?= url('/admin/search') ?>?q=' + encodeURIComponent(q), { headers:{'Accept':'application/json'} });
          if (!r.ok) throw new Error('search failed');
          const j = await r.json();
          if (seq !== this._seq) return;
          this.groups = j.groups || [];
          this.total = this.groups.reduce((s,g)=>s+g.items.length,0);
          this.active = 0;
        } catch(e){ this.groups=[]; this.total=0; }
        finally { if (seq === this._seq) this.loading=false; this.$nextTick(()=>window.lucide&&lucide.createIcons()); }
      },
      move(d){
        const n = this.query.length < 2
          ? this.recent.length + this.actions.length + this.pages.length
          : this.total;
        if (!n) return;
        this.active = (this.active + d + n) % n;
        this.$nextTick(()=>{
          const links = document.querySelectorAll('[x-data="cmdPalette()"] a');
          links[this.active]?.scrollIntoView({block:'nearest'});
        });
      },
      go(){
        const links = document.querySelectorAll('[x-data="cmdPalette()"] a');
        if (links[this.active]) { window.location.href = links[this.active].href; }
      },
      remember(item){
        if (!item || !item.url) return;
        let r = JSON.parse(localStorage.getItem('kyros-cmd-recent') || '[]');
        r = r.filter(x => x.url !== item.url);
        r.unshift({ title:item.title, subtitle:item.subtitle || '', url:item.url, icon:item.icon || 'circle' });
        localStorage.setItem('kyros-cmd-recent', JSON.stringify(r.slice(0,8)));
        this.recent = r.slice(0,5);
      }
    }
  }
  document.addEventListener('DOMContentLoaded',()=>window.lucide&&lucide.createIcons());
  document.addEventListener('alpine:initialized',()=>window.lucide&&lucide.createIcons());
</script>
<?= $pageScripts ?? '' ?>
<?= View::stack('scripts') ?>
</body>
</html>
