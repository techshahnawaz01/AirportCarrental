<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EDITOR = 'editor';

    /** Writes and publishes their own blog posts only (like the WordPress Author role). */
    public const ROLE_AUTHOR = 'author';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_EDITOR => 'Editor',
        self::ROLE_AUTHOR => 'Author',
    ];

    /** Profile links shown on the author page, in display order. */
    public const SOCIAL = [
        'website' => 'Website',
        'facebook' => 'Facebook',
        'twitter' => 'X (Twitter)',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
    ];

    protected $fillable = ['name', 'slug', 'avatar_id', 'job_title', 'bio', 'social', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'social' => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Every user gets a unique URL slug from their name unless one is set.
        static::saving(function (User $user) {
            if (blank($user->slug) || $user->isDirty('slug')) {
                $user->slug = static::uniqueSlug($user->slug ?: $user->name, $user->id);
            }
        });
    }

    public static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'author';
        $slug = $base;

        for ($i = 2; static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Page::class, 'author_id');
    }

    /** Only users with at least one published blog post have a public author page. */
    public function scopeWithPublicProfile(Builder $query): Builder
    {
        return $query->whereHas('posts', fn ($q) => $q->published()->where('type', 'post'));
    }

    public function url(): string
    {
        return route('author', $this->slug);
    }

    /** @return array<string, string> Filled social links, keyed like self::SOCIAL. */
    public function socialLinks(): array
    {
        return collect(self::SOCIAL)->keys()
            ->mapWithKeys(fn ($key) => [$key => (string) ($this->social[$key] ?? '')])
            ->filter()
            ->all();
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isAuthor(): bool
    {
        return $this->role === self::ROLE_AUTHOR;
    }

    /** Administrators and editors manage all content; authors only their own posts. */
    public function canManageAllContent(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_EDITOR], true);
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
