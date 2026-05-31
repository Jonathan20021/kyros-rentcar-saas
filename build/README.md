# Kyros — Self-hosted assets build

The app no longer depends on runtime CDNs (Tailwind Play CDN, Google Fonts,
Alpine/Lucide/Chart.js/GSAP/AOS/FullCalendar). Everything is compiled/copied into
`public/assets/` and served by the app itself, so pages always render fully
styled with no external dependency.

## When do I need to rebuild?

Only the **CSS** needs rebuilding, and only when you add/observe Tailwind utility
classes that weren't used anywhere before (Tailwind ships just the classes it finds
in `app/Views` + `app/Helpers`). Editing text, data, or existing classes needs no
rebuild.

## Rebuild the CSS

Requires Node.js (used 20.x).

```bash
cd build
npm install        # first time only
npm run build      # writes ../public/assets/css/tailwind.css (minified)
# or, while working:
npm run watch
```

## Brand color is still dynamic

`brand` / `brand2` resolve to `rgb(var(--brand-rgb) / <alpha>)`. The per-tenant
accent is injected as `--brand-rgb` / `--brand2-rgb` (space-separated RGB) in
`app/Views/layouts/_assets.php`, so `bg-brand`, `text-brand`, `bg-brand/10`, etc.
keep following each tenant's color from a single static stylesheet.

## Vendored files (committed under public/assets/)

| File | Source package |
|------|----------------|
| `css/tailwind.css` | compiled here from `input.css` |
| `css/aos.css` | aos 2.3.4 |
| `fonts/inter-*.woff2` | @fontsource-variable/inter 5.1.1 (latin + latin-ext, variable) |
| `fonts/plus-jakarta-sans-*.woff2` | @fontsource-variable/plus-jakarta-sans 5.1.1 |
| `js/alpine.min.js` | alpinejs 3.14.8 |
| `js/lucide.min.js` | lucide 0.469.0 |
| `js/chart.umd.min.js` | chart.js 4.4.1 |
| `js/gsap.min.js`, `js/ScrollTrigger.min.js` | gsap 3.12.5 |
| `js/aos.min.js` | aos 2.3.4 |
| `js/fullcalendar.global.min.js` | fullcalendar 6.1.11 |

To refresh a vendored lib: `npm install` here, then copy the dist file from
`node_modules/...` into `public/assets/js` (or `/css`). See the commit that
introduced this folder for the exact copy commands.

## Note

`build/node_modules/` is git-ignored — only the source config and the compiled
outputs under `public/assets/` are committed and deployed.
