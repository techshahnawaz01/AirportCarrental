@extends('layouts.app')

@section('content')
    {{-- Reading progress (driven by resources/js/frontend/guide.js) --}}
    <div class="fixed inset-x-0 top-0 z-50 h-1 bg-transparent" aria-hidden="true">
        <div data-reading-progress class="h-full w-0 bg-gradient-to-r from-primary to-accent transition-[width] duration-150"></div>
    </div>

    <x-site.page-hero :page="$page" :subtitle="$page->excerpt">
        <p class="mt-7 flex flex-wrap items-center gap-2.5 text-sm text-white/90">
            <span class="eyebrow-light"><x-icon name="clock" class="size-4 text-accent" />{{ max(1, (int) ceil(str_word_count(strip_tags($content)) / 220)) }} min read</span>
            <span class="eyebrow-light"><x-icon name="calendar" class="size-4 text-accent" />Updated {{ local_date($page->updated_at) }}</span>
        </p>
    </x-site.page-hero>

    <div class="container-site grid gap-10 py-12 lg:grid-cols-12 lg:gap-12 lg:py-20">
        @if (count($toc) > 1)
            <aside class="lg:col-span-3" data-toc>
                {{-- Mobile: collapsible; desktop: sticky with scroll-spy --}}
                <details class="group card-premium lg:hidden">
                    <summary class="flex cursor-pointer items-center justify-between px-4 py-3 font-semibold text-secondary">
                        On this page <x-icon name="chevron-down" class="size-4 transition group-open:rotate-180" />
                    </summary>
                    <nav aria-label="Table of contents (mobile)" class="border-t border-line px-2 py-2">
                        @include('frontend.partials.toc-links')
                    </nav>
                </details>
                <nav aria-label="Table of contents" class="card-premium sticky top-28 hidden max-h-[calc(100vh-8rem)] overflow-y-auto p-5 lg:block">
                    <p class="flex items-center gap-2 px-3 text-xs font-bold tracking-wider text-secondary uppercase"><x-icon name="list" class="size-4 text-accent" /> On this page</p>
                    <div class="mt-3 border-l border-line">@include('frontend.partials.toc-links')</div>
                </nav>
            </aside>
        @endif

        <article @class(['min-w-0', 'lg:col-span-6' => count($toc) > 1, 'lg:col-span-8' => count($toc) <= 1])>
            @if ($page->featuredImage)
                <img src="{{ $page->featuredImage->url }}" {{ $page->featuredImage->responsive('(min-width: 1024px) 760px, 100vw') }} alt="{{ $page->featuredImage->alt ?: $page->title }}" width="{{ $page->featuredImage->width }}" height="{{ $page->featuredImage->height }}" class="mb-10 aspect-video w-full rounded-3xl object-cover shadow-float">
            @endif
            <div class="content-prose">{!! $content !!}</div>

            @if ($page->faqs->isNotEmpty())
                <x-site.faq-list :faqs="$page->faqs" class="mt-14" />
            @endif

            @isset($comments)
                <x-site.comments :page="$page" :comments="$comments" />
            @endisset
        </article>

        <aside @class(['lg:col-span-3' => count($toc) > 1, 'lg:col-span-4' => count($toc) <= 1])>
            @include('frontend.partials.sidebar')
        </aside>
    </div>
@endsection
