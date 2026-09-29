<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id', 'author_id', 'type', 'template', 'title', 'slug', 'excerpt', 'content', 'data',
        'featured_image_id', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_image_id',
        'noindex', 'nofollow', 'sitemap_priority', 'is_active', 'allow_comments', 'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'noindex' => 'boolean',
            'nofollow' => 'boolean',
            'is_active' => 'boolean',
            'allow_comments' => 'boolean',
            'sitemap_priority' => 'decimal:1',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Keep the denormalised URL path in sync with the slug hierarchy.
        static::saving(function (Page $page) {
            $page->slug = Str::slug($page->slug ?: $page->title) ?: Str::random(8);
            $parentPath = $page->parent_id ? static::withTrashed()->whereKey($page->parent_id)->value('path') : null;
            $page->path = trim(($parentPath ? $parentPath.'/' : '').$page->slug, '/');
        });

        static::saved(function (Page $page) {
            if ($page->wasChanged('path')) {
                $page->children()->withTrashed()->get()->each->save();
            }
        });
    }

    /* ----------------------------------------------------------------- */
    /* Relationships */
    /* ----------------------------------------------------------------- */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order')->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /* ----------------------------------------------------------------- */
    /* Scopes */
    /* ----------------------------------------------------------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when($type, fn ($q) => $q->where('type', $type));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn ($q) => $q->where(fn ($q) => $q
            ->where('title', 'like', '%'.$term.'%')
            ->orWhere('path', 'like', '%'.$term.'%')));
    }

    /* ----------------------------------------------------------------- */
    /* Helpers */
    /* ----------------------------------------------------------------- */

    public function isHome(): bool
    {
        return (int) settings('general.home_page_id') === $this->id;
    }

    public function url(): string
    {
        // Root URL keeps its trailing slash (https://example.com/), the canonical form for a home page.
        return $this->isHome() ? url('/').'/' : url($this->path);
    }

    /**
     * Last content change, used for sitemap <lastmod>. Never empty.
     */
    public function lastModified(): Carbon
    {
        return $this->updated_at ?? $this->published_at ?? $this->created_at ?? now();
    }

    public function isPublished(): bool
    {
        return $this->is_active && (! $this->published_at || $this->published_at->isPast());
    }

    public function typeLabel(): string
    {
        return config("cms.page_types.{$this->type}.singular", Str::headline($this->type));
    }

    public function templateView(): string
    {
        $template = $this->template ?: config("cms.page_types.{$this->type}.default_template", 'default');

        return view()->exists("frontend.templates.{$template}")
            ? "frontend.templates.{$template}"
            : 'frontend.templates.default';
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data ?? [], $key, $default);
    }

    public function summary(int $words = 30): string
    {
        $text = $this->excerpt ?: strip_tags(preg_replace('/\[[^\]]+\]/', '', (string) $this->content));

        return Str::words(trim(preg_replace('/\s+/', ' ', html_entity_decode($text))), $words);
    }

    /**
     * Ancestors from the root down to (and excluding) this page.
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent && $ancestors->count() < 10) {
            $ancestors->prepend($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Pages that can be chosen as a parent (prevents cycles).
     */
    public static function parentOptions(?Page $exclude = null): Collection
    {
        return static::query()
            ->select('id', 'title', 'path', 'type')
            ->when($exclude, fn ($q) => $q
                ->whereKeyNot($exclude->id)
                ->where('path', 'not like', $exclude->path.'/%'))
            ->orderBy('path')
            ->get();
    }
}
