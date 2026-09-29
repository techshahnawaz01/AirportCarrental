<section class="not-prose">
    <div class="overflow-hidden rounded-3xl bg-secondary text-white shadow-lg">
        <div class="grid gap-6 p-6 sm:p-10 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <p class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide text-accent uppercase"><x-icon name="shield" class="size-4" /> TSA checkpoints</p>
                <h2 class="mt-2 text-2xl font-bold sm:text-3xl">{{ $title }}</h2>
                @if ($data)
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs text-white/70">Right now</p>
                            <p class="mt-1 text-3xl font-extrabold">{{ $data['right_now'] }}<span class="text-base font-medium"> min</span></p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <p class="text-xs text-white/70">PreCheck lanes</p>
                            <p class="mt-1 text-3xl font-extrabold">{{ $data['precheck'] ?? '—' }}</p>
                        </div>
                    </div>
                    @if ($data['description'])<p class="mt-4 text-sm text-white/75">{{ $data['description'] }}</p>@endif
                @else
                    <p class="mt-4 text-sm text-white/75">Live wait-time data is unavailable right now. Allow extra time at security during peak hours.</p>
                @endif
            </div>
            @if ($data)
                <div class="lg:col-span-2 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-white p-4 text-slate-800">
                        <h3 class="font-semibold text-secondary">Estimated hourly waits</h3>
                        <div class="mt-3 max-h-72 overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="sticky top-0 bg-white text-left text-xs text-slate-500 uppercase"><tr><th class="py-2">Time</th><th class="py-2 text-right">Minutes</th></tr></thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($data['hourly'] as $slot)
                                        <tr><td class="py-2">{{ $slot['slot'] }}</td><td class="py-2 text-right font-semibold">{{ $slot['minutes'] ?? '—' }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-white p-4 text-slate-800">
                        <h3 class="font-semibold text-secondary">PreCheck checkpoints</h3>
                        <ul class="mt-3 max-h-72 divide-y divide-line overflow-y-auto text-sm">
                            @forelse ($data['checkpoints'] as $checkpoint)
                                <li class="flex items-center justify-between gap-3 py-2">
                                    <span><span class="font-medium">{{ $checkpoint['terminal'] }}</span> · {{ $checkpoint['checkpoint'] }}</span>
                                    <span class="badge {{ strcasecmp($checkpoint['status'], 'open') === 0 ? 'badge-success' : 'badge-danger' }}">{{ $checkpoint['status'] }}</span>
                                </li>
                            @empty
                                <li class="py-6 text-center text-slate-500">No checkpoint data.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
