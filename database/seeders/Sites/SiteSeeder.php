<?php

namespace Database\Seeders\Sites;

use App\Models\Media;
use App\Models\Menu;
use App\Models\Page;
use App\Models\User;
use App\Services\MediaService;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

/**
 * Helpers for site content seeders. Extend this class to bootstrap a new
 * website: set settings, register images, create pages and menus.
 */
abstract class SiteSeeder extends Seeder
{
    /** Folder (relative to this file) holding the images this site ships with. */
    protected string $assets = '';

    /** @var array<string, Media> */
    private array $media = [];

    protected function settings(array $values): void
    {
        app(SettingsService::class)->set($values);
    }

    protected function image(string $file, ?string $alt = null, ?string $folder = null): Media
    {
        return $this->media[$file] ??= app(MediaService::class)->importFromPath(
            __DIR__.'/'.$this->assets.'/'.$file, $file, $folder, $alt
        );
    }

    protected function page(array $attributes, array $faqs = []): Page
    {
        $parent = null;
        if (isset($attributes['parent'])) {
            $parent = Page::where('path', $attributes['parent'])->firstOrFail();
            unset($attributes['parent']);
        }

        $page = Page::updateOrCreate(
            ['path' => trim(($parent ? $parent->path.'/' : '').$attributes['slug'], '/')],
            $attributes + [
                'parent_id' => $parent?->id,
                'type' => 'page',
                'template' => 'default',
                'is_active' => true,
                'published_at' => now(),
                'author_id' => User::where('role', User::ROLE_ADMIN)->value('id'),
            ],
        );

        $page->faqs()->delete();
        foreach ($faqs as $index => [$question, $answer]) {
            $page->faqs()->create(['question' => $question, 'answer' => e($answer), 'sort_order' => $index]);
        }

        return $page;
    }

    /**
     * $items: [['label', 'url' | 'page' => path, 'icon', 'image', 'description', 'children' => [...]], ...]
     */
    protected function menu(string $location, array $items): void
    {
        $menu = Menu::firstOrCreate(['location' => $location], ['name' => config("cms.menu_locations.{$location}", $location)]);
        $menu->items()->delete();

        $create = function (array $items, ?int $parentId) use (&$create, $menu) {
            foreach (array_values($items) as $order => $item) {
                $page = isset($item['page']) ? Page::where('path', trim($item['page'], '/'))->first() : null;
                $created = $menu->items()->create([
                    'parent_id' => $parentId,
                    'label' => $item['label'],
                    'page_id' => $page?->id,
                    'url' => $page ? null : ($item['url'] ?? (isset($item['page']) ? '/'.trim($item['page'], '/') : '#')),
                    'icon' => $item['icon'] ?? null,
                    'image_id' => isset($item['image']) ? $this->image($item['image'])->id : null,
                    'description' => $item['description'] ?? null,
                    'open_in_new_tab' => $item['new_tab'] ?? false,
                    'sort_order' => $order,
                ]);

                $create($item['children'] ?? [], $created->id);
            }
        };

        $create($items, null);
    }
}
