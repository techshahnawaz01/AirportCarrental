<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
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
