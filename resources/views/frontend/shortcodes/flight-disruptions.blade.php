<section class="not-prose my-10" data-disruptions data-url="{{ route('widgets.disruptions') }}" data-phone="{{ $phone }}">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div data-tabs role="tablist" aria-label="Direction" class="inline-flex rounded-2xl bg-soft p-1.5 ring-1 ring-line">
            <button type="button" role="tab" data-value="arrivals" aria-selected="true" aria-controls="disruptions-panel" class="inline-flex items-center gap-1.5 rounded-xl px-5 py-2.5 text-sm font-semibold text-muted transition aria-selected:bg-white aria-selected:text-primary aria-selected:shadow-card"><x-icon name="plane-arrival" class="size-4" /> Arrivals</button>
            <button type="button" role="tab" data-value="departures" aria-selected="false" tabindex="-1" aria-controls="disruptions-panel" class="inline-flex items-center gap-1.5 rounded-xl px-5 py-2.5 text-sm font-semibold text-muted transition aria-selected:bg-white aria-selected:text-primary aria-selected:shadow-card"><x-icon name="plane-departure" class="size-4" /> Departures</button>
        </div>
        <p class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700"><span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span></span> Updated throughout the day</p>
    </div>

    <div id="disruptions-panel" role="tabpanel" class="mt-6 grid grid-cols-[minmax(0,1fr)] gap-8">
        @foreach ([
            'delayed' => ['label' => 'Delayed flights', 'hint' => 'More than 10 minutes behind schedule', 'icon' => 'clock', 'head' => 'from-amber-400 to-orange-500', 'count' => 'bg-white text-amber-700'],
            'cancelled' => ['label' => 'Cancelled flights', 'hint' => 'Contact your airline to rebook or claim a refund', 'icon' => 'x-circle', 'head' => 'from-red-500 to-rose-600', 'count' => 'bg-white text-red-700'],
        ] as $status => $meta)
            <div class="overflow-hidden rounded-3xl border border-line bg-white shadow-card">
                <div class="flex items-center justify-between gap-3 bg-gradient-to-r px-4 py-4 text-white sm:px-6 {{ $meta['head'] }}">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-white/20 ring-1 ring-white/30"><x-icon :name="$meta['icon']" class="size-6" /></span>
                        <div>
                            <h3 class="text-lg font-bold">{{ $meta['label'] }}</h3>
                            <p class="text-xs text-white/85">{{ $meta['hint'] }}</p>
                        </div>
                    </div>
                    <span data-count="{{ $status }}" class="shrink-0 rounded-full px-3 py-1 font-display text-lg font-bold shadow-sm {{ $meta['count'] }}">…</span>
                </div>

                <div class="hidden md:block">
                    <table class="w-full text-left text-sm" data-status="{{ $status }}">
                        <thead class="bg-soft text-xs font-semibold tracking-wider text-muted uppercase">
                            <tr><th class="px-5 py-3">Flight</th><th class="px-5 py-3">Route</th><th class="px-5 py-3">{{ $status === 'delayed' ? 'Delay' : 'Scheduled' }}</th>@if ($phone)<th class="px-5 py-3 text-right">Help</th>@endif</tr>
                        </thead>
                        <tbody class="divide-y divide-line"><tr data-loading><td colspan="{{ $phone ? 4 : 3 }}" class="px-5 py-4"><div class="skeleton h-10"></div></td></tr></tbody>
                    </table>
                </div>
                <ul class="grid grid-cols-[minmax(0,1fr)] gap-3 p-3 md:hidden" data-cards-for="{{ $status }}"><li data-loading class="skeleton h-28 rounded-2xl"></li></ul>
            </div>
        @endforeach
    </div>
</section>
