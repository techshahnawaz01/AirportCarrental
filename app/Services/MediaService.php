<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores uploads on the public disk under media/{Y}/{m}/ (or a named folder),
 * de-duplicates identical files by SHA-1 and records them in the media table.
 */
class MediaService
{
    public function __construct(private readonly SettingsService $settings) {}

    public function upload(UploadedFile $file, ?string $folder = null, ?int $userId = null, ?string $alt = null): Media
    {
        return $this->store($file->getRealPath(), $file->getClientOriginalName(), $folder, $userId, $alt ?? $this->submittedAlt($file));
    }

    /**
     * Alt text typed next to an image field's upload button, sent as _alt[<file input in dot notation>].
     */
    private function submittedAlt(UploadedFile $file): ?string
    {
        $alts = request()->input('_alt');
        if (! is_array($alts)) {
            return null;
        }

        $key = collect(Arr::dot(request()->allFiles()))->search(fn ($uploaded) => $uploaded === $file);
        $alt = $key === false ? null : trim((string) ($alts[$key] ?? ''));

        return filled($alt) ? Str::limit(strip_tags($alt), 255, '') : null;
    }

    /**
     * Import a file that already exists on the local filesystem (seeders, importers).
     */
    public function importFromPath(string $absolutePath, ?string $originalName = null, ?string $folder = null, ?string $alt = null): Media
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException("File not found: {$absolutePath}");
        }

        return $this->store($absolutePath, $originalName ?? basename($absolutePath), $folder, null, $alt);
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete([$media->path, $media->variantPath()]);

        // Clear any image settings that still point at this file.
        $keys = collect($this->settings->all())->filter(fn ($value) => $value === $media->path)->keys();
        foreach ($keys as $key) {
            $this->settings->set([$key => null]);
        }

        $media->delete();
    }

    private function store(string $sourcePath, string $originalName, ?string $folder, ?int $userId, ?string $alt): Media
    {
        // Resize a temporary copy, so importing files that ship with the code never rewrites them.
        $copy = tempnam(sys_get_temp_dir(), 'media');
        copy($sourcePath, $copy);

        try {
            return $this->storeFile($copy, $originalName, $folder, $userId, $alt);
        } finally {
            @unlink($copy);
        }
    }

    private function storeFile(string $sourcePath, string $originalName, ?string $folder, ?int $userId, ?string $alt): Media
    {
        // Logos, icons and author photos never display large, so they are kept small enough to load instantly.
        $small = in_array($folder, ['branding', 'avatars'], true);
        $this->downscale($sourcePath, $small
            ? (int) config('cms.uploads.branding_max_dimension', 480)
            : (int) config('cms.uploads.max_dimension', 2400));

        $hash = sha1_file($sourcePath);
        if ($existing = Media::where('hash', $hash)->first()) {
            if ($alt !== null && $alt !== $existing->alt) {
                $existing->update(['alt' => $alt]);
            }

            return $existing;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'bin';
        $directory = trim(config('cms.uploads.directory', 'media').'/'.($folder ?: now()->format('Y/m')), '/');
        $basename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'file';
        $path = $directory.'/'.Str::limit($basename, 80, '').'-'.Str::lower(Str::random(6)).'.'.$extension;

        $disk = Storage::disk('public');
        $stream = fopen($sourcePath, 'r');
        $disk->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $dimensions = @getimagesize($sourcePath) ?: [null, null];

        $media = Media::create([
            'disk' => 'public',
            'path' => $path,
            'original_name' => Str::limit($originalName, 250, ''),
            'mime_type' => mime_content_type($sourcePath) ?: 'application/octet-stream',
            'size' => filesize($sourcePath),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'alt' => $alt ?? Str::headline(pathinfo($originalName, PATHINFO_FILENAME)),
            'hash' => $hash,
            'uploaded_by' => $userId,
        ]);

        if (! $small) {
            $this->createVariant($media);
        }

        return $media;
    }

    /**
     * Save a copy of a large JPEG/PNG/WebP image at the phone-friendly width (config
     * cms.uploads.variant_width) next to the original, for srcset. Returns whether one was made.
     */
    public function createVariant(Media $media): bool
    {
        $width = (int) config('cms.uploads.variant_width', 960);
        $disk = Storage::disk($media->disk);

        if (! $media->isImage() || ! $media->width || $media->width <= $width || ! $disk->exists($media->path)) {
            return false;
        }

        $copy = tempnam(sys_get_temp_dir(), 'variant');
        file_put_contents($copy, $disk->get($media->path));

        try {
            $height = $media->height ? (int) ceil($media->height * $width / $media->width) : $width;
            if (! $this->resize($copy, $width, $height, (int) config('cms.uploads.variant_quality', 72))) {
                return false;
            }

            // Only worth serving when clearly lighter than the original (some originals are already very compressed).
            clearstatcache(true, $copy);
            if (filesize($copy) > 0.8 * $disk->size($media->path)) {
                return false;
            }

            return $disk->put($media->variantPath(), file_get_contents($copy));
        } finally {
            @unlink($copy);
        }
    }

    /**
     * Downscale JPEG/PNG/WebP images whose longest edge exceeds $max, in place, when GD is available.
     */
    private function downscale(string $path, int $max): void
    {
        $info = @getimagesize($path);

        if ($info && max($info[0], $info[1]) > $max) {
            $ratio = $max / max($info[0], $info[1]);
            $this->resize($path, (int) round($info[0] * $ratio), (int) round($info[1] * $ratio), 85);
        }
    }

    /** Resize a JPEG/PNG/WebP file in place. Returns false when GD or the format is unavailable. */
    private function resize(string $path, int $width, int $height, int $quality): bool
    {
        $info = @getimagesize($path);

        if (! $info || ! function_exists('imagecreatetruecolor')) {
            return false;
        }

        $type = $info[2];
        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $source) {
            return false;
        }

        $target = imagecreatetruecolor($width, $height);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

        $saved = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($target, $path, $quality),
            IMAGETYPE_PNG => imagepng($target, $path, 8),
            IMAGETYPE_WEBP => imagewebp($target, $path, $quality),
        };

        imagedestroy($source);
        imagedestroy($target);

        return $saved;
    }
}
