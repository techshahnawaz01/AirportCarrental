@if ($enabled)
<section @class(["not-prose relative z-10 rounded-3xl border border-line bg-white p-5 shadow-xl sm:p-8", "-mt-28 lg:-mt-36" => $overlap])
         data-flight-widget
         data-departures-url="{{ route('widgets.flights', 'departures') }}" data-arrivals-url="{{ route('widgets.flights', 'arrivals') }}"
         data-departures-page="{{ $departuresUrl }}" data-arrivals-page="{{ $arrivalsUrl }}">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h2 class="flex items-center gap-2 text-xl font-bold text-secondary sm:text-2xl"><x-icon name="plane" class="size-6 text-primary" /> {{ $title }}</h2>
        <div data-tabs role="tablist" aria-label="Flight direction" class="inline-flex rounded-xl bg-slate-100 p-1">
            <button type="button" role="tab" data-value="departures" aria-selected="true" aria-controls="flight-widget-panel" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 aria-selected:bg-white aria-selected:text-primary aria-selected:shadow-sm">
                <span class="inline-flex items-center gap-1.5"><x-icon name="plane-departure" class="size-4" /> Departures</span>
            </button>
            <button type="button" role="tab" data-value="arrivals" aria-selected="false" tabindex="-1" aria-controls="flight-widget-panel" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 aria-selected:bg-white aria-selected:text-primary aria-selected:shadow-sm">
                <span class="inline-flex items-center gap-1.5"><x-icon name="plane-arrival" class="size-4" /> Arrivals</span>
            </button>
        </div>
    </div>
    <form class="mt-5" role="search">
        <label for="flight-widget-search" class="sr-only">Search flights</label>
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-slate-400" />
            <input id="flight-widget-search" type="search" placeholder="Search departures…" class="form-control py-3 pl-10" autocomplete="off">
        </div>
    </form>
    <div id="flight-widget-panel" role="tabpanel">
        <ul data-flight-list class="mt-2 divide-y divide-line" aria-live="polite"></ul>
    </div>
    <a data-more-link href="{{ $departuresUrl }}" class="mt-4 inline-flex text-sm font-semibold text-primary hover:underline" @unless ($departuresUrl) hidden @endunless>View all departures →</a>
</section>
@endif
