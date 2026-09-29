<section class="not-prose my-8" data-flight-board data-url="{{ route('widgets.flights', $direction) }}">
    <div class="grid gap-3 rounded-2xl border border-line bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label for="board-search-{{ $direction }}" class="form-label">Search</label>
            <input id="board-search-{{ $direction }}" type="search" data-search placeholder="Flight, airline or city" class="form-control">
        </div>
        <div>
            <label for="board-airline-{{ $direction }}" class="form-label">Airline</label>
            <select id="board-airline-{{ $direction }}" data-filter="airline" class="form-control"><option value="">All airlines</option></select>
        </div>
        <div>
            <label for="board-status-{{ $direction }}" class="form-label">Status</label>
            <select id="board-status-{{ $direction }}" data-filter="status" class="form-control capitalize"><option value="">All statuses</option></select>
        </div>
        <div>
            <label for="board-terminal-{{ $direction }}" class="form-label">Terminal</label>
            <select id="board-terminal-{{ $direction }}" data-filter="terminal" class="form-control"><option value="">All terminals</option></select>
        </div>
    </div>
    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm">
        <p><span data-summary class="font-semibold text-secondary">Loading…</span> <span data-updated class="text-slate-500"></span></p>
        <button type="button" data-reset class="btn btn-ghost btn-sm"><x-icon name="refresh" class="size-4" /> Reset filters</button>
    </div>
    <div class="mt-3 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="table min-w-[760px]">
            <caption class="sr-only">{{ ucfirst($direction) }} @if ($airport) at {{ $airport }} @endif</caption>
            <thead>
                <tr>
                    <th scope="col">{{ $direction === 'arrivals' ? 'Scheduled arrival' : 'Scheduled departure' }}</th>
                    <th scope="col">Flight</th>
                    <th scope="col">Airline</th>
                    <th scope="col">{{ $direction === 'arrivals' ? 'From' : 'To' }}</th>
                    <th scope="col">Status</th>
                    <th scope="col">Delay</th>
                    <th scope="col">Terminal</th>
                    <th scope="col">Gate</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < 5; $i++)
                    <tr><td colspan="8"><div class="skeleton h-5"></div></td></tr>
                @endfor
            </tbody>
        </table>
    </div>
    <div data-pager class="mt-4 flex items-center justify-center gap-3"></div>
    <p class="mt-3 text-xs text-slate-500">Times shown in local airport time. Always confirm with your airline before travelling.</p>
</section>
