<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $homeId = (int) settings('general.home_page_id');
        $pages = $this->indexablePages(['id', 'path', 'type', 'created_at', 'updated_at', 'published_at', 'sitemap_priority'])
            ->sortBy(fn ($page) => $page->id === $homeId ? '' : $page->path)
            ->values();

        return response()
            ->view('frontend.seo.sitemap', ['pages' => $pages], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Human-friendly sitemap: pages grouped by top-level section.
     */
    public function sitemapPage(SeoService $seo): View
    {
        $pages = $this->indexablePages(['id', 'parent_id', 'title', 'path', 'type', 'updated_at']);
        $homeId = (int) settings('general.home_page_id');
        $byParent = $pages->groupBy(fn ($page) => (int) $page->parent_id);

        // Top-level pages with children become sections; the rest are grouped as "General".
        $sections = $pages->whereNull('parent_id')->where('id', '!=', $homeId)
            ->filter(fn ($page) => $byParent->has($page->id))
            ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn ($page) => ['page' => $page, 'children' => $this->descendants($page->id, $byParent)])
            ->values();

        $general = $pages->whereNull('parent_id')
            ->reject(fn ($page) => $page->id === $homeId || $byParent->has($page->id))
            ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return view('frontend.seo.sitemap-page', [
            'sections' => $sections,
            'general' => $general,
            'total' => $pages->count(),
            'seo' => $seo->forPage(null, ['title' => 'Sitemap – '.settings('branding.site_name'), 'description' => 'Every page on '.settings('branding.site_name').', grouped by section.', 'canonical' => route('sitemap.page')]),
        ]);
    }

    /**
     * Flattened descendants with their depth, sorted alphabetically at each level.
     */
    private function descendants(int $parentId, Collection $byParent, int $depth = 0): Collection
    {
        return collect($byParent->get($parentId, []))
            ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)
            ->flatMap(fn ($page) => collect([['page' => $page, 'depth' => $depth]])
                ->merge($depth < 4 ? $this->descendants($page->id, $byParent, $depth + 1) : []));
    }

    private function indexablePages(array $columns): Collection
    {
        return Page::published()
            ->where('noindex', false)
            ->whereNull('canonical_url')
            ->orderBy('path')
            ->get($columns);
    }

    public function robots(): Response
    {
        $lines = settings()->bool('seo.indexing_enabled')
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /search', 'Disallow: /widgets']
            : ['User-agent: *', 'Disallow: /'];

        if ($extra = trim((string) settings('seo.robots_txt'))) {
            $lines[] = '';
            $lines[] = $extra;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
