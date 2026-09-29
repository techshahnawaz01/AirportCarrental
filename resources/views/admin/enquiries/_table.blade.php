@if ($enquiries->isEmpty())
    <x-ui.empty-state icon="inbox" title="No enquiries found" description="New contact form submissions will appear here." />
@else
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>From</th><th class="hidden md:table-cell">Subject</th><th>Received</th><th class="text-right"><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @foreach ($enquiries as $enquiry)
                    <tr @class(['font-semibold' => ! $enquiry->read_at])>
                        <td class="max-w-xs">
                            <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="flex items-center gap-2 hover:text-primary">
                                @unless ($enquiry->read_at)<span class="size-2 shrink-0 rounded-full bg-primary" title="Unread"></span>@endunless
                                <span class="truncate">{{ $enquiry->name }}</span>
                            </a>
                            <p class="truncate text-xs font-normal text-fg-muted">{{ $enquiry->email }}</p>
                        </td>
                        <td class="hidden max-w-sm md:table-cell"><p class="truncate">{{ $enquiry->subject ?: \Illuminate\Support\Str::limit($enquiry->message, 60) }}</p></td>
                        <td class="font-normal whitespace-nowrap text-fg-muted" title="{{ local_date($enquiry->created_at, 'M j, Y g:i A') }}">{{ $enquiry->created_at->diffForHumans() }}</td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="btn-icon" aria-label="Open"><x-icon name="eye" class="size-4" /></a>
                                <button type="button" class="btn-icon hover:text-red-600" aria-label="Delete" data-action="{{ route('admin.enquiries.destroy', $enquiry) }}" data-method="DELETE" data-confirm="Delete the enquiry from {{ $enquiry->name }}?" data-confirm-button="Delete" data-remove="tr"><x-icon name="trash" class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="border-t border-line px-4 py-3">{{ $enquiries->links() }}</div>
@endif
