<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id', 'parent_id', 'page_id', 'label', 'url', 'icon', 'image_id', 'description',
        'open_in_new_tab', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['open_in_new_tab' => 'boolean', 'is_active' => 'boolean'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /**
     * Linked page URL wins over a custom URL so links survive slug changes.
     */
    public function href(): ?string
    {
        if ($this->page) {
            return $this->page->url();
        }

        if (! $this->url || $this->url === '#') {
            return null;
        }

        return preg_match('#^(https?:|mailto:|tel:|\#)#i', $this->url) ? $this->url : url($this->url);
    }

    public function isCurrent(): bool
    {
        $href = $this->href();

        return $href && rtrim($href, '/') === rtrim(url()->current(), '/');
    }
}
