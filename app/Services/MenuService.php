<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    /** @var array<string, Collection> */
    private array $loaded = [];

    /**
     * Active root items (with active children) for a menu location.
     */
    public function items(string $location): Collection
    {
        return $this->loaded[$location] ??= Cache::rememberForever("cms.menu.{$location}", function () use ($location) {
            $menu = Menu::where('location', $location)->first();

            if (! $menu) {
                return collect();
            }

            return $menu->rootItems()
                ->where('is_active', true)
                ->with([
                    'page:id,path,title,is_active',
                    'image',
                    'children' => fn ($q) => $q->where('is_active', true)->with('page:id,path,title,is_active', 'image'),
                ])
                ->get();
        });
    }

    public function flush(): void
    {
        foreach (array_keys(config('cms.menu_locations')) as $location) {
            Cache::forget("cms.menu.{$location}");
        }

        $this->loaded = [];
    }
}
