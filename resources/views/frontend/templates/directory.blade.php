@extends('layouts.app')

@php($groups = $children->groupBy(fn ($child) => ctype_alpha($l = mb_strtoupper(mb_substr($child->title, 0, 1))) ? $l : '#'))

@section('content')
    <x-site.page-hero :page="$page" :subtitle="$page->excerpt ?: $children->count().' listings'" />

    <div class="container-site py-12 lg:py-16" data-directory>
        @if ($children->isEmpty())
            <x-ui.empty-state icon="list" title="Nothing listed yet" class="rounded-2xl border border-dashed border-line" />
        @else
            <div class="sticky top-16 z-20 -mx-4 border-b border-line bg-white/95 px-4 py-4 backdrop-blur sm:mx-0 sm:rounded-2xl sm:border sm:px-5 lg:top-20">
                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                    <div class="relative flex-1">
                        <label for="directory-search" class="sr-only">Filter {{ strtolower($page->title) }}</label>
                        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                        <input id="directory-search" type="search" data-directory-search autocomplete="off" placeholder="Type to filter {{ $children->count() }} listings…" class="form-control pl-9">
                    </div>
                    <nav aria-label="Jump to letter" class="flex flex-wrap gap-1">
                        @foreach ($groups->keys() as $letter)
                            <a href="#letter-{{ $letter === '#' ? 'other' : $letter }}" data-directory-letter="{{ $letter }}" class="flex size-8 items-center justify-center rounded-lg text-sm font-semibold text-slate-600 hover:bg-primary/10 hover:text-primary">{{ $letter }}</a>
                        @endforeach
                    </nav>
                </div>
                <p class="mt-2 text-xs text-slate-500" aria-live="polite"><span data-directory-count>{{ $children->count() }}</span> shown</p>
            </div>

            <div class="mt-8 space-y-10">
                @foreach ($groups as $letter => $items)
                    <section id="letter-{{ $letter === '#' ? 'other' : $letter }}" class="scroll-mt-44" data-directory-group="{{ $letter }}">
                        <h2 class="mb-4 flex items-center gap-3 text-2xl font-extrabold text-secondary">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-primary text-lg text-white">{{ $letter }}</span>
                            <span class="h-px flex-1 bg-line"></span>
                        </h2>
                        <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($items as $child)
                                <li data-directory-item data-search="{{ mb_strtolower($child->title.' '.$child->excerpt) }}">
                                    <a href="{{ $child->url() }}" class="group flex h-full items-center gap-4 rounded-2xl border border-line bg-white p-3 transition hover:border-primary/30 hover:shadow-md">
                                        <span class="size-14 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                            @if ($child->featuredImage)
                                                <img src="{{ $child->featuredImage->url }}" alt="" loading="lazy" class="size-full object-cover transition group-hover:scale-105">
                                            @else
                                                <span class="flex size-full items-center justify-center text-lg font-bold text-primary">{{ mb_substr($child->title, 0, 1) }}</span>
                                            @endif
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="line-clamp-2 font-semibold text-secondary group-hover:text-primary">{{ $child->title }}</span>
                                        </span>
                                        <x-icon name="chevron-right" class="size-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-primary" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>

            <div data-directory-empty hidden>
                <x-ui.empty-state icon="search" title="No matches" description="Try a shorter or different search term." class="mt-8 rounded-2xl border border-dashed border-line" />
            </div>
        @endif

        {{-- The list comes first; the page's own text follows it. --}}
        @if (trim(strip_tags($content, '<img><iframe>')))
            <div class="content-prose mt-16 max-w-4xl border-t border-line pt-12">{!! $content !!}</div>
        @endif

        @if ($page->faqs->isNotEmpty())
            <x-site.faq-list :faqs="$page->faqs" class="mt-16" />
        @endif
    </div>
@endsection
