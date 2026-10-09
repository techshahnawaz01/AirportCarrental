<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class Media extends Model
{
    protected $fillable = [
        'disk', 'path', 'original_name', 'mime_type', 'size', 'width', 'height', 'alt', 'hash', 'uploaded_by',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return ['size' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /** Path of the smaller copy made for phones (see MediaService::createVariant()). */
    public function variantPath(): string
    {
        $width = (int) config('cms.uploads.variant_width', 960);

        return preg_replace('/(\.[a-z0-9]+)$/i', "-{$width}w$1", $this->path);
    }

    /**
     * srcset offering the phone-sized copy next to the original, or null when there is no copy
     * (small images, or uploads made before copies existed; run `php artisan cms:image-variants`).
     */
    public function srcset(): ?string
    {
        $width = (int) config('cms.uploads.variant_width', 960);

        if (! $this->isImage() || ! $this->width || $this->width <= $width || ! Storage::disk($this->disk)->exists($this->variantPath())) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->variantPath())." {$width}w, {$this->url} {$this->width}w";
    }

    /** srcset + sizes attributes for an <img>, or nothing when there is no smaller copy. */
    public function responsive(string $sizes): HtmlString
    {
        $srcset = $this->srcset();

        return new HtmlString($srcset ? 'srcset="'.e($srcset).'" sizes="'.e($sizes).'"' : '');
    }

    /** <link rel="preload"> for an above-the-fold (LCP) image, matching the <img> srcset/sizes. */
    public function preloadTag(string $sizes = '100vw'): HtmlString
    {
        $srcset = $this->srcset();

        return new HtmlString('<link rel="preload" as="image" href="'.e($this->url).'"'
            .($srcset ? ' imagesrcset="'.e($srcset).'" imagesizes="'.e($sizes).'"' : '')
            .' fetchpriority="high">');
    }

    /**
     * Root-relative URL for use inside stored content, so it survives domain changes.
     */
    public function relativeUrl(): string
    {
        return parse_url($this->url, PHP_URL_PATH) ?: $this->url;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(fn ($q) => $q
            ->where('original_name', 'like', '%'.$term.'%')
            ->orWhere('alt', 'like', '%'.$term.'%')));
    }

    public function toPickerArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'src' => $this->relativeUrl(),
            'path' => $this->path,
            'name' => $this->original_name,
            'alt' => $this->alt,
            'size' => $this->humanSize(),
            'dimensions' => $this->width ? $this->width.'×'.$this->height : null,
        ];
    }
}
