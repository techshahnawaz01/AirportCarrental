{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($pages as $page)
    <url>
        <loc>{{ $page->url() }}</loc>
        <lastmod>{{ $page->updated_at?->toAtomString() }}</lastmod>
@if ($page->sitemap_priority !== null)
        <priority>{{ $page->sitemap_priority }}</priority>
@elseif ($page->isHome())
        <priority>1.0</priority>
@endif
    </url>
@endforeach
</urlset>
