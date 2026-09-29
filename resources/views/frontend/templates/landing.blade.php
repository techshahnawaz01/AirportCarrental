@extends('layouts.app')

@php($hero = $page->meta('hero', []))

@section('content')
    <section class="relative isolate overflow-hidden bg-secondary">
        @if ($page->featuredImage)
            <img src="{{ $page->featuredImage->url }}" alt="" class="absolute inset-0 -z-10 size-full object-cover opacity-40" fetchpriority="high">
        @endif
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,color-mix(in_oklab,var(--color-primary)_55%,transparent),transparent_70%)]"></div>
        <div class="container-site py-24 text-center text-white sm:py-32 lg:py-40">
            @if (! empty($hero['eyebrow']))
                <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold tracking-wide uppercase ring-1 ring-white/20">
                    <span class="size-1.5 rounded-full bg-accent"></span> {{ $hero['eyebrow'] }}
                </p>
            @endif
            <h1 class="mx-auto mt-6 max-w-4xl text-4xl font-extrabold tracking-tight text-balance sm:text-5xl lg:text-6xl">{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="mx-auto mt-6 max-w-2xl text-lg text-white/85 sm:text-xl">{{ $page->excerpt }}</p>
            @endif
            @if (! empty($hero['primary_label']) || ! empty($hero['secondary_label']))
                <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    @if (! empty($hero['primary_label']))
                        <a href="{{ $hero['primary_url'] }}" class="btn btn-accent btn-lg w-full sm:w-auto">{{ $hero['primary_label'] }} <x-icon name="arrow-right" class="size-4" /></a>
                    @endif
                    @if (! empty($hero['secondary_label']))
                        <a href="{{ $hero['secondary_url'] }}" class="btn btn-lg w-full bg-white/10 text-white ring-1 ring-white/30 hover:bg-white/20 sm:w-auto">{{ $hero['secondary_label'] }}</a>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- Content sections: use shortcodes such as [media_text], [link_cards], [cta] and [contact_form] --}}
    <div class="container-site space-y-16 py-16 lg:space-y-24 lg:py-24">
        <div class="landing-content space-y-16 lg:space-y-24">{!! $content !!}</div>

        @if ($page->faqs->isNotEmpty())
            <x-site.faq-list :faqs="$page->faqs" />
        @endif
    </div>
@endsection
