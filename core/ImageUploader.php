<?php

namespace Core;

/**
 * Result object returned by ImageUploader::handle().
 */
class ImageUploadResult
{
    private bool   $filePresent;
    private bool   $errorFlag;
    private string $errorMsg;
    private string $path;
    private array  $thumbs;

    public function __construct(bool $filePresent, bool $errorFlag, string $errorMsg, string $path, array $thumbs)
    {
        $this->filePresent = $filePresent;
        $this->errorFlag   = $errorFlag;
        $this->errorMsg    = $errorMsg;
        $this->path        = $path;
        $this->thumbs      = $thumbs;
    }

    /** True when no file was included in the request (field was empty). */
    public function isEmpty(): bool { return !$this->filePresent; }

    /** True when a file was present but failed validation or processing. */
    public function hasError(): bool { return $this->filePresent && $this->errorFlag; }

    /** Human-readable error message (non-empty only when hasError() is true). */
    public function getError(): string { return $this->errorMsg; }

    /**
     * Relative path from public/ to the original uploaded file.
     * Example: 'uploads/personas/abc_1713100000.jpg'
     * Empty string if no file was uploaded.
     */
    public function getPath(): string { return $this->path; }

    /**
     * Associative array of generated thumbnails keyed by '{W}x{H}'.
     * Example: ['130x130' => 'uploads/personas/thumbs/abc_130x130.jpg']
     */
    public function getThumbs(): array { return $this->thumbs; }
}

/**
 * Handles file upload, validates MIME type and size, and generates
 * configurable GD thumbnails. Validation only runs when a file is
 * actually submitted; empty fields are silently ignored.
 *
 * Usage:
 *   $uploader = new ImageUploader([
 *       'module'       => 'personas',           // subfolder under public/uploads/
 *       'allowedTypes' => ['image/jpeg', ...],
 *       'maxSize'      => 2097152,              // bytes
 *       'thumbs'       => [
 *           ['w' => 130, 'h' => 130, 'mode' => 'crop'],
 *           ['w' => 300, 'h' => 300, 'mode' => 'fit'],
 *       ],
 *   ]);
 *   $result = $uploader->handle('field_name');
 *
 * Thumb modes:
 *   'crop' — center-crop to exact W×H (default)
 *   'fit'  — scale to fit inside W×H, preserving aspect ratio (no crop)
 */
class ImageUploader
{
    private string $module;
    private array  $allowedTypes;
    private int    $maxSize;
    private int    $maxWidth;
    private int    $maxHeight;
    private int    $maxPixels;
    private bool   $stripMetadata;
    private array  $thumbs;
    private string $publicBase;

    public function __construct(array $config)
    {
        $this->module       = trim((string) ($config['module'] ?? 'uploads'), '/');
        $this->allowedTypes = (array) ($config['allowedTypes'] ?? ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        $this->maxSize      = (int)   ($config['maxSize']      ?? 2097152);
        $this->maxWidth     = max(1, (int) ($config['maxWidth'] ?? 5000));
        $this->maxHeight    = max(1, (int) ($config['maxHeight'] ?? 5000));
        $this->maxPixels    = max(1, (int) ($config['maxPixels'] ?? 25000000));
        $this->stripMetadata = (bool) ($config['stripMetadata'] ?? true);
        $this->thumbs       = (array) ($config['thumbs']       ?? []);
        $this->publicBase   = rtrim((string) ($config['publicBase'] ?? (dirname(__DIR__) . '/public')), '/');
    }

    // -------------------------------------------------------------------------

    public function handle(string $field): ImageUploadResult
    {
        $noFile = new ImageUploadResult(false, false, '', '', []);

        if (!isset($_FILES[$field]) || (string) ($_FILES[$field]['tmp_name'] ?? '') === '') {
            return $noFile;
        }

        $file     = $_FILES[$field];
        $tmpName  = (string) ($file['tmp_name'] ?? '');
        $origName = (string) ($file['name']     ?? '');
        $size     = (int)    ($file['size']     ?? 0);
        $error    = (int)    ($file['error']    ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE || $tmpName === '') {
            return $noFile;
        }

        if ($error !== UPLOAD_ERR_OK) {
            return $this->fail('Error al subir el archivo. Codigo: ' . $error);
        }

        // --- Size check ---
        if ($size > $this->maxSize) {
            $mb = number_format($this->maxSize / 1048576, 1);
            return $this->fail("El archivo no debe superar los {$mb} MB.");
        }

        // --- MIME check (server-side, not trusting extension) ---
        $mime = (string) \mime_content_type($tmpName);
        if (!in_array($mime, $this->allowedTypes, true)) {
            $allowed = implode(', ', array_map(
                fn(string $t) => strtoupper(substr($t, (int) strpos($t, '/') + 1)),
                $this->allowedTypes
            ));
            return $this->fail("Tipo de archivo no permitido. Use: {$allowed}.");
        }

        // --- Dimensions check (mitigates oversized image/pixel bombs) ---
        $imageInfo = @\getimagesize($tmpName);
        if ($imageInfo === false) {
            return $this->fail('El archivo no es una imagen valida.');
        }

        $width  = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        $infoMime = strtolower((string) ($imageInfo['mime'] ?? ''));

        if ($width < 1 || $height < 1) {
            return $this->fail('No se pudieron leer las dimensiones de la imagen.');
        }

        if ($infoMime !== '' && $infoMime !== strtolower($mime)) {
            return $this->fail('El contenido de la imagen no coincide con su tipo declarado.');
        }

        if ($width > $this->maxWidth || $height > $this->maxHeight) {
            return $this->fail("La imagen excede las dimensiones maximas permitidas ({$this->maxWidth}x{$this->maxHeight}px).");
        }

        $pixels = $width * $height;
        if ($pixels > $this->maxPixels) {
            return $this->fail('La imagen contiene demasiados pixeles para ser procesada.');
        }

        // --- Prepare destination ---
        $ext      = strtolower((string) pathinfo($origName, PATHINFO_EXTENSION));
        $baseName = \bin2hex(\random_bytes(8)) . '_' . time();
        $fileName = $baseName . '.' . $ext;

        $uploadDir = $this->publicBase . '/uploads/' . $this->module;
        if (!\is_dir($uploadDir) && !\mkdir($uploadDir, 0755, true)) {
            return $this->fail('No se pudo crear el directorio de subida.');
        }

        $destPath = $uploadDir . '/' . $fileName;
        if ($this->stripMetadata && $this->canReencodeMime($mime)) {
            if (!$this->reencodeUploadedImage($tmpName, $destPath, $mime)) {
                return $this->fail('No se pudo procesar la imagen subida.');
            }
        } else {
            if (!\move_uploaded_file($tmpName, $destPath)) {
                return $this->fail('No se pudo guardar el archivo.');
            }
        }

        $relativePath = 'uploads/' . $this->module . '/' . $fileName;

        // --- Generate thumbnails ---
        $thumbResults = [];
        foreach ($this->thumbs as $thumb) {
            $w    = (int)    ($thumb['w']    ?? 100);
            $h    = (int)    ($thumb['h']    ?? 100);
            $mode = (string) ($thumb['mode'] ?? 'crop');
            $key  = "{$w}x{$h}";

            $thumbDir = $uploadDir . '/thumbs';
            if (!\is_dir($thumbDir) && !\mkdir($thumbDir, 0755, true)) {
                continue;
            }

            $thumbFile = $baseName . "_{$key}." . $ext;
            $thumbDest = $thumbDir . '/' . $thumbFile;

            if ($this->generateThumb($destPath, $thumbDest, $mime, $w, $h, $mode)) {
                $thumbResults[$key] = 'uploads/' . $this->module . '/thumbs/' . $thumbFile;
            }
        }

        return new ImageUploadResult(true, false, '', $relativePath, $thumbResults);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function fail(string $msg): ImageUploadResult
    {
        return new ImageUploadResult(true, true, $msg, '', []);
    }

    private function canReencodeMime(string $mime): bool
    {
        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function reencodeUploadedImage(string $tmpName, string $destPath, string $mime): bool
    {
        if (!\extension_loaded('gd') || !\is_uploaded_file($tmpName)) {
            return false;
        }

        $srcImg = match ($mime) {
            'image/jpeg' => @\imagecreatefromjpeg($tmpName),
            'image/png'  => @\imagecreatefrompng($tmpName),
            'image/webp' => function_exists('imagecreatefromwebp') ? @\imagecreatefromwebp($tmpName) : false,
            default      => false,
        };

        if ($srcImg === false) {
            return false;
        }

        if ($mime === 'image/png' || $mime === 'image/webp') {
            \imagealphablend($srcImg, false);
            \imagesavealpha($srcImg, true);
        }

        $ok = match ($mime) {
            'image/jpeg' => \imagejpeg($srcImg, $destPath, 88),
            'image/png'  => \imagepng($srcImg, $destPath, 6),
            'image/webp' => function_exists('imagewebp') ? \imagewebp($srcImg, $destPath, 85) : false,
            default      => false,
        };

        \imagedestroy($srcImg);

        return (bool) $ok;
    }

    private function generateThumb(string $src, string $dest, string $mime, int $dstW, int $dstH, string $mode): bool
    {
        if (!\extension_loaded('gd')) {
            return false;
        }

        $srcImg = match ($mime) {
            'image/jpeg' => @\imagecreatefromjpeg($src),
            'image/png'  => @\imagecreatefrompng($src),
            'image/gif'  => @\imagecreatefromgif($src),
            'image/webp' => @\imagecreatefromwebp($src),
            default      => false,
        };

        if ($srcImg === false) {
            return false;
        }

        $srcW = \imagesx($srcImg);
        $srcH = \imagesy($srcImg);

        // Calculate source rectangle and final canvas size
        if ($mode === 'fit') {
            // Scale to fit inside dstW × dstH, preserving aspect ratio
            $ratio   = min($dstW / $srcW, $dstH / $srcH);
            $canvasW = (int) round($srcW * $ratio);
            $canvasH = (int) round($srcH * $ratio);
            $srcX = 0; $srcY = 0;
            $srcCropW = $srcW; $srcCropH = $srcH;
        } else {
            // crop: center-crop source to fill dstW × dstH exactly
            $ratio    = max($dstW / $srcW, $dstH / $srcH);
            $srcCropW = (int) round($dstW / $ratio);
            $srcCropH = (int) round($dstH / $ratio);
            $srcX     = (int) round(($srcW - $srcCropW) / 2);
            $srcY     = (int) round(($srcH - $srcCropH) / 2);
            $canvasW  = $dstW;
            $canvasH  = $dstH;
        }

        $dstImg = \imagecreatetruecolor($canvasW, $canvasH);
        if ($dstImg === false) {
            \imagedestroy($srcImg);
            return false;
        }

        // Preserve transparency for PNG and GIF
        if (in_array($mime, ['image/png', 'image/gif'], true)) {
            \imagealphablend($dstImg, false);
            \imagesavealpha($dstImg, true);
            $transparent = \imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
            if ($transparent !== false) {
                \imagefill($dstImg, 0, 0, $transparent);
            }
        }

        \imagecopyresampled($dstImg, $srcImg, 0, 0, $srcX, $srcY, $canvasW, $canvasH, $srcCropW, $srcCropH);

        $ok = match ($mime) {
            'image/jpeg' => \imagejpeg($dstImg, $dest, 85),
            'image/png'  => \imagepng($dstImg, $dest, 7),
            'image/gif'  => \imagegif($dstImg, $dest),
            'image/webp' => \imagewebp($dstImg, $dest, 85),
            default      => false,
        };

        \imagedestroy($srcImg);
        \imagedestroy($dstImg);

        return (bool) $ok;
    }
}
