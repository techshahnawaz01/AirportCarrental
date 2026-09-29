<?php

namespace App\Shortcodes;

use App\Models\Page;
use App\Services\MenuService;

class LinkCardsShortcode implements Shortcode
{
    public function __construct(private readonly MenuService $menus) {}

    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        $items = $this->menus->items($attributes['menu'] ?? 'featured');

        if ($items->isEmpty()) {
            return '';
        }

        return view('frontend.shortcodes.link-cards', [
            'items' => $items,
            'style' => in_array($attributes['style'] ?? 'images', ['images', 'icons'], true) ? $attributes['style'] ?? 'images' : 'images',
            'title' => $attributes['title'] ?? null,
            'subtitle' => $attributes['subtitle'] ?? null,
        ])->render();
    }
}
