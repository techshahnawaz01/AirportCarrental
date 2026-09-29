<?php

namespace App\Shortcodes;

use App\Models\Page;

class LatestPostsShortcode implements Shortcode
{
    public function render(array $attributes, ?string $content, ?Page $page): string
    {
        $parentId = isset($attributes['parent'])
            ? Page::where('path', trim($attributes['parent'], '/'))->value('id')
            : settings('general.blog_page_id');

        $posts = Page::published()->with('featuredImage')
            ->when($parentId, fn ($q) => $q->where('parent_id', $parentId), fn ($q) => $q->where('type', 'post'))
            ->latest('published_at')->latest('id')
            ->limit(min((int) ($attributes['limit'] ?? 5), 12))
            ->get();

        if ($posts->isEmpty()) {
            return '';
        }

        return view('frontend.shortcodes.latest-posts', [
            'posts' => $posts,
            'title' => $attributes['title'] ?? 'Latest from the blog',
            'moreUrl' => $parentId ? Page::find($parentId)?->url() : null,
        ])->render();
    }
}
