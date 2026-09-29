@extends('layouts.app')

@section('content')
    <x-site.page-hero :page="$page">
        <p class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-white/80">
            @if ($page->published_at)
                <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" class="size-4" /><time datetime="{{ $page->published_at->toDateString() }}">{{ local_date($page->published_at) }}</time></span>
            @endif
            <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-4" />{{ max(1, (int) ceil(str_word_count(strip_tags($page->content)) / 220)) }} min read</span>
        </p>
    </x-site.page-hero>

    <div class="container-site grid gap-10 py-12 lg:grid-cols-12 lg:py-16">
        <article class="min-w-0 lg:col-span-8">
            @if ($page->featuredImage)
                <img src="{{ $page->featuredImage->url }}" alt="{{ $page->featuredImage->alt ?: $page->title }}" width="{{ $page->featuredImage->width }}" height="{{ $page->featuredImage->height }}" class="mb-8 aspect-video w-full rounded-2xl object-cover shadow-sm">
            @endif
            <div class="content-prose">{!! $content !!}</div>

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
