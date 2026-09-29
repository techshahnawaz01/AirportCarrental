{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
{!! '<'.'?xml-stylesheet type="text/css" href="'.e(route('sitemap.css')).'"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($pages as $page)
    <url>
        <loc>{{ $page->url() }}</loc>
        <lastmod>{{ $page->lastModified()->toAtomString() }}</lastmod>
@if ($page->sitemap_priority !== null)
        <priority>{{ number_format((float) $page->sitemap_priority, 1) }}</priority>
@elseif ($page->isHome())
        <priority>1.0</priority>
@endif
    </url>
@endforeach
</urlset>
