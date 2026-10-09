<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\User;
use App\Services\PageService;
use App\Services\SeoService;
use Carbon\CarbonInterface;
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

        // Listing pages change when what they show changes: a parent lists its children,
        // and pages with a [latest_posts] block show the newest posts.
        $live = Page::published()->get(['id', 'parent_id', 'type', 'author_id', 'created_at', 'updated_at', 'published_at']);
        $childChange = $live->whereNotNull('parent_id')->groupBy('parent_id')->map(fn ($children) => $children->map->lastModified()->max());
        $posts = $live->where('type', 'post');
        $showsPosts = Page::published()->where('content', 'like', '%[latest_posts%')->pluck('id')->flip();

        $lastmod = $pages->mapWithKeys(fn ($page) => [$page->id => $this->w3cDate(collect([
            $page->lastModified(),
            $childChange[$page->id] ?? null,
            $showsPosts->has($page->id) ? $posts->map->lastModified()->max() : null,
        ])->filter()->max())]);

        // Author archives, dated by their latest published post.
        $authors = User::withPublicProfile()->orderBy('slug')->get(['id', 'slug'])
            ->each(fn ($author) => $author->last_post_at = ($latest = $posts->where('author_id', $author->id)->map->lastModified()->max()) ? $this->w3cDate($latest) : null);

        return response()
            ->view('frontend.seo.sitemap', ['pages' => $pages, 'lastmod' => $lastmod, 'authors' => $authors], 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * CSS that makes sitemap.xml readable in browsers. Search engines ignore it,
     * so the XML stays a standard sitemap. (CSS is used instead of XSLT, which
     * browsers are removing.)
     */
    public function sitemapStyles(): Response
    {
        return response()
            ->view('frontend.seo.sitemap-css', [], 200)
            ->header('Content-Type', 'text/css; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * <lastmod> value in the site's timezone. Dates imported without a time (stored as
     * midnight UTC) stay a plain date, so the day doesn't shift and no time is invented.
     */
    private function w3cDate(CarbonInterface $date): string
    {
        $utc = $date->copy()->utc();

        return $utc->format('H:i:s') === '00:00:00'
            ? $utc->toDateString()
            : $date->copy()->timezone(PageService::timezone(null))->toAtomString();
    }

    /** Script that makes sitemap.xml addresses clickable and dates readable in browsers. */
    public function sitemapScript(): Response
    {
        return response()
            ->view('frontend.seo.sitemap-js', [], 200)
            ->header('Content-Type', 'text/javascript; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
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
