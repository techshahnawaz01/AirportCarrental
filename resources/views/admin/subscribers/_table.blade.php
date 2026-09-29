@if ($subscribers->isEmpty())
    <x-ui.empty-state icon="mail" title="No subscribers yet" />
@else
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>Email</th><th>Subscribed</th><th class="text-right"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @foreach ($subscribers as $subscriber)
                    <tr>
                        <td class="font-medium">{{ $subscriber->email }}</td>
                        <td class="text-fg-muted">{{ local_date($subscriber->created_at) }}</td>
                        <td class="text-right"><button type="button" class="btn-icon hover:text-red-600" aria-label="Remove" data-action="{{ route('admin.subscribers.destroy', $subscriber) }}" data-method="DELETE" data-confirm="Remove {{ $subscriber->email }}?" data-confirm-button="Remove" data-remove="tr"><x-icon name="trash" class="size-4" /></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="border-t border-line px-4 py-3">{{ $subscribers->links() }}</div>
@endif
