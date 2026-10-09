@extends('layouts.app')

@php($groups = $children->groupBy(fn ($child) => ctype_alpha($l = mb_strtoupper(mb_substr($child->title, 0, 1))) ? $l : '#'))

@section('content')
    <x-site.page-hero :page="$page" :subtitle="$page->excerpt ?: $children->count().' listings'" />

    <div class="container-site py-12 lg:py-20" data-directory>
        @if ($children->isEmpty())
            <x-ui.empty-state icon="list" title="Nothing listed yet" class="rounded-3xl border-2 border-dashed border-line bg-soft" />
        @else
            <div class="sticky top-[72px] z-20 -mx-4 border-b border-line bg-white px-4 py-4 shadow-card sm:mx-0 sm:rounded-3xl sm:border sm:px-6 sm:py-5 lg:top-24">
                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                    <div class="relative flex-1">
                        <label for="directory-search" class="sr-only">Filter {{ strtolower($page->title) }}</label>
                        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-primary" />
                        <input id="directory-search" type="search" data-directory-search autocomplete="off" placeholder="Type to filter {{ $children->count() }} listings…" class="form-control bg-soft py-3 pl-10">
                    </div>
                    <nav aria-label="Jump to letter" class="flex flex-wrap gap-1">
                        @foreach ($groups->keys() as $letter)
                            <a href="#letter-{{ $letter === '#' ? 'other' : $letter }}" data-directory-letter="{{ $letter }}" class="flex size-8 items-center justify-center rounded-lg text-sm font-semibold text-slate-600 transition hover:bg-primary hover:text-white">{{ $letter }}</a>
                        @endforeach
                    </nav>
                </div>
                <p class="mt-2 text-xs text-slate-500" aria-live="polite"><span data-directory-count>{{ $children->count() }}</span> shown</p>
            </div>

            <div class="mt-8 space-y-10">
                @foreach ($groups as $letter => $items)
                    <section id="letter-{{ $letter === '#' ? 'other' : $letter }}" class="scroll-mt-44" data-directory-group="{{ $letter }}">
                        <h2 class="mb-4 flex items-center gap-3 text-2xl font-bold text-secondary">
                            <span class="flex size-11 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary-dark text-lg text-white shadow-glow-primary">{{ $letter }}</span>
                            <span class="h-px flex-1 bg-line"></span>
                        </h2>
                        <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($items as $child)
                                <li data-directory-item data-search="{{ mb_strtolower($child->title.' '.$child->excerpt) }}">
                                    <a href="{{ $child->url() }}" class="card-premium card-hover group flex h-full items-center gap-4 rounded-2xl p-3">
                                        <span class="size-14 shrink-0 overflow-hidden rounded-xl bg-primary-soft">
                                            @if ($child->featuredImage)
                                                <img src="{{ $child->featuredImage->url }}" alt="" loading="lazy" class="size-full object-cover transition group-hover:scale-105">
                                            @else
                                                <span class="flex size-full items-center justify-center text-lg font-bold text-primary">{{ mb_substr($child->title, 0, 1) }}</span>
                                            @endif
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="line-clamp-2 font-semibold text-secondary group-hover:text-primary">{{ $child->title }}</span>
                                        </span>
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-soft text-slate-400 transition group-hover:bg-accent group-hover:text-on-accent"><x-icon name="chevron-right" class="size-4" /></span>
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
