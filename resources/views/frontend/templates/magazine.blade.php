@extends('layouts.app')

@php
    $featured = $children->onFirstPage() ? $children->first() : null;
    $grid = $featured ? $children->getCollection()->slice(1) : $children->getCollection();
@endphp

@section('content')
    <section class="border-b border-line bg-slate-50">
        <div class="container-site py-12 sm:py-16">
            <x-ui.breadcrumbs :items="$page->ancestors()->map(fn ($p) => ['label' => $p->title, 'url' => $p->url()])->push(['label' => $page->title])->all()" :home="url('/')" class="text-slate-500" />
            <div class="mt-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-4xl font-extrabold tracking-tight text-secondary sm:text-5xl">{{ $page->title }}</h1>
                    @if ($page->excerpt)<p class="mt-3 max-w-2xl text-lg text-slate-600">{{ $page->excerpt }}</p>@endif
                </div>
                <form action="{{ route('search') }}" role="search" class="relative w-full md:w-72">
                    <label for="magazine-search" class="sr-only">Search articles</label>
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                    <input id="magazine-search" type="search" name="q" placeholder="Search articles…" class="form-control pl-9">
                </form>
            </div>
        </div>
    </section>

    <div class="container-site py-12 lg:py-16">
        @if ($children->isEmpty())
            <x-ui.empty-state icon="newspaper" title="No articles yet" description="New stories will appear here soon." class="rounded-2xl border border-dashed border-line" />
        @else
            @if ($featured)
                <article class="group relative grid overflow-hidden rounded-3xl bg-secondary text-white shadow-lg lg:grid-cols-5">
                    <div class="relative aspect-[16/10] overflow-hidden lg:col-span-3 lg:aspect-auto lg:min-h-[26rem]">
                        @if ($featured->featuredImage)
                            <img src="{{ $featured->featuredImage->url }}" alt="{{ $featured->featuredImage->alt ?: $featured->title }}" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105" fetchpriority="high">
                        @endif
                    </div>
                    <div class="flex flex-col justify-center p-6 sm:p-10 lg:col-span-2">
                        <p class="text-xs font-semibold tracking-wider text-accent uppercase">Latest story @if ($featured->published_at)· {{ local_date($featured->published_at) }}@endif</p>
                        <h2 class="mt-3 text-2xl font-extrabold leading-tight text-balance sm:text-3xl">
                            <a href="{{ $featured->url() }}" class="after:absolute after:inset-0">{{ $featured->title }}</a>
                        </h2>
                        <p class="mt-4 line-clamp-4 text-white/80">{{ $featured->summary(45) }}</p>
                        <span class="mt-6 inline-flex items-center gap-1 font-semibold text-accent">Read the story <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></span>
                    </div>
                </article>
            @endif

            @if ($grid->isNotEmpty())
                <div class="mt-12 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($grid as $post)
                        <article class="group relative flex flex-col">
                            <div class="aspect-[16/10] overflow-hidden rounded-2xl bg-slate-100">
                                @if ($post->featuredImage)
                                    <img src="{{ $post->featuredImage->url }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
                                @endif
                            </div>
                            @if ($post->published_at)
                                <time datetime="{{ $post->published_at->toDateString() }}" class="mt-4 text-xs font-semibold tracking-wide text-primary uppercase">{{ local_date($post->published_at) }}</time>
                            @endif
                            <h3 class="mt-1.5 text-lg leading-snug font-bold text-secondary group-hover:text-primary">
                                <a href="{{ $post->url() }}" class="after:absolute after:inset-0">{{ $post->title }}</a>
                            </h3>
                            <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $post->summary(22) }}</p>
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
