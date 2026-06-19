<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WebImageOptimizer
{
    private const WEBP_QUALITY = 80;

    public function store(
        UploadedFile $file,
        string $directory,
        ?int $maxLongestSide = null,
        ?int $maxWidth = null,
    ): string {
        if ($this->isSvg($file)) {
            return $file->store($directory, 'public');
        }

        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            throw new RuntimeException('GD with WEBP support is required to optimize uploaded images.');
        }

        $contents = file_get_contents($file->getRealPath());
        $image = $contents !== false ? @imagecreatefromstring($contents) : false;

        if ($image === false) {
            throw new RuntimeException('The uploaded image could not be processed.');
        }

        try {
            $image = $this->orientJpeg($image, $file);
            $resized = $this->resize($image, $maxLongestSide, $maxWidth);

            if ($resized === false) {
                Log::warning('Image resize failed; storing the original uploaded image.', [
                    'mime_type' => $file->getMimeType(),
                    'original_name' => $file->getClientOriginalName(),
                    'directory' => $directory,
                ]);

                return $this->storeOriginal($file, $directory);
            }

            $image = $resized;
            $relativePath = trim($directory, '/').'/'.Str::uuid().'.webp';
            $absolutePath = Storage::disk('public')->path($relativePath);

            if (! is_dir(dirname($absolutePath))) {
                mkdir(dirname($absolutePath), 0755, true);
            }

            if (! imagewebp($image, $absolutePath, self::WEBP_QUALITY)) {
                throw new RuntimeException('The optimized image could not be saved.');
            }

            return $relativePath;
        } finally {
            imagedestroy($image);
        }
    }

    private function isSvg(UploadedFile $file): bool
    {
        return $file->getMimeType() === 'image/svg+xml'
            || strtolower($file->getClientOriginalExtension()) === 'svg';
    }

    private function orientJpeg(\GdImage $image, UploadedFile $file): \GdImage
    {
        if ($file->getMimeType() !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => false,
        };

        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function resize(\GdImage $image, ?int $maxLongestSide, ?int $maxWidth): \GdImage|false
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = 1.0;

        if ($maxLongestSide && max($width, $height) > $maxLongestSide) {
            $scale = min($scale, $maxLongestSide / max($width, $height));
        }

        if ($maxWidth && $width > $maxWidth) {
            $scale = min($scale, $maxWidth / $width);
        }

        if ($scale >= 1) {
            return $image;
        }

        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($resized === false) {
            return false;
        }

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);

        $copied = imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height,
        );

        if (! $copied) {
            imagedestroy($resized);

            return false;
        }

        imagedestroy($image);

        return $resized;
    }

    private function storeOriginal(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        if ($path === false) {
            throw new RuntimeException('The original uploaded image could not be saved.');
        }

        return $path;
    }
}
