<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BlogThumbnailService
{
    private const DIRECTORY = 'media-library';
    private const SIZE = 800;
    private const JPEG_QUALITY = 86;
    private const WEBP_QUALITY = 82;

    public function store(UploadedFile $file): string
    {
        $this->ensureGdIsAvailable();

        $sourceImage = $this->createImageFromUpload($file);
        $processedImage = $this->createSquareThumbnail($sourceImage);

        try {
            $extension = $this->supportsWebpOutput() ? 'webp' : 'jpg';
            $path = $this->uniqueImagePath($file->getClientOriginalName(), $extension);
            $stored = Storage::disk('public')->put($path, $this->encodeImage($processedImage, $extension));

            if (! $stored) {
                throw ValidationException::withMessages([
                    'thumbnail' => 'Nepavyko išsaugoti optimizuotos miniatiūros.',
                ]);
            }

            return $path;
        } finally {
            imagedestroy($sourceImage);
            imagedestroy($processedImage);
        }
    }

    private function ensureGdIsAvailable(): void
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagecreatetruecolor')) {
            throw ValidationException::withMessages([
                'thumbnail' => 'Nuotraukų apdorojimui serveryje turi būti įjungtas PHP GD plėtinys.',
            ]);
        }
    }

    private function createImageFromUpload(UploadedFile $file): \GdImage
    {
        $contents = file_get_contents($file->getRealPath());
        $image = $contents !== false ? @imagecreatefromstring($contents) : false;

        if (! $image instanceof \GdImage) {
            throw ValidationException::withMessages([
                'thumbnail' => 'Nepavyko apdoroti įkeltos nuotraukos. Įkelkite JPG, PNG arba WebP failą.',
            ]);
        }

        return $image;
    }

    private function createSquareThumbnail(\GdImage $sourceImage): \GdImage
    {
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $cropSize = min($sourceWidth, $sourceHeight);
        $cropX = (int) (($sourceWidth - $cropSize) / 2);
        $cropY = (int) (($sourceHeight - $cropSize) / 2);
        $thumbnail = imagecreatetruecolor(self::SIZE, self::SIZE);

        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);

        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefilledrectangle($thumbnail, 0, 0, self::SIZE, self::SIZE, $transparent);

        imagecopyresampled(
            $thumbnail,
            $sourceImage,
            0,
            0,
            $cropX,
            $cropY,
            self::SIZE,
            self::SIZE,
            $cropSize,
            $cropSize
        );

        return $thumbnail;
    }

    private function encodeImage(\GdImage $image, string $extension): string
    {
        ob_start();

        $imageToEncode = $extension === 'webp' ? $image : $this->flattenOnWhite($image);
        $encoded = $extension === 'webp'
            ? imagewebp($imageToEncode, null, self::WEBP_QUALITY)
            : imagejpeg($imageToEncode, null, self::JPEG_QUALITY);

        $contents = ob_get_clean();

        if ($imageToEncode !== $image) {
            imagedestroy($imageToEncode);
        }

        if (! $encoded || $contents === false) {
            throw ValidationException::withMessages([
                'thumbnail' => 'Nepavyko išsaugoti optimizuotos miniatiūros.',
            ]);
        }

        return $contents;
    }

    private function flattenOnWhite(\GdImage $image): \GdImage
    {
        $flattened = imagecreatetruecolor(self::SIZE, self::SIZE);
        $white = imagecolorallocate($flattened, 255, 255, 255);

        imagefilledrectangle($flattened, 0, 0, self::SIZE, self::SIZE, $white);
        imagecopy($flattened, $image, 0, 0, 0, 0, self::SIZE, self::SIZE);

        return $flattened;
    }

    private function supportsWebpOutput(): bool
    {
        return function_exists('imagewebp');
    }

    private function uniqueImagePath(string $originalName, string $extension): string
    {
        $filename = $this->sanitizeImageFilename(pathinfo($originalName, PATHINFO_FILENAME));
        $path = self::DIRECTORY.'/'.$filename.'.'.$extension;
        $counter = 1;

        while (Storage::disk('public')->exists($path)) {
            $path = self::DIRECTORY.'/'.$filename.'_'.$counter.'.'.$extension;
            $counter++;
        }

        return $path;
    }

    private function sanitizeImageFilename(string $filename): string
    {
        $filename = (string) Str::of($filename)
            ->ascii()
            ->lower()
            ->replaceMatches('/\s+/', '-')
            ->replaceMatches('/[^a-z0-9_-]+/', '')
            ->replaceMatches('/-+/', '-')
            ->trim('-_');

        return $filename !== '' ? $filename : 'thumbnail';
    }
}
