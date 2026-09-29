<li class="flex items-center gap-3 px-5 py-3 text-sm">
    <div class="min-w-0 flex-1">
        <p class="truncate font-mono text-xs">/{{ $redirect->from_path }}</p>
        <p class="truncate text-xs text-fg-muted">→ {{ $redirect->to_url }}</p>
    </div>
    <x-ui.badge>{{ $redirect->status_code }}</x-ui.badge>
    <span class="text-xs text-fg-muted" title="Hits">{{ number_format($redirect->hits) }} hits</span>
    <button type="button" class="btn-icon hover:text-red-600" aria-label="Remove redirect" data-action="{{ route('admin.redirects.destroy', $redirect) }}" data-method="DELETE" data-confirm="Remove the redirect from /{{ $redirect->from_path }}?" data-confirm-button="Remove" data-remove="li"><x-icon name="trash" class="size-4" /></button>
</li>
