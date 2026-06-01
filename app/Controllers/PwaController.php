<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Config;

/**
 * Progressive Web App endpoints — served dynamically so every URL is
 * base-path aware (local XAMPP lives under /kyros-rentcar-saas/public,
 * production is mounted at the docroot). All three responses are public,
 * tenant-agnostic and cacheable: one installable app for the whole SaaS.
 *
 *   GET /manifest.webmanifest  -> web app manifest (JSON)
 *   GET /sw.js                 -> service worker (JS, scope = app base path)
 *   GET /offline               -> offline fallback page (served by the SW)
 */
class PwaController extends Controller
{
    /** Cache-busting token derived from the build's compiled CSS + this file. */
    private function buildToken(): string
    {
        $root = rtrim((string) Config::get('app.root_path', ''), '/\\');
        $parts = [];
        foreach (['public/assets/css/tailwind.css', 'app/Controllers/PwaController.php'] as $rel) {
            $f = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
            if (is_file($f)) { $parts[] = (string) filemtime($f); }
        }
        return substr(md5(implode('|', $parts) ?: 'kyros'), 0, 10);
    }

    public function manifest(Request $request): void
    {
        $name = (string) Config::get('app.name', 'Kyros Rent Car');
        $short = (string) Config::get('app.short_name', 'Kyros');
        $manifest = [
            'id'               => url('/'),
            'name'             => $name,
            'short_name'       => $short,
            'description'      => 'Gestiona tu rent car: flotilla, reservas, contratos, pagos y tu pagina publica desde un solo lugar.',
            'lang'             => 'es',
            'dir'              => 'ltr',
            'start_url'        => url('/?source=pwa'),
            'scope'            => url('/'),
            'display'          => 'standalone',
            'display_override' => ['window-controls-overlay', 'standalone', 'minimal-ui'],
            'orientation'      => 'any',
            'theme_color'      => '#0E1422',
            'background_color' => '#0E1422',
            'categories'       => ['business', 'productivity', 'utilities'],
            'icons' => [
                ['src' => asset('img/icon-192.png'),          'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('img/icon-512.png'),          'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('img/icon-maskable-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                [
                    'name'       => 'Dashboard',
                    'short_name' => 'Inicio',
                    'url'        => url('/dashboard?source=pwa'),
                    'icons'      => [['src' => asset('img/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png']],
                ],
                [
                    'name'       => 'Reservas',
                    'short_name' => 'Reservas',
                    'url'        => url('/admin/reservations?source=pwa'),
                    'icons'      => [['src' => asset('img/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png']],
                ],
                [
                    'name'       => 'Nueva reserva',
                    'short_name' => 'Reservar',
                    'url'        => url('/admin/reservations/create?source=pwa'),
                    'icons'      => [['src' => asset('img/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png']],
                ],
            ],
        ];

        header('Content-Type: application/manifest+json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public function serviceWorker(Request $request): void
    {
        $base    = rtrim(url('/'), '/');           // '' in prod, '/kyros-rentcar-saas/public' local
        $offline = url('/offline');
        $version = 'kyros-pwa-' . $this->buildToken();

        // Core shell precached on install so the offline page renders fully styled.
        $precache = array_values(array_unique([
            $offline,
            asset('css/tailwind.css'),
            asset('js/alpine.min.js'),
            asset('js/lucide.min.js'),
            asset('img/icon-192.png'),
            asset('img/icon-512.png'),
            asset('fonts/inter-latin-wght-normal.woff2'),
            asset('fonts/plus-jakarta-sans-latin-wght-normal.woff2'),
        ]));

        $cfg = json_encode([
            'version'  => $version,
            'base'     => $base,
            'offline'  => $offline,
            'assets'   => $base . '/assets/',
            'precache' => $precache,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        header('Content-Type: text/javascript; charset=utf-8');
        header('Cache-Control: no-cache, max-age=0');
        header('Service-Worker-Allowed: ' . ($base === '' ? '/' : $base . '/'));

        echo <<<JS
/* Kyros Rent Car — service worker. Auto-generated by PwaController. */
'use strict';
const CFG = {$cfg};
const CACHE = CFG.version;

// ---- install: precache the app shell ----
self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    const cache = await caches.open(CACHE);
    // Tolerate individual asset failures (versioned URLs, optional fonts).
    await Promise.all(CFG.precache.map((url) =>
      cache.add(new Request(url, { cache: 'reload' })).catch(() => null)
    ));
    self.skipWaiting();
  })());
});

// ---- activate: drop stale caches, take control ----
self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)));
    await self.clients.claim();
  })());
});

self.addEventListener('message', (event) => {
  if (event.data === 'SKIP_WAITING') self.skipWaiting();
});

function isAsset(url) {
  return url.pathname.startsWith(CFG.assets);
}

// Stale-while-revalidate for static assets: instant from cache, refreshed in bg.
async function staleWhileRevalidate(request) {
  const cache = await caches.open(CACHE);
  const cached = await cache.match(request);
  const network = fetch(request).then((resp) => {
    if (resp && resp.ok && resp.type === 'basic') cache.put(request, resp.clone());
    return resp;
  }).catch(() => null);
  return cached || network || fetch(request);
}

// Network-first for page navigations: always fresh; offline -> fallback page.
async function navigateFirst(request) {
  try {
    return await fetch(request);
  } catch (e) {
    const cache = await caches.open(CACHE);
    const offline = await cache.match(CFG.offline);
    return offline || new Response('Sin conexion', { status: 503, headers: { 'Content-Type': 'text/plain; charset=utf-8' } });
  }
}

self.addEventListener('fetch', (event) => {
  const request = event.request;
  if (request.method !== 'GET') return;                 // never touch POST/PUT/DELETE
  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;      // skip cross-origin (CDNs, analytics)

  // Don't intercept the SW endpoints themselves.
  if (url.pathname === CFG.offline || url.pathname.endsWith('/sw.js') ||
      url.pathname.endsWith('/manifest.webmanifest')) {
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(navigateFirst(request));
    return;
  }
  if (isAsset(url)) {
    event.respondWith(staleWhileRevalidate(request));
  }
  // Everything else (dynamic GET JSON: charts, search, events) -> default network.
});
JS;
        exit;
    }

    public function offline(Request $request): void
    {
        // Standalone page (no layout) so the SW can serve it with zero deps.
        header('Content-Type: text/html; charset=utf-8');
        $this->view('public/offline', [], null);
        exit;
    }
}
