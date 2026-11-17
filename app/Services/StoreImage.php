<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class StoreImage
{
    public function savePrivate(UploadedFile $file, string $directory): string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('The GD WebP encoder is required to save payment proofs.');
        }

        $source = imagecreatefromstring($file->get());
        if ($source === false) {
            throw new RuntimeException('The uploaded image could not be decoded.');
        }

        ob_start();
        imagewebp($source, null, 84);
        $encoded = ob_get_clean();
        imagedestroy($source);
        if (! is_string($encoded) || $encoded === '') {
            throw new RuntimeException('The payment proof could not be encoded as WebP.');
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('local')->put($path, $encoded);

        return $path;
    }

    public function save(UploadedFile $file, string $directory, int $maxDimension = 1800): string
    {
        if (! function_exists('imagewebp')) {
            throw new RuntimeException('The GD WebP encoder is required to save store images.');
        }

        $source = imagecreatefromstring($file->get());
        if ($source === false) {
            throw new RuntimeException('The uploaded image could not be decoded.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagewebp($target, null, 84);
        $encoded = ob_get_clean();
        imagedestroy($source);
        imagedestroy($target);

        if (! is_string($encoded) || $encoded === '') {
            throw new RuntimeException('The image could not be encoded as WebP.');
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('uploads')->put($path, $encoded);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('uploads')->delete($path);
        }
    }
}
