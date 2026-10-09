@php($arriving = $direction === 'arrivals')
<section class="not-prose my-10" data-flight-board data-direction="{{ $direction }}" data-url="{{ route('widgets.flights', $direction) }}">
    {{-- Board header --}}
    <div class="relative isolate overflow-hidden rounded-t-3xl bg-secondary px-5 pt-6 pb-20 text-white sm:px-8 sm:pt-8">
        <div class="hero-blob -top-20 -right-16 -z-10 size-72 bg-primary/40"></div>
        <div class="hero-blob -bottom-24 left-10 -z-10 size-60 bg-accent/15"></div>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary-dark shadow-glow-primary">
                    <x-icon :name="$arriving ? 'plane-arrival' : 'plane-departure'" class="size-7" />
                </span>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $arriving ? 'Arrivals' : 'Departures' }} @if ($airport)<span class="hidden text-white/60 sm:inline">· {{ $airport }}</span>@endif</h2>
                    <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-white/70">
                        <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-300"><span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span></span> Live board</span>
                        <span data-updated></span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Quick stats: click to filter by status --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4" data-stats>
            @foreach ([
                ['key' => '', 'label' => 'All flights', 'icon' => 'plane', 'tone' => 'bg-white/10 ring-white/15'],
                ['key' => 'active', 'label' => $arriving ? 'En route' : 'Departed', 'icon' => 'bolt', 'tone' => 'bg-emerald-400/10 ring-emerald-300/25'],
                ['key' => 'delayed', 'label' => 'Delayed', 'icon' => 'clock', 'tone' => 'bg-amber-400/10 ring-amber-300/25'],
                ['key' => 'cancelled', 'label' => 'Cancelled', 'icon' => 'x-circle', 'tone' => 'bg-red-400/10 ring-red-300/25'],
            ] as $stat)
                <button type="button" data-stat="{{ $stat['key'] }}" aria-pressed="{{ $stat['key'] === '' ? 'true' : 'false' }}"
                        class="group rounded-2xl p-4 text-left ring-1 transition hover:-translate-y-0.5 hover:bg-white/15 aria-pressed:bg-white aria-pressed:text-secondary aria-pressed:ring-white {{ $stat['tone'] }}">
                    <span class="flex items-center justify-between text-xs font-semibold tracking-wide text-white/70 uppercase group-aria-pressed:text-muted">
                        {{ $stat['label'] }} <x-icon :name="$stat['icon']" class="size-4" />
                    </span>
                    <span data-stat-count class="mt-1 block font-display text-3xl font-bold">–</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Filters --}}
    <div class="relative z-10 mx-3 -mt-12 grid grid-cols-2 gap-3 rounded-3xl border border-line bg-white p-4 shadow-float sm:mx-6 sm:p-5 lg:grid-cols-12">
        <div class="relative col-span-2 lg:col-span-5">
            <label for="board-search-{{ $direction }}" class="sr-only">Search</label>
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-primary" />
            <input id="board-search-{{ $direction }}" type="search" data-search placeholder="Flight number, airline or city" class="form-control bg-soft py-3 pl-11">
        </div>
        <div class="lg:col-span-2">
            <label for="board-airline-{{ $direction }}" class="sr-only">Airline</label>
            <select id="board-airline-{{ $direction }}" data-filter="airline" class="form-control bg-soft py-3"><option value="">All airlines</option></select>
        </div>
        <div class="lg:col-span-2">
            <label for="board-status-{{ $direction }}" class="sr-only">Status</label>
            <select id="board-status-{{ $direction }}" data-filter="status" class="form-control bg-soft py-3 capitalize"><option value="">All statuses</option></select>
        </div>
        <div class="lg:col-span-2">
            <label for="board-terminal-{{ $direction }}" class="sr-only">Terminal</label>
            <select id="board-terminal-{{ $direction }}" data-filter="terminal" class="form-control bg-soft py-3"><option value="">All terminals</option></select>
        </div>
        <button type="button" data-reset class="btn btn-secondary py-3 lg:col-span-1" aria-label="Reset filters" title="Reset filters"><x-icon name="refresh" class="size-4" /><span class="lg:sr-only">Reset</span></button>
    </div>

    <div class="rounded-b-3xl border border-t-0 border-line bg-soft px-3 pt-6 pb-4 sm:px-6">
        <p class="mb-3 px-1 text-sm"><span data-summary class="font-semibold text-secondary">Loading…</span></p>

        {{-- Desktop table --}}
        <div class="hidden overflow-hidden rounded-2xl border border-line bg-white shadow-card md:block">
            <table class="w-full text-left text-sm">
                <caption class="sr-only">{{ ucfirst($direction) }} @if ($airport) at {{ $airport }} @endif</caption>
                <thead class="bg-secondary/[0.03] text-xs font-semibold tracking-wider text-muted uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3.5">Time</th>
                        <th scope="col" class="px-5 py-3.5">Flight</th>
                        <th scope="col" class="px-5 py-3.5">{{ $arriving ? 'From' : 'To' }}</th>
                        <th scope="col" class="px-5 py-3.5">Status</th>
                        <th scope="col" class="px-5 py-3.5 text-center">Terminal</th>
                        <th scope="col" class="px-5 py-3.5 text-center">Gate</th>
                        @if ($arriving)<th scope="col" class="px-5 py-3.5 text-center">Baggage</th>@endif
                    </tr>
                </thead>
                <tbody data-rows class="divide-y divide-line">
                    @for ($i = 0; $i < 6; $i++)
                        <tr><td colspan="{{ $arriving ? 7 : 6 }}" class="px-5 py-4"><div class="skeleton h-10"></div></td></tr>
                    @endfor
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <ul data-cards class="grid gap-3 md:hidden">
            @for ($i = 0; $i < 4; $i++)<li class="skeleton h-32 rounded-2xl"></li>@endfor
        </ul>

        <div data-pager class="mt-5 flex flex-wrap items-center justify-center gap-2"></div>
        <p class="mt-4 flex items-center justify-center gap-1.5 text-center text-xs text-muted"><x-icon name="info" class="size-4 shrink-0" /> Times shown in local airport time. Always confirm with your airline before travelling.</p>
    </div>
</section>
