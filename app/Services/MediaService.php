<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
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
        return $this->store($file->getRealPath(), $file->getClientOriginalName(), $folder, $userId, $alt);
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
        Storage::disk($media->disk)->delete($media->path);

        // Clear any image settings that still point at this file.
        $keys = collect($this->settings->all())->filter(fn ($value) => $value === $media->path)->keys();
        foreach ($keys as $key) {
            $this->settings->set([$key => null]);
        }

        $media->delete();
    }

    private function store(string $sourcePath, string $originalName, ?string $folder, ?int $userId, ?string $alt): Media
    {
        $this->downscale($sourcePath);

        $hash = sha1_file($sourcePath);
        if ($existing = Media::where('hash', $hash)->first()) {
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

        return Media::create([
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
    }

    /**
     * Downscale oversized JPEG/PNG/WebP images in place when GD is available.
     */
    private function downscale(string $path): void
    {
        $max = (int) config('cms.uploads.max_dimension', 2400);
        $info = @getimagesize($path);

        if (! $info || ! function_exists('imagecreatetruecolor') || max($info[0], $info[1]) <= $max) {
            return;
        }

        [$width, $height, $type] = $info;
        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $source) {
            return;
        }

        $ratio = $max / max($width, $height);
        $target = imagecreatetruecolor((int) round($width * $ratio), (int) round($height * $ratio));
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);

        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($target, $path, 85),
            IMAGETYPE_PNG => imagepng($target, $path, 8),
            IMAGETYPE_WEBP => imagewebp($target, $path, 85),
        };

        imagedestroy($source);
        imagedestroy($target);
    }
}
