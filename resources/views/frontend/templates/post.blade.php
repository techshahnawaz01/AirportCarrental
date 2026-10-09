@extends('layouts.app')

@section('content')
    {{-- A published post means its author has a public page (/author/{slug}). --}}
    @php($author = $page->author)
    @php($authorLinked = $author && $page->isPublished())
    <x-site.page-hero :page="$page">
        <p class="mt-7 flex flex-wrap items-center gap-2.5 text-sm text-white/90">
            @if ($author)
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 py-1 pr-3.5 pl-1 ring-1 ring-white/25">
                    <x-site.author-avatar :author="$author" size="size-7" text="text-[11px]" class="ring-white/40" />
                    <span>By @if ($authorLinked)<a href="{{ $author->url() }}" class="font-semibold text-white hover:text-accent" rel="author">{{ $author->name }}</a>@else<span class="font-semibold text-white">{{ $author->name }}</span>@endif</span>
                </span>
            @endif
            @if ($page->published_at)
                <span class="eyebrow-light"><x-icon name="calendar" class="size-4 text-accent" /><time datetime="{{ $page->published_at->toDateString() }}">{{ local_date($page->published_at) }}</time></span>
            @endif
            <span class="eyebrow-light"><x-icon name="clock" class="size-4 text-accent" />{{ max(1, (int) ceil(str_word_count(strip_tags($page->content)) / 220)) }} min read</span>
        </p>
    </x-site.page-hero>

    <div class="container-site grid gap-10 py-12 lg:grid-cols-12 lg:gap-12 lg:py-20">
        <article class="min-w-0 lg:col-span-8">
            @if ($page->featuredImage)
                <img src="{{ $page->featuredImage->url }}" {{ $page->featuredImage->responsive('(min-width: 1024px) 760px, 100vw') }} alt="{{ $page->featuredImage->alt ?: $page->title }}" width="{{ $page->featuredImage->width }}" height="{{ $page->featuredImage->height }}" class="mb-10 aspect-video w-full rounded-3xl object-cover shadow-float">
            @endif
            <div class="content-prose">{!! $content !!}</div>

            @if ($authorLinked)
                <x-site.author-box :author="$author" class="mt-14" />
            @endif

            @if ($page->faqs->isNotEmpty())
                <x-site.faq-list :faqs="$page->faqs" class="mt-14" />
            @endif

            @isset($comments)
                <x-site.comments :page="$page" :comments="$comments" />
            @endisset
        </article>

        <aside class="lg:col-span-4">
            @include('frontend.partials.sidebar')
        </aside>
    </div>
@endsection
