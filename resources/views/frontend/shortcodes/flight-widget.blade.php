@if ($enabled)
<section @class(["not-prose relative z-10 rounded-3xl border border-line/80 bg-white p-5 shadow-float sm:p-8", "-mt-32 lg:-mt-40" => $overlap])
         data-flight-widget
         data-departures-url="{{ route('widgets.flights', 'departures') }}" data-arrivals-url="{{ route('widgets.flights', 'arrivals') }}"
         data-departures-page="{{ $departuresUrl }}" data-arrivals-page="{{ $arrivalsUrl }}">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="flex size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary-dark text-white shadow-glow-primary"><x-icon name="plane" class="size-6" /></span>
            <div>
                <p class="font-display text-xl font-bold text-secondary sm:text-2xl">{{ $title }}</p>
                <p class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600"><span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span></span> Live status</p>
            </div>
        </div>
        <div data-tabs role="tablist" aria-label="Flight direction" class="inline-flex rounded-2xl bg-soft p-1.5 ring-1 ring-line">
            <button type="button" role="tab" data-value="departures" aria-selected="true" aria-controls="flight-widget-panel" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-muted transition aria-selected:bg-white aria-selected:text-primary aria-selected:shadow-card">
                <span class="inline-flex items-center gap-1.5"><x-icon name="plane-departure" class="size-4" /> Departures</span>
            </button>
            <button type="button" role="tab" data-value="arrivals" aria-selected="false" tabindex="-1" aria-controls="flight-widget-panel" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-muted transition aria-selected:bg-white aria-selected:text-primary aria-selected:shadow-card">
                <span class="inline-flex items-center gap-1.5"><x-icon name="plane-arrival" class="size-4" /> Arrivals</span>
            </button>
        </div>
    </div>
    <form class="mt-6" role="search">
        <label for="flight-widget-search" class="sr-only">Search flights</label>
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-primary" />
            <input id="flight-widget-search" type="search" placeholder="Search departures…" class="form-control bg-soft py-3.5 pl-12 text-base" autocomplete="off">
        </div>
    </form>
    <div id="flight-widget-panel" role="tabpanel">
        <ul data-flight-list class="mt-3 grid grid-cols-[minmax(0,1fr)] gap-2 md:grid-cols-[repeat(2,minmax(0,1fr))]" aria-live="polite"></ul>
    </div>
    <a data-more-link href="{{ $departuresUrl }}" class="btn btn-secondary mt-5 w-full sm:w-auto" @unless ($departuresUrl) hidden @endunless>View all departures →</a>
</section>
@endif
