{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
{!! '<'.'?xml-stylesheet type="text/css" href="'.e(route('sitemap.css')).'"?>' !!}
{{-- Browsers run this XHTML script (links and readable dates); search engines ignore it. --}}
@php($script = '<script xmlns="http://www.w3.org/1999/xhtml" src="'.e(route('sitemap.js')).'"></script>')
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($pages as $page)
    <url>
        <loc>{{ $page->url() }}</loc>
        <lastmod>{{ $lastmod[$page->id] }}</lastmod>
@if ($page->sitemap_priority !== null)
        <priority>{{ number_format((float) $page->sitemap_priority, 1) }}</priority>
@elseif ($page->isHome())
        <priority>1.0</priority>
@endif
@if ($loop->last && collect($authors ?? [])->isEmpty())
        {!! $script !!}
@endif
    </url>
@endforeach
@foreach ($authors ?? [] as $author)
    <url>
        <loc>{{ $author->url() }}</loc>
@if ($author->last_post_at)
        <lastmod>{{ $author->last_post_at }}</lastmod>
@endif
@if ($loop->last)
        {!! $script !!}
@endif
    </url>
@endforeach
</urlset>
