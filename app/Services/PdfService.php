<?php
namespace App\Services;

use App\Core\Config;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Server-side PDF generation via dompdf.
 *
 * dompdf has limitations vs. modern browsers — no flex/grid, no `color-mix`,
 * limited gradient support. Templates rendered here MUST use table-based
 * layouts, inline styles, and hex colors only.
 */
class PdfService
{
    /**
     * Render HTML to a dompdf PDF and return the raw bytes.
     * Local file:// images (the tenant logo) are enabled — needed to embed
     * the rent car's logo without fetching over HTTP.
     */
    public static function render(string $html, string $size = 'A4', string $orient = 'portrait'): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);       // allow https:// (e.g. qr code)
        $options->set('chroot', Config::get('app.root_path')); // file:// must stay under project root
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('defaultMediaType', 'screen');
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper($size, $orient);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();
        return $dompdf->output();
    }

    /** True if PHP can render raster images (PNG/JPG) inside the PDF. */
    public static function canRenderImages(): bool
    {
        return extension_loaded('gd');
    }

    /**
     * Build a base64 data URI from a path that is either:
     *  - absolute filesystem (C:\xampp\...\file.png)
     *  - app-relative URL (/assets/uploads/logos/x.png) — looked up under public/
     *  - already a data: URI / remote URL — returned unchanged
     *
     * dompdf renders SVG natively (via php-svg-lib) without GD, but raster
     * formats (PNG/JPEG/WEBP) need GD inside CPDF::image. When GD is missing
     * we skip raster files so the render doesn't crash — the <img> simply
     * disappears and the surrounding layout stays intact.
     */
    public static function embedImage(?string $path): string
    {
        if (!$path) return '';
        if (preg_match('#^(data:image/svg|https?://)#i', $path)) return $path;
        if (preg_match('#^data:image/#i', $path) && !self::canRenderImages()) return '';
        if (preg_match('#^data:#i', $path)) return $path;

        $abs = null;
        // /assets/... → public/assets/...
        if (str_starts_with($path, '/')) {
            $candidate = Config::get('app.root_path') . DIRECTORY_SEPARATOR . 'public' . str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (is_file($candidate)) $abs = $candidate;
        }
        if (!$abs && is_file($path)) $abs = $path;
        if (!$abs) return '';

        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        // dompdf's CPDF backend renders JPEG and SVG WITHOUT GD (JPEG via
        // addJpegFromFile, SVG via php-svg-lib). PNG/WEBP/GIF go through
        // addPngFromFile which hard-requires the GD extension — skip those
        // when GD isn't loaded so the render doesn't crash.
        $needsGd = in_array($ext, ['png','webp','gif','bmp'], true);
        if ($needsGd && !self::canRenderImages()) return '';

        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp'        => 'image/webp',
            'svg'         => 'image/svg+xml',
            default       => 'image/png',
        };

        $raw = @file_get_contents($abs);
        if ($raw === false) return '';
        return 'data:' . $mime . ';base64,' . base64_encode($raw);
    }

    /**
     * Render a QR code as a `data:image/svg+xml;base64,…` URI suitable for
     * `<img src="…">`. Works WITHOUT GD — dompdf routes the SVG through
     * php-svg-lib, which handles vector geometry natively.
     */
    public static function qrSvgDataUri(string $data, string $fg = '#0E1422'): string
    {
        if (!class_exists(\chillerlan\QRCode\QRCode::class)) {
            return '';
        }
        $opts = new \chillerlan\QRCode\QROptions([
            'outputType'  => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'    => \chillerlan\QRCode\QRCode::ECC_M,
            'imageBase64' => false,
            'svgViewBoxSize' => 30,
            'addQuietzone'   => true,
            'quietzoneSize'  => 2,
            'markupDark'  => $fg,
            'markupLight' => '#FFFFFF',
            'cssClass'    => '',
            'svgUseFillAttributes' => true,
        ]);
        try {
            $svg = (new \chillerlan\QRCode\QRCode($opts))->render($data);
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Build a brand-styled "logo fallback" SVG with the tenant's initials
     * when we don't have a usable raster logo (e.g. GD is missing, or the
     * tenant didn't upload one). Two-letter initials, a brand-color tile, a
     * darker bottom accent strip, and a small corner mark — reads as a real
     * brand mark instead of a generic placeholder.
     */
    public static function brandSvgDataUri(string $name, string $bg = '#F23645', string $fg = '#FFFFFF', int $size = 96): string
    {
        $name = trim($name);
        // Pull up to two initials from the tenant name, falling back to "K".
        $words = preg_split('/\s+/u', $name) ?: [];
        $first = mb_substr($words[0] ?? 'K', 0, 1);
        $second = mb_substr($words[1] ?? '', 0, 1);
        $initials = htmlspecialchars(mb_strtoupper($first . $second), ENT_QUOTES, 'UTF-8');
        if ($initials === '') $initials = 'K';

        // Darker shade of the brand color for the bottom accent strip.
        $hex = ltrim($bg, '#');
        if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        $rR = hexdec(substr($hex, 0, 2));
        $gG = hexdec(substr($hex, 2, 2));
        $bB = hexdec(substr($hex, 4, 2));
        $deep = sprintf('#%02X%02X%02X', max(0, (int)($rR * .72)), max(0, (int)($gG * .72)), max(0, (int)($bB * .72)));

        $r = (int) round($size * 0.18);            // corner radius
        $stripH = (int) round($size * 0.18);       // bottom accent strip height
        $stripY = $size - $stripH;
        $dotR   = (int) round($size * 0.045);
        $dotX   = $size - (int) round($size * 0.16);
        $dotY   = (int) round($size * 0.16);
        $fontSize = strlen($initials) > 1 ? (int) round($size * 0.42) : (int) round($size * 0.55);
        $textY = strlen($initials) > 1 ? (int) round($size * 0.50) : (int) round($size * 0.54);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$size} {$size}" width="{$size}" height="{$size}">
  <rect x="0" y="0" width="{$size}" height="{$size}" rx="{$r}" ry="{$r}" fill="{$bg}"/>
  <rect x="0" y="{$stripY}" width="{$size}" height="{$stripH}" fill="{$deep}"/>
  <rect x="0" y="0" width="{$size}" height="{$size}" rx="{$r}" ry="{$r}" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="1"/>
  <circle cx="{$dotX}" cy="{$dotY}" r="{$dotR}" fill="{$fg}" opacity="0.55"/>
  <text x="50%" y="{$textY}" text-anchor="middle" dominant-baseline="middle"
        font-family="Helvetica,Arial,sans-serif" font-size="{$fontSize}" font-weight="700" letter-spacing="-1" fill="{$fg}">{$initials}</text>
</svg>
SVG;
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /** Stream the PDF inline to the browser with a filename. */
    public static function stream(string $pdfBytes, string $filename): void
    {
        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . strlen($pdfBytes));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $pdfBytes;
    }
}
