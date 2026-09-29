@extends('layouts.app')

@section('content')
    <x-site.page-hero title="Sitemap" :subtitle="'All '.$total.' pages on '.settings('branding.site_name').', grouped by section.'" :crumbs="[['label' => 'Sitemap']]" />

    {{-- Filtering reuses the Directory template behaviour (resources/js/frontend/directory.js). --}}
    <div class="container-site py-12 lg:py-16" data-directory>
        <div class="sticky top-16 z-20 -mx-4 border-b border-line bg-white/95 px-4 py-4 backdrop-blur sm:mx-0 sm:rounded-2xl sm:border sm:px-5 lg:top-20">
            <div class="flex flex-col gap-3">
                <div class="relative">
                    <label for="sitemap-search" class="sr-only">Filter pages</label>
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                    <input id="sitemap-search" type="search" data-directory-search autocomplete="off" placeholder="Find a page…" class="form-control pl-9">
                </div>
                <nav aria-label="Jump to section" class="-mx-1 flex gap-1.5 overflow-x-auto px-1 pb-1 md:flex-wrap md:overflow-visible md:pb-0">
                    @foreach ($sections as $section)
                        <a href="#section-{{ $section['page']->id }}" data-directory-letter="{{ $section['page']->id }}" class="shrink-0 rounded-full border border-line px-3 py-1 text-xs font-semibold whitespace-nowrap text-slate-600 hover:border-primary/40 hover:text-primary">{{ \Illuminate\Support\Str::limit($section['page']->title, 28) }}</a>
                    @endforeach
                    @if ($general->isNotEmpty())
                        <a href="#section-general" data-directory-letter="general" class="shrink-0 rounded-full border border-line px-3 py-1 text-xs font-semibold whitespace-nowrap text-slate-600 hover:border-primary/40 hover:text-primary">General</a>
                    @endif
                </nav>
            </div>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                <p aria-live="polite"><span data-directory-count>{{ $total }}</span> pages shown</p>
                <a href="{{ route('sitemap') }}" class="inline-flex items-center gap-1 font-semibold text-primary hover:underline"><x-icon name="document" class="size-3.5" /> XML sitemap for search engines</a>
            </div>
        </div>

        <div class="mt-10 columns-1 gap-6 md:columns-2 xl:columns-3 [&>*]:mb-6 [&>*]:break-inside-avoid">
            <section class="rounded-2xl border border-line bg-white p-5" data-directory-group="home">
                <a href="{{ url('/') }}" class="flex items-center gap-3" data-directory-item data-search="home {{ mb_strtolower(settings('branding.site_name')) }}">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-primary text-white"><x-icon name="home" class="size-5" /></span>
                    <span>
                        <span class="block font-bold text-secondary">Home</span>
                        <span class="text-xs text-slate-500">{{ url('/') }}</span>
                    </span>
                </a>
            </section>

            @foreach ($sections as $section)
                <section id="section-{{ $section['page']->id }}" class="scroll-mt-48 rounded-2xl border border-line bg-white p-5" data-directory-group="{{ $section['page']->id }}" data-group-search="{{ mb_strtolower($section['page']->title.' '.$section['page']->path) }}">
                    <h2 class="flex items-start justify-between gap-3">
                        <a href="{{ $section['page']->url() }}" class="text-lg leading-snug font-bold text-secondary hover:text-primary">{{ $section['page']->title }}</a>
                        <span class="badge badge-primary mt-1 shrink-0">{{ $section['children']->count() }}</span>
                    </h2>
                    <ul class="mt-3 space-y-0.5 border-t border-line pt-3">
                        @foreach ($section['children'] as $row)
                            <li data-directory-item data-search="{{ mb_strtolower($row['page']->title.' '.$row['page']->path) }}">
                                <a href="{{ $row['page']->url() }}" @class([
                                    'group flex items-start gap-2 rounded-lg py-1.5 pr-2 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-primary',
                                    'pl-2' => $row['depth'] === 0,
                                    'pl-6 text-[13px]' => $row['depth'] > 0,
                                ])>
                                    <x-icon :name="$row['depth'] ? 'chevron-right' : 'arrow-right'" class="mt-0.5 size-3.5 shrink-0 text-slate-300 transition group-hover:text-primary" />
                                    <span>{{ $row['page']->title }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach

            @if ($general->isNotEmpty())
                <section id="section-general" class="scroll-mt-48 rounded-2xl border border-line bg-white p-5" data-directory-group="general" data-group-search="general">
                    <h2 class="flex items-start justify-between gap-3 text-lg font-bold text-secondary">
                        General pages <span class="badge badge-primary mt-1">{{ $general->count() }}</span>
                    </h2>
                    <ul class="mt-3 space-y-0.5 border-t border-line pt-3">
                        @foreach ($general as $page)
                            <li data-directory-item data-search="{{ mb_strtolower($page->title.' '.$page->path) }}">
                                <a href="{{ $page->url() }}" class="group flex items-start gap-2 rounded-lg py-1.5 pr-2 pl-2 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-primary">
                                    <x-icon name="arrow-right" class="mt-0.5 size-3.5 shrink-0 text-slate-300 transition group-hover:text-primary" />
                                    <span>{{ $page->title }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <div data-directory-empty hidden>
            <x-ui.empty-state icon="search" title="No pages match" description="Try a different word, or use the site search." class="rounded-2xl border border-dashed border-line">
                <a href="{{ route('search') }}" class="btn btn-primary">Open search</a>
            </x-ui.empty-state>
        </div>
    </div>
@endsection
