@extends('layouts.app')

@section('content')
    <x-site.page-hero :page="$page" :subtitle="$page->meta('address')" />
    @php($rooms = $page->meta('rooms', []))

    <nav aria-label="Hotel sections" class="sticky top-16 z-30 border-b border-line bg-white/95 backdrop-blur lg:top-20">
        <ul class="container-site flex gap-1 overflow-x-auto py-2 text-sm font-medium whitespace-nowrap">
            @if ($gallery->isNotEmpty())<li><a href="#gallery" class="block rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100 hover:text-primary">Gallery</a></li>@endif
            @if ($page->meta('location_description') || $page->meta('map_embed_url'))<li><a href="#location" class="block rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100 hover:text-primary">Location</a></li>@endif
            @if ($rooms)<li><a href="#rooms" class="block rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100 hover:text-primary">Rooms &amp; suites</a></li>@endif
            <li><a href="#details" class="block rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100 hover:text-primary">Details</a></li>
            @if ($page->faqs->isNotEmpty())<li><a href="#faqs" class="block rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100 hover:text-primary">FAQs</a></li>@endif
        </ul>
    </nav>

    @if ($gallery->isNotEmpty())
        <section id="gallery" class="scroll-mt-32 bg-slate-50 py-12 lg:py-16">
            <div class="container-site">
                <h2 class="text-2xl font-bold text-secondary sm:text-3xl">Gallery</h2>
                <div data-gallery class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
                    @foreach ($gallery as $image)
                        <a href="{{ $image->url }}" @class(['group overflow-hidden rounded-xl bg-slate-200', 'col-span-2 row-span-2' => $loop->first])>
                            <img src="{{ $image->url }}" alt="{{ $image->alt ?: $page->title }}" loading="lazy" class="aspect-square size-full object-cover transition duration-500 group-hover:scale-105">
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($page->meta('location_description') || $page->meta('map_embed_url'))
        <section id="location" class="container-site grid scroll-mt-32 gap-10 py-12 lg:grid-cols-2 lg:py-16">
            <div>
                <h2 class="text-2xl font-bold text-secondary sm:text-3xl">{{ $page->meta('location_title', 'Location') }}</h2>
                <div class="content-prose mt-4">{!! $page->meta('location_description') !!}</div>
            </div>
            @if ($map = $page->meta('map_embed_url'))
                <iframe src="{{ $map }}" title="Map of {{ $page->title }}" class="min-h-80 w-full rounded-2xl border border-line" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            @endif
        </section>
    @endif

    @if ($rooms)
        <section id="rooms" class="scroll-mt-32 bg-slate-50 py-12 lg:py-16">
            <div class="container-site">
                <h2 class="text-2xl font-bold text-secondary sm:text-3xl">Rooms &amp; suites</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($rooms as $room)
                        <article class="flex flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-sm">
                            @if (! empty($room['image_id']) && ($image = $roomImages->get($room['image_id'])))
                                <img src="{{ $image->url }}" alt="{{ $room['title'] }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                            @endif
                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="text-lg font-semibold text-secondary">{{ $room['title'] }}</h3>
                                @if (! empty($room['description']))<p class="mt-2 text-sm text-slate-600">{{ $room['description'] }}</p>@endif
                                @if (! empty($room['amenities']))
                                    <ul class="mt-4 grid gap-1.5 text-sm text-slate-700">
                                        @foreach (array_slice($room['amenities'], 0, 5) as $amenity)
                                            <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" />{{ $amenity }}</li>
                                        @endforeach
                                    </ul>
                                    @if (count($room['amenities']) > 5)
                                        <details class="group mt-1.5">
                                            <summary class="cursor-pointer text-sm font-semibold text-primary group-open:hidden">Show {{ count($room['amenities']) - 5 }} more</summary>
                                            <ul class="grid gap-1.5 text-sm text-slate-700">
                                                @foreach (array_slice($room['amenities'], 5) as $amenity)
                                                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-emerald-600" />{{ $amenity }}</li>
                                                @endforeach
                                            </ul>
                                        </details>
                                    @endif
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
                @if ($notice = $page->meta('notice'))
                    <p class="mt-8 rounded-xl bg-amber-50 p-4 text-center text-sm font-medium text-amber-900">{{ $notice }}</p>
                @endif
            </div>
        </section>
    @endif

    <section id="details" class="container-site scroll-mt-32 py-12 lg:py-16">
        <div class="content-prose mx-auto max-w-4xl">{!! $content !!}</div>
    </section>

    @if ($page->faqs->isNotEmpty())
        <div id="faqs" class="container-site scroll-mt-32 pb-8"><x-site.faq-list :faqs="$page->faqs" /></div>
    @endif
@endsection
