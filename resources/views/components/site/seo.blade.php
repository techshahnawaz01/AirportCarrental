@props(['seo' => []])
@php
    $seoService = app(\App\Services\SeoService::class);
    $seo = array_merge($seoService->forPage(null), $seo ?? []);
    // FAQs rendered anywhere in the page body (page FAQs, [faqs] blocks) become one FAQPage node.
    $seo['schema'] = $seoService->withRenderedFaqs($seo['schema'] ?? []);
@endphp
<title>{{ $seo['title'] }}</title>
@if ($seo['description'])<meta name="description" content="{{ $seo['description'] }}">@endif
@if ($seo['keywords'])<meta name="keywords" content="{{ $seo['keywords'] }}">@endif
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:site_name" content="{{ $seo['site_name'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
@if ($seo['description'])<meta property="og:description" content="{{ $seo['description'] }}">@endif
<meta property="og:url" content="{{ $seo['canonical'] }}">
@if ($seo['image'])<meta property="og:image" content="{{ $seo['image'] }}">@endif
<meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
@if ($seo['description'])<meta name="twitter:description" content="{{ $seo['description'] }}">@endif
@if ($seo['image'])<meta name="twitter:image" content="{{ $seo['image'] }}">@endif
@if (! empty($seo['schema']))
<script type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endif
