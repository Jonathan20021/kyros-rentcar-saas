/**
 * Tailwind build config for the SELF-HOSTED stylesheet.
 * Mirrors the theme that used to live inline in app/Views/layouts/_assets.php.
 * Run from this folder:  npm install && npm run build
 * Output: ../public/assets/css/tailwind.css
 */
module.exports = {
  darkMode: 'class',
  // Paths are relative to this build/ folder (Tailwind resolves from CWD).
  content: [
    '../app/Views/**/*.php',
    '../app/Helpers/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        // Dynamic brand — driven by --brand-rgb / --brand2-rgb (set per-tenant in
        // _assets.php) so bg-brand, text-brand, bg-brand/10, ring-brand/30, ... all
        // follow the tenant accent while staying a static compiled stylesheet.
        brand:  'rgb(var(--brand-rgb) / <alpha-value>)',
        brand2: 'rgb(var(--brand2-rgb) / <alpha-value>)',
        ink: '#1C2433', navy: '#1C2433',
        paper: '#EEF2F8', line: '#E6EAF1',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
      },
      boxShadow: {
        xs: '0 1px 2px rgba(28,36,51,.06)',
        card: '0 1px 3px rgba(28,36,51,.05), 0 6px 16px -10px rgba(28,36,51,.12)',
        soft: '0 12px 36px -16px rgba(28,36,51,.20)',
        lift: '0 26px 64px -24px rgba(28,36,51,.34)',
      },
      borderRadius: { lg: '.65rem', xl: '.85rem', '2xl': '1.1rem', '3xl': '1.4rem', '4xl': '1.8rem' },
    },
  },
  plugins: [],
}
