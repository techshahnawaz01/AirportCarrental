@if ($logs->isEmpty())
    <x-ui.empty-state icon="bolt" title="No activity yet" />
@else
    <ul class="divide-y divide-line">
        @foreach ($logs as $log)
            <li class="flex gap-3 px-5 py-3">
                <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-muted text-xs font-semibold text-fg-muted">{{ $log->user?->initials() ?: '—' }}</span>
                <div class="min-w-0 text-sm">
                    <p><span class="font-medium">{{ $log->user?->name ?? 'System' }}</span> <span class="text-fg-muted">{{ lcfirst($log->description) }}</span></p>
                    <p class="text-xs text-fg-muted" title="{{ local_date($log->created_at, 'M j, Y g:i A') }}">{{ $log->created_at?->diffForHumans() }}@if (empty($compact) && $log->ip_address) · {{ $log->ip_address }}@endif</p>
                </div>
            </li>
        @endforeach
    </ul>
    @if (empty($compact))
        <div class="border-t border-line px-5 py-4">{{ $logs->links() }}</div>
    @endif
@endif
