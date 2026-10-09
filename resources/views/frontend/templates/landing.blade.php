@extends('layouts.app')

@php($hero = $page->meta('hero', []))

@section('content')
    @if ($page->featuredImage)
        @push('preload'){{ $page->featuredImage->preloadTag() }}@endpush
    @endif
    <section class="relative isolate overflow-hidden bg-primary-dark">
        @if ($page->featuredImage)
            <img src="{{ $page->featuredImage->url }}" {{ $page->featuredImage->responsive('100vw') }} alt="" class="absolute inset-0 -z-20 size-full object-cover" fetchpriority="high">
        @endif
        <div class="hero-overlay absolute inset-0 -z-10"></div>
        <div class="hero-blob top-10 left-10 -z-10 size-72 bg-white/10"></div>
        <div class="hero-blob right-10 bottom-10 -z-10 size-96 bg-accent/25"></div>
        <div class="container-site py-24 text-center text-white sm:py-32 lg:py-40">
            @if (! empty($hero['eyebrow']))
                <p class="eyebrow-light">
                    <span class="size-2 rounded-full bg-accent"></span> {{ $hero['eyebrow'] }}
                </p>
            @endif
            <h1 class="mx-auto mt-7 max-w-4xl text-4xl leading-[1.05] font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl xl:text-7xl">{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="mx-auto mt-6 max-w-2xl text-lg text-white/85 sm:text-xl">{{ $page->excerpt }}</p>
            @endif
            @if (! empty($hero['primary_label']) || ! empty($hero['secondary_label']))
                <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    @if (! empty($hero['primary_label']))
                        <a href="{{ $hero['primary_url'] }}" class="btn btn-accent btn-lg w-full sm:w-auto">{{ $hero['primary_label'] }} <x-icon name="arrow-right" class="size-4" /></a>
                    @endif
                    @if (! empty($hero['secondary_label']))
                        <a href="{{ $hero['secondary_url'] }}" class="btn btn-glass btn-lg w-full sm:w-auto">{{ $hero['secondary_label'] }}</a>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- Content sections: use shortcodes such as [media_text], [link_cards], [cta] and [contact_form] --}}
    <div class="container-site space-y-20 py-16 lg:space-y-28 lg:py-24">
        <div class="landing-content space-y-20 lg:space-y-28">{!! $content !!}</div>

        @if ($page->faqs->isNotEmpty())
            <x-site.faq-list :faqs="$page->faqs" />
        @endif
    </div>
@endsection
