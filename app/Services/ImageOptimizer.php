<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploaded photos. Large phone photos are shrunk (and turned into WebP)
 * so pages load quickly on slow mobile networks. Small photos are stored as they are.
 * If the GD extension is missing, the original file is stored.
 */
class ImageOptimizer
{
    private const MAX_UNCHANGED_BYTES = 400_000;

    /**
     * @return string|false the stored path, or false when storing failed
     */
    public function store(UploadedFile $file, string $directory, int $maxDimension = 1280): string|false
    {
        $diskName = config('filesystems.media_disk');
        $optimised = $this->optimise($file, $maxDimension);

        if ($optimised === null) {
            return $file->storePublicly($directory, $diskName);
        }

        $path = trim($directory, '/').'/'.Str::random(40).'.'.$optimised['extension'];

        return Storage::disk($diskName)->put($path, $optimised['contents'], 'public') ? $path : false;
    }

    /**
     * @return array{contents: string, extension: string}|null null means "store the original"
     */
    private function optimise(UploadedFile $file, int $maxDimension): ?array
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecopyresampled')) {
            return null;
        }

        $realPath = $file->getRealPath();
        $info = $realPath ? @getimagesize($realPath) : false;

        if ($info === false) {
            return null;
        }

        [$width, $height, $type] = $info;

        if (max($width, $height) <= $maxDimension && $file->getSize() <= self::MAX_UNCHANGED_BYTES) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($realPath));

        if ($source === false) {
            return null;
        }

        $source = $this->applyOrientation($source, $realPath, $type);
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();

        if (function_exists('imagewebp')) {
            imagewebp($canvas, null, 82);
            $extension = 'webp';
        } else {
            $canvas = $this->flattenOnWhite($canvas, $newWidth, $newHeight);
            imagejpeg($canvas, null, 82);
            $extension = 'jpg';
        }

        $contents = (string) ob_get_clean();

        return $contents === '' ? null : ['contents' => $contents, 'extension' => $extension];
    }

    /** Phone photos carry a rotation flag; apply it so pictures are not sideways. */
    private function applyOrientation(GdImage $image, string $path, int $type): GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $angle = match ($exif['Orientation'] ?? 1) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        return imagerotate($image, $angle, 0) ?: $image;
    }

    private function flattenOnWhite(GdImage $image, int $width, int $height): GdImage
    {
        $flat = imagecreatetruecolor($width, $height);
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $image, 0, 0, 0, 0, $width, $height);

        return $flat;
    }
}
