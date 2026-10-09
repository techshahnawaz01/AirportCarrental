@extends('layouts.app')

@php
    $featured = $children->onFirstPage() ? $children->first() : null;
    $grid = $featured ? $children->getCollection()->slice(1) : $children->getCollection();
@endphp

@section('content')
    <section class="relative isolate overflow-hidden border-b border-line bg-gradient-to-br from-primary-soft via-white to-accent-soft">
        <div class="hero-blob -top-24 right-0 -z-10 size-96 bg-primary/10"></div>
        <div class="container-site py-14 sm:py-20">
            <x-ui.breadcrumbs :items="$page->breadcrumbs()" :home="url('/')" class="inline-flex rounded-full bg-white px-4 py-1.5 text-slate-500 shadow-sm ring-1 ring-line" />
            <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-4xl font-bold tracking-tight text-secondary sm:text-5xl lg:text-6xl">{{ $page->title }}</h1>
                    @if ($page->excerpt)<p class="mt-4 max-w-2xl text-lg text-muted">{{ $page->excerpt }}</p>@endif
                </div>
                <form action="{{ route('search') }}" role="search" class="relative w-full md:w-80">
                    <label for="magazine-search" class="sr-only">Search articles</label>
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                    <input id="magazine-search" type="search" name="q" placeholder="Search articles…" class="form-control py-3 pl-10 shadow-card">
                </form>
            </div>
        </div>
    </section>

    <div class="container-site py-14 lg:py-20">
        @if ($children->isEmpty())
            <x-ui.empty-state icon="newspaper" title="No articles yet" description="New stories will appear here soon." class="rounded-3xl border-2 border-dashed border-line bg-soft" />
        @else
            @if ($featured)
                <article class="group relative grid overflow-hidden rounded-[2rem] bg-secondary text-white shadow-float lg:grid-cols-5">
                    <div class="relative aspect-[16/10] overflow-hidden lg:col-span-3 lg:aspect-auto lg:min-h-[26rem]">
                        @if ($featured->featuredImage)
                            <img src="{{ $featured->featuredImage->url }}" {{ $featured->featuredImage->responsive('(min-width: 1024px) 60vw, 100vw') }} alt="{{ $featured->featuredImage->alt ?: $featured->title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105" fetchpriority="high">
                        @endif
                    </div>
                    <div class="flex flex-col justify-center p-6 sm:p-10 lg:col-span-2">
                        <p class="inline-flex self-start rounded-full bg-accent px-3 py-1 text-xs font-semibold tracking-wider text-on-accent uppercase">Latest story @if ($featured->published_at)· {{ local_date($featured->published_at) }}@endif</p>
                        <h2 class="mt-5 text-2xl leading-tight font-bold text-balance sm:text-3xl lg:text-4xl">
                            <a href="{{ $featured->url() }}" class="after:absolute after:inset-0">{{ $featured->title }}</a>
                        </h2>
                        <p class="mt-4 line-clamp-4 text-white/80">{{ $featured->summary(45) }}</p>
                        @if ($featured->author)
                            <a href="{{ $featured->author->url() }}" class="relative z-10 mt-6 inline-flex items-center gap-2.5 self-start text-sm font-semibold text-white hover:text-accent" rel="author">
                                <x-site.author-avatar :author="$featured->author" size="size-9" text="text-xs" class="ring-white/30" />
                                {{ $featured->author->name }}
                            </a>
                        @endif
                        <span class="mt-6 inline-flex items-center gap-1 font-semibold text-accent">Read the story <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></span>
                    </div>
                </article>
            @endif

            @if ($grid->isNotEmpty())
                <div class="mt-12 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($grid as $post)
                        <article class="group relative flex flex-col">
                            <div class="aspect-[16/10] overflow-hidden rounded-3xl bg-soft shadow-card transition duration-300 group-hover:shadow-card-hover">
                                @if ($post->featuredImage)
                                    <img src="{{ $post->featuredImage->url }}" {{ $post->featuredImage->responsive('(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw') }} alt="" loading="lazy" class="size-full object-cover transition duration-700 group-hover:scale-110">
                                @endif
                            </div>
                            @if ($post->published_at)
                                <time datetime="{{ $post->published_at->toDateString() }}" class="mt-5 text-xs font-bold tracking-wider text-accent-ink uppercase">{{ local_date($post->published_at) }}</time>
                            @endif
                            <h3 class="mt-1.5 text-lg leading-snug font-bold text-secondary group-hover:text-primary">
                                <a href="{{ $post->url() }}" class="after:absolute after:inset-0">{{ $post->title }}</a>
                            </h3>
                            <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $post->summary(22) }}</p>
                            @if ($post->author)
                                <a href="{{ $post->author->url() }}" class="relative z-10 mt-4 inline-flex items-center gap-2 self-start text-sm font-semibold text-secondary hover:text-primary" rel="author">
                                    <x-site.author-avatar :author="$post->author" size="size-7" text="text-[10px]" />
                                    {{ $post->author->name }}
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="mt-12">{{ $children->links() }}</div>
        @endif

        @if (trim(strip_tags($content, '<img><iframe>')))
            <div class="content-prose mx-auto mt-16 max-w-4xl border-t border-line pt-12">{!! $content !!}</div>
        @endif

        @if ($page->faqs->isNotEmpty())
            <x-site.faq-list :faqs="$page->faqs" class="mt-16" />
        @endif
    </div>
@endsection
