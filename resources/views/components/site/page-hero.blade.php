@props(['page' => null, 'title' => null, 'subtitle' => null, 'image' => null, 'crumbs' => null])
@php
    $title ??= $page?->title;
    $image ??= $page?->featuredImage?->url;
    $crumbs ??= $page ? $page->ancestors()->map(fn ($p) => ['label' => $p->title, 'url' => $p->url()])->push(['label' => $page->title])->all() : [['label' => $title]];
@endphp
<section class="relative isolate overflow-hidden bg-secondary">
    @if ($image)
        <img src="{{ $image }}" alt="" class="absolute inset-0 -z-10 size-full object-cover" fetchpriority="high">
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-secondary via-secondary/80 to-primary/60"></div>
    <div class="container-site py-14 text-white sm:py-20">
        <x-ui.breadcrumbs :items="$crumbs" :home="url('/')" class="text-white/90" />
        <h1 class="mt-4 max-w-4xl text-3xl font-extrabold tracking-tight text-balance sm:text-4xl lg:text-5xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-4 max-w-2xl text-base text-white/85 sm:text-lg">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
