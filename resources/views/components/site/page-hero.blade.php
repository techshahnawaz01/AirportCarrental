@props(['page' => null, 'title' => null, 'subtitle' => null, 'image' => null, 'crumbs' => null])
@php
    $title ??= $page?->title;
    // Only the page's own featured image has a phone-sized copy; a custom $image URL is used as is.
    $media = $image === null ? $page?->featuredImage : null;
    $image ??= $media?->url;
    $crumbs ??= $page ? $page->breadcrumbs() : [['label' => $title]];
@endphp
@if ($media)
    @push('preload'){{ $media->preloadTag() }}@endpush
@endif
<section class="relative isolate overflow-hidden bg-primary-dark">
    @if ($image)
        <img src="{{ $image }}" {{ $media?->responsive('100vw') }} alt="" class="absolute inset-0 -z-20 size-full object-cover" fetchpriority="high">
    @endif
    <div class="hero-overlay absolute inset-0 -z-10"></div>
    <div class="hero-blob -top-24 -left-16 -z-10 size-80 bg-white/10"></div>
    <div class="hero-blob -right-20 -bottom-32 -z-10 size-96 bg-accent/25"></div>
    <div class="container-site relative py-14 text-white sm:py-20 lg:py-24">
        <x-ui.breadcrumbs :items="$crumbs" :home="url('/')" class="inline-flex rounded-full bg-white/10 px-4 py-1.5 text-white/90 ring-1 ring-white/20" />
        <h1 class="mt-6 max-w-4xl text-3xl leading-[1.1] font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl xl:text-6xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-5 max-w-2xl text-base leading-7 text-white/85 sm:text-lg">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
    {{-- Soft curve into the page --}}
    <svg class="absolute inset-x-0 -bottom-px h-6 w-full text-page sm:h-10" viewBox="0 0 1440 40" preserveAspectRatio="none" aria-hidden="true"><path fill="currentColor" d="M0 40h1440V0c-240 26-480 40-720 40S240 26 0 0Z"/></svg>
</section>
