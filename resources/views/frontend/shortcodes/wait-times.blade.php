<section class="not-prose" data-reveal>
    <div class="relative isolate overflow-hidden rounded-[2rem] bg-secondary text-white shadow-float">
        <div class="hero-blob -top-24 -left-24 -z-10 size-80 bg-primary/40"></div>
        <div class="hero-blob -right-20 -bottom-24 -z-10 size-72 bg-accent/20"></div>
        <div class="grid gap-8 p-6 sm:p-10 lg:grid-cols-3 lg:p-12">
            <div class="lg:col-span-1">
                <p class="eyebrow-light"><x-icon name="shield" class="size-4 text-accent" /> TSA checkpoints</p>
                <h3 class="mt-5 text-2xl leading-tight font-bold tracking-tight sm:text-3xl lg:text-4xl">{{ $title }}</h3>
                @if ($data)
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-gradient-to-br from-accent to-accent-dark p-5 text-on-accent shadow-glow-accent">
                            <p class="text-xs font-semibold opacity-85">Right now</p>
                            <p class="mt-1 font-display text-4xl font-bold">{{ $data['right_now'] }}<span class="text-base font-medium"> min</span></p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/15">
                            <p class="text-xs font-semibold text-white/70">PreCheck lanes</p>
                            <p class="mt-1 font-display text-4xl font-bold">{{ $data['precheck'] ?? '—' }}</p>
                        </div>
                    </div>
                    @if ($data['description'])<p class="mt-4 text-sm text-white/75">{{ $data['description'] }}</p>@endif
                @else
                    <p class="mt-4 text-sm text-white/75">Live wait-time data is unavailable right now. Allow extra time at security during peak hours.</p>
                @endif
            </div>
            @if ($data)
                <div class="lg:col-span-2 grid gap-4 md:grid-cols-2">
                    <div class="rounded-3xl bg-white p-5 text-slate-800 shadow-card">
                        <h4 class="flex items-center gap-2 font-semibold text-secondary"><x-icon name="clock" class="size-5 text-primary" /> Estimated hourly waits</h4>
                        <div class="mt-3 max-h-72 overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="sticky top-0 bg-white text-left text-xs text-slate-500 uppercase"><tr><th class="py-2">Time</th><th class="py-2 text-right">Minutes</th></tr></thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($data['hourly'] as $slot)
                                        <tr><td class="py-2">{{ $slot['slot'] }}</td><td class="py-2 text-right font-bold text-primary">{{ $slot['minutes'] ?? '—' }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="rounded-3xl bg-white p-5 text-slate-800 shadow-card">
                        <h4 class="flex items-center gap-2 font-semibold text-secondary"><x-icon name="check-circle" class="size-5 text-primary" /> PreCheck checkpoints</h4>
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
