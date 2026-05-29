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
        return self::handle($file, $subdir, ['image/jpeg','image/png','image/webp']);
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

        return Config::get('app.upload_url') . '/' . $subdir . '/' . $name;
    }
}
