<section class="not-prose my-8" data-disruptions data-url="{{ route('widgets.disruptions') }}" data-phone="{{ $phone }}">
    <div data-tabs role="tablist" aria-label="Direction" class="inline-flex rounded-xl bg-slate-100 p-1">
        <button type="button" role="tab" data-value="arrivals" aria-selected="true" aria-controls="disruptions-panel" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 aria-selected:bg-white aria-selected:text-primary aria-selected:shadow-sm">Arrivals</button>
        <button type="button" role="tab" data-value="departures" aria-selected="false" tabindex="-1" aria-controls="disruptions-panel" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 aria-selected:bg-white aria-selected:text-primary aria-selected:shadow-sm">Departures</button>
    </div>
    <div id="disruptions-panel" role="tabpanel" class="mt-6 grid grid-cols-[minmax(0,1fr)] gap-8">
        @foreach (['delayed' => 'Delayed flights', 'cancelled' => 'Cancelled flights'] as $status => $label)
            <div>
                <h3 class="flex items-center gap-2 text-lg font-bold text-secondary">
                    {{ $label }} <span data-count="{{ $status }}" class="badge {{ $status === 'cancelled' ? 'badge-danger' : 'badge-warning' }}">…</span>
                </h3>
                <div class="mt-3 overflow-x-auto rounded-2xl border border-line bg-white">
                    <table class="table min-w-[560px]" data-status="{{ $status }}">
                        <thead><tr><th>Flight</th><th>Route</th><th>Airline</th><th>{{ $status === 'delayed' ? 'Delay' : 'Scheduled' }}</th><th>Help</th></tr></thead>
                        <tbody><tr data-loading><td colspan="5"><div class="skeleton h-5"></div></td></tr></tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</section>
