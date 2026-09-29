@if ($comments->isEmpty())
    <x-ui.empty-state icon="chat" title="No comments found" />
@else
    <ul class="divide-y divide-line">
        @foreach ($comments as $comment)
            <li data-row class="flex flex-col gap-3 p-4 sm:flex-row sm:items-start">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                        <span class="font-semibold">{{ $comment->name }}</span>
                        <a href="mailto:{{ $comment->email }}" class="text-fg-muted hover:text-primary">{{ $comment->email }}</a>
                        @if ($comment->rating)<x-ui.badge tone="warning">★ {{ $comment->rating }}/5</x-ui.badge>@endif
                        <span class="text-xs text-fg-muted">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-1.5 text-sm whitespace-pre-line">{{ $comment->body }}</p>
                    @if ($comment->page)
                        <a href="{{ $comment->page->url() }}#comments" target="_blank" rel="noopener" class="mt-1.5 inline-flex items-center gap-1 text-xs text-primary hover:underline">On: {{ $comment->page->title }} <x-icon name="external" class="size-3" /></a>
                    @endif
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <x-ui.status-toggle :url="route('admin.comments.toggle', $comment)" :active="$comment->is_approved" on="Approved" off="Pending" />
                    <button type="button" class="btn-icon hover:text-red-600" aria-label="Delete comment" data-action="{{ route('admin.comments.destroy', $comment) }}" data-method="DELETE" data-confirm="Delete this comment?" data-confirm-button="Delete" data-remove="li"><x-icon name="trash" class="size-4" /></button>
                </div>
            </li>
        @endforeach
    </ul>
    <div class="border-t border-line px-4 py-3">{{ $comments->links() }}</div>
@endif
