@extends('layouts.app')

@section('content')
    <section class="relative isolate overflow-hidden bg-secondary">
        @if ($page->featuredImage)
            <img src="{{ $page->featuredImage->url }}" alt="{{ $page->featuredImage->alt }}" class="absolute inset-0 -z-10 size-full object-cover" fetchpriority="high">
        @endif
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-secondary/95 via-secondary/75 to-primary/40"></div>
        <div class="container-site py-20 text-white sm:py-28 lg:py-32">
            @if ($tagline = settings('branding.tagline'))
                <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide uppercase ring-1 ring-white/20">
                    <x-icon name="plane" class="size-4 text-accent" /> {{ $tagline }}
                </p>
            @endif
            <h1 class="mt-5 max-w-3xl text-4xl font-extrabold tracking-tight text-balance sm:text-5xl lg:text-6xl">{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="mt-5 max-w-2xl text-lg text-white/85">{{ $page->excerpt }}</p>
            @endif
            <form action="{{ route('search') }}" role="search" data-live-search class="relative mt-8 max-w-xl">
                <label for="hero-search" class="sr-only">Search</label>
                <div class="flex overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-black/5">
                    <x-icon name="search" class="pointer-events-none my-auto ml-4 size-5 shrink-0 text-slate-400" />
                    <input id="hero-search" type="search" name="q" autocomplete="off" placeholder="Search parking, terminals, airlines…" aria-expanded="false" aria-controls="hero-search-results"
                           class="min-w-0 flex-1 border-0 bg-transparent px-3 py-4 text-slate-900 placeholder:text-slate-400 focus:ring-0">
                    <button class="btn btn-primary m-1.5">Search</button>
                </div>
                <div id="hero-search-results" data-live-results role="listbox" hidden class="absolute inset-x-0 top-full z-20 mt-2 max-h-96 overflow-y-auto rounded-xl border border-line bg-white p-1 text-left shadow-2xl"></div>
            </form>
        </div>
    </section>

    <div class="container-site space-y-16 py-14 lg:space-y-24 lg:py-20">{!! $content !!}</div>

    @if ($page->faqs->isNotEmpty())
        <div class="container-site py-16">
            <x-site.faq-list :faqs="$page->faqs" subtitle="Quick answers to the questions travellers ask most." />
        </div>
    @endif
@endsection
