@extends('layouts.app')

@php
    // Highlight the last word of the headline, Tripzella-style.
    $words = preg_split('/\s+/', trim($page->title));
    $lastWord = count($words) > 1 ? array_pop($words) : null;
    $overlap = str_contains((string) $content, 'data-flight-widget');
@endphp

@section('content')
    @if ($page->featuredImage)
        @push('preload'){{ $page->featuredImage->preloadTag() }}@endpush
    @endif
    {{-- Above the fold: no reveal animation, so the headline and search paint immediately. --}}
    <section class="relative isolate overflow-hidden bg-primary-dark">
        @if ($page->featuredImage)
            <img src="{{ $page->featuredImage->url }}" {{ $page->featuredImage->responsive('100vw') }} alt="{{ $page->featuredImage->alt }}" class="absolute inset-0 -z-20 size-full object-cover" fetchpriority="high">
        @endif
        <div class="hero-overlay absolute inset-0 -z-10"></div>
        <div class="hero-blob top-10 left-10 -z-10 size-72 bg-white/10"></div>
        <div class="hero-blob right-10 bottom-10 -z-10 size-96 bg-accent/20"></div>
        <div class="hero-blob top-1/2 left-1/3 -z-10 size-48 bg-primary-light/25"></div>

        <div @class(['container-site relative pt-14 text-white sm:pt-20 lg:pt-24', 'pb-40 lg:pb-52' => $overlap, 'pb-20 lg:pb-28' => ! $overlap])>
            <ul class="flex flex-wrap items-center gap-2.5 text-xs font-medium text-white/90 sm:text-sm">
                @if ($tagline = settings('branding.tagline'))
                    <li class="eyebrow-light"><x-icon name="plane" class="size-4 text-accent" /> {{ $tagline }}</li>
                @endif
            </ul>

            <h1 class="mt-6 max-w-4xl text-4xl leading-[1.05] font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl xl:text-7xl">
                {{ implode(' ', $words) }}
                @if ($lastWord)<span class="relative whitespace-nowrap text-accent">{{ $lastWord }}<svg class="absolute -bottom-2 left-0 h-3 w-full text-accent/60" viewBox="0 0 200 12" preserveAspectRatio="none" aria-hidden="true"><path d="M2 9c50-6 146-8 196-3" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round"/></svg></span>@endif
            </h1>
            @if ($page->excerpt)
                <p class="mt-6 max-w-2xl text-lg leading-8 text-white/85 sm:text-xl">{{ $page->excerpt }}</p>
            @endif

            <form action="{{ route('search') }}" role="search" data-live-search class="relative mt-9 max-w-2xl">
                <label for="hero-search" class="sr-only">Search</label>
                <div class="flex items-center gap-2 rounded-2xl bg-white p-2 shadow-float ring-1 ring-white/20">
                    <span class="ml-2 hidden size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary sm:flex"><x-icon name="search" class="size-5" /></span>
                    <input id="hero-search" type="search" name="q" autocomplete="off" placeholder="Search parking, terminals, airlines…" aria-expanded="false" aria-controls="hero-search-results"
                           class="min-w-0 flex-1 border-0 bg-transparent px-2 py-3 text-base text-secondary placeholder:text-slate-400 focus:ring-0">
                    <button class="btn btn-accent btn-lg px-5 sm:px-7"><x-icon name="search" class="size-5 sm:hidden" /><span class="hidden sm:inline">Search</span></button>
                </div>
                <div id="hero-search-results" data-live-results role="listbox" hidden class="absolute inset-x-0 top-full z-20 mt-2 max-h-96 overflow-y-auto rounded-2xl border border-line bg-white p-1.5 text-left shadow-float"></div>
            </form>

            @if ($phone = site_phone())
                <p class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-white/80">
                    <span class="flex -space-x-1.5" aria-hidden="true">
                        @foreach (['bg-accent', 'bg-emerald-400', 'bg-sky-300'] as $dot)<span class="size-2.5 rounded-full {{ $dot }} ring-2 ring-primary"></span>@endforeach
                    </span>
                    Need a hand? Talk to a travel specialist:
                    <a href="{{ tel_href($phone) }}" class="font-semibold text-white underline decoration-accent decoration-2 underline-offset-4 hover:text-accent">{{ $phone }}</a>
                </p>
            @endif
        </div>
    </section>

    <div class="home-sections container-site space-y-20 pb-8 lg:space-y-28 @unless ($overlap) pt-16 lg:pt-24 @endunless">{!! $content !!}</div>

    @if ($page->faqs->isNotEmpty())
        <div class="bg-soft py-20 lg:py-24 mt-20 lg:mt-28">
            <div class="container-site">
                <x-site.faq-list :faqs="$page->faqs" subtitle="Quick answers to the questions travellers ask most." />
            </div>
        </div>
    @endif
@endsection
