@extends('layouts.app')

@section('content')
    <section class="relative isolate overflow-hidden bg-primary-dark">
        <div class="hero-overlay absolute inset-0 -z-10"></div>
        <div class="hero-blob -top-24 -left-16 -z-10 size-80 bg-white/10"></div>
        <div class="hero-blob -right-20 -bottom-32 -z-10 size-96 bg-accent/25"></div>
        <div class="container-site relative py-14 text-white sm:py-20">
            <x-ui.breadcrumbs :items="[['label' => $author->name]]" :home="url('/')" class="inline-flex rounded-full bg-white/10 px-4 py-1.5 text-white/90 ring-1 ring-white/20" />

            <div class="mt-8 flex flex-col gap-6 sm:flex-row sm:items-center sm:gap-8">
                @if ($author->avatar)
                    @push('preload')<link rel="preload" as="image" href="{{ $author->avatar->url }}" fetchpriority="high">@endpush
                @endif
                <x-site.author-avatar :author="$author" size="size-28 sm:size-36" text="text-4xl" class="ring-4 ring-white/30 shadow-float" loading="eager" />
                <div class="min-w-0">
                    <p class="eyebrow-light"><x-icon name="user" class="size-4 text-accent" /> Author</p>
                    <h1 class="mt-4 text-3xl leading-tight font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl">{{ $author->name }}</h1>
                    @if ($author->job_title)<p class="mt-2 text-lg font-medium text-white/85">{{ $author->job_title }}</p>@endif
                    <p class="mt-4 flex flex-wrap items-center gap-2.5 text-sm text-white/90">
                        <span class="eyebrow-light"><x-icon name="newspaper" class="size-4 text-accent" /> {{ $posts->total() }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }}</span>
                    </p>
                </div>
            </div>

            @if ($author->bio)
                <p class="mt-8 max-w-3xl text-base leading-7 text-white/85 sm:text-lg">{{ $author->bio }}</p>
            @endif
            <x-site.author-social :author="$author" :light="true" class="mt-6" />
        </div>
        <svg class="absolute inset-x-0 -bottom-px h-6 w-full text-page sm:h-10" viewBox="0 0 1440 40" preserveAspectRatio="none" aria-hidden="true"><path fill="currentColor" d="M0 40h1440V0c-240 26-480 40-720 40S240 26 0 0Z"/></svg>
    </section>

    <div class="container-site py-14 lg:py-20">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <span class="eyebrow"><x-icon name="newspaper" class="size-3.5" /> Articles</span>
                <h2 class="section-title mt-4">Posts by {{ $author->name }}</h2>
            </div>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
            @foreach ($posts as $post)
                <x-site.page-card :page="$post" :show-date="true" :show-author="false" />
            @endforeach
        </div>
        <div class="mt-12">{{ $posts->links() }}</div>
    </div>
@endsection
