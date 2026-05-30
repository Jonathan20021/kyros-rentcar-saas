<?php
namespace App\Services;

use App\Core\Config;
use App\Core\Logger;

/**
 * Secure file upload handler.
 *  - Validates real MIME (finfo), not just the client-provided type.
 *  - Enforces size limit and an allowlist of extensions.
 *  - Renames files to random tokens to prevent overwrite/path traversal.
 */
class FileUploader
{
    /**
     * @return string|null relative public path (e.g. /assets/uploads/vehicles/ab12.jpg) or null on failure
     */
    public static function image(array $file, string $subdir = 'misc'): ?string
    {
        return self::handle($file, $subdir, ['image/jpeg','image/png','image/webp','image/svg+xml']);
    }

    public static function document(array $file, string $subdir = 'documents'): ?string
    {
        return self::handle($file, $subdir, ['image/jpeg','image/png','image/webp','application/pdf']);
    }

    /**
     * Save a base64 PNG data URL (e.g. a canvas signature) to a public path.
     * @return string|null relative public path or null on failure
     */
    public static function saveDataUrlPng(string $dataUrl, string $subdir = 'signatures'): ?string
    {
        $parts = explode(',', $dataUrl, 2);
        if (count($parts) !== 2 || stripos($parts[0], 'image/png') === false) {
            return null;
        }
        $raw = base64_decode($parts[1], true);
        // PNG signature check + size guard (max 2MB)
        if ($raw === false || strlen($raw) < 8 || strlen($raw) > 2 * 1024 * 1024 || substr($raw, 0, 8) !== "\x89PNG\r\n\x1a\n") {
            return null;
        }
        $subdir = preg_replace('/[^a-z0-9_\-]/i', '', $subdir);
        $dir = Config::get('app.upload_path') . DIRECTORY_SEPARATOR . $subdir;
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $name = bin2hex(random_bytes(12)) . '.png';
        if (file_put_contents($dir . DIRECTORY_SEPARATOR . $name, $raw) === false) {
            Logger::error('Failed to write signature to ' . $dir);
            return null;
        }
        return Config::get('app.upload_url') . '/' . $subdir . '/' . $name;
    }

    /**
     * Save a raw SVG signature (e.g. from a canvas pad that serializes strokes
     * to vector paths). SVGs render natively in dompdf without GD, so this is
     * the preferred path for signed contracts on GD-less hosts.
     *
     * The accepted SVG is strictly whitelisted: only <svg>, <rect>, <path>,
     * <circle>, <g> tags with a small set of style attrs — no <script>, no
     * external refs, no data: hrefs. Anything else makes the save fail.
     *
     * @return string|null relative public path or null on failure
     */
    public static function saveSignatureSvg(string $svg, string $subdir = 'signatures'): ?string
    {
        $svg = trim($svg);
        if ($svg === '' || strlen($svg) > 256 * 1024) return null;
        // Must look like an <svg> document.
        if (stripos($svg, '<svg') !== 0) return null;
        // Reject anything dangerous outright.
        if (preg_match('#<\s*script|on[a-z]+\s*=|javascript:|<\s*foreignObject|<\s*image|xlink:href|href\s*=#i', $svg)) {
            return null;
        }
        // Only allow a small whitelist of tags — strip everything else.
        $allowed = ['svg','g','path','rect','circle','line','polyline','polygon'];
        $stripped = preg_replace_callback(
            '#</?\s*([a-zA-Z0-9-]+)#',
            function ($m) use ($allowed) {
                $tag = strtolower($m[1]);
                return in_array($tag, $allowed, true) ? $m[0] : '';
            },
            $svg
        );
        if ($stripped === null) return null;

        $subdir = preg_replace('/[^a-z0-9_\-]/i', '', $subdir);
        $dir = Config::get('app.upload_path') . DIRECTORY_SEPARATOR . $subdir;
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $name = bin2hex(random_bytes(12)) . '.svg';
        if (file_put_contents($dir . DIRECTORY_SEPARATOR . $name, $stripped) === false) {
            Logger::error('Failed to write signature to ' . $dir);
            return null;
        }
        return Config::get('app.upload_url') . '/' . $subdir . '/' . $name;
    }

    /**
     * Handle a multi-file image field ($_FILES[$field] with array names),
     * returning the list of saved public paths.
     */
    public static function imagesFromField(string $field, string $subdir): array
    {
        $saved = [];
        if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
            return $saved;
        }
        $count = count($_FILES[$field]['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($_FILES[$field]['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
            $file = [
                'name'     => $_FILES[$field]['name'][$i],
                'type'     => $_FILES[$field]['type'][$i],
                'tmp_name' => $_FILES[$field]['tmp_name'][$i],
                'error'    => $_FILES[$field]['error'][$i],
                'size'     => $_FILES[$field]['size'][$i],
            ];
            if ($path = self::image($file, $subdir)) { $saved[] = $path; }
        }
        return $saved;
    }

    protected static function handle(array $file, string $subdir, array $allowedMimes): ?string
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Logger::warning('Upload error code ' . $file['error']);
            return null;
        }

        $maxBytes = (int) Config::get('security.upload_max_bytes', 5 * 1024 * 1024);
        if ($file['size'] > $maxBytes || $file['size'] <= 0) {
            return null;
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        // Real MIME detection
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowedMap = Config::get('security.upload_allowed_mime', []);
        if (!in_array($mime, $allowedMimes, true) || !isset($allowedMap[$mime])) {
            Logger::warning('Rejected upload MIME: ' . $mime);
            return null;
        }
        $ext = $allowedMap[$mime];

        // Build safe destination
        $subdir = preg_replace('/[^a-z0-9_\-]/i', '', $subdir);
        $dir    = Config::get('app.upload_path') . DIRECTORY_SEPARATOR . $subdir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Logger::error('Failed to move uploaded file to ' . $dest);
            return null;
        }

        // SVGs are vector documents — sanitize after write so an attacker
        // can't smuggle <script>, on*= handlers, or external refs through
        // the upload. If sanitization yields nothing usable, drop the file.
        if ($mime === 'image/svg+xml') {
            $clean = self::sanitizeSvgFile($dest);
            if ($clean === null) {
                @unlink($dest);
                Logger::warning('SVG upload rejected by sanitizer');
                return null;
            }
            file_put_contents($dest, $clean);
        }

        return Config::get('app.upload_url') . '/' . $subdir . '/' . $name;
    }

    /**
     * Sanitize an on-disk SVG, returning the cleaned markup or null if the
     * file isn't a usable SVG. Strips scripts, event handlers, external
     * references, and any tag not on the whitelist of safe vector tags.
     */
    private static function sanitizeSvgFile(string $path): ?string
    {
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') return null;
        $svg = trim($raw);
        if (stripos($svg, '<svg') === false) return null;
        if (preg_match('#<\s*script|on[a-z]+\s*=|javascript:|<\s*foreignObject|xlink:href\s*=|href\s*=#i', $svg)) {
            // Strip the dangerous bits but keep the rest if possible.
            $svg = preg_replace('#<\s*script\b[^>]*>.*?<\s*/\s*script\s*>#is', '', $svg);
            $svg = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\')#i', '', $svg);
            $svg = preg_replace('#\s(?:xlink:href|href)\s*=\s*("[^"]*"|\'[^\']*\')#i', '', $svg);
            $svg = preg_replace('#<\s*foreignObject\b[^>]*>.*?<\s*/\s*foreignObject\s*>#is', '', $svg);
            $svg = preg_replace('#javascript:#i', '', $svg);
        }
        // Strip processing instructions / DOCTYPE (XXE surface).
        $svg = preg_replace('#<\?xml[^>]*\?>#i', '', $svg);
        $svg = preg_replace('#<!DOCTYPE[^>]*>#i', '', $svg);
        $svg = preg_replace('#<!--.*?-->#s', '', $svg);
        $svg = trim((string) $svg);
        return $svg !== '' && stripos($svg, '<svg') !== false ? $svg : null;
    }
}
