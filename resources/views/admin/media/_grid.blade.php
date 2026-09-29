@if ($items->isEmpty())
    <x-ui.empty-state icon="photo" title="No media found" :description="request('q') ? 'Try a different search.' : 'Upload your first image above.'" />
@else
    <ul class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6" role="list">
        @foreach ($items as $item)
            <li data-row>
                <button type="button" class="group block w-full text-left" data-media-details="{{ json_encode($item->toPickerArray()) }}"
                        data-update-url="{{ route('admin.media.update', $item) }}" data-delete-url="{{ route('admin.media.destroy', $item) }}">
                    <span class="block aspect-square overflow-hidden rounded-xl border border-line bg-surface-muted">
                        <img src="{{ $item->url }}" alt="{{ $item->alt }}" loading="lazy" class="size-full object-cover transition group-hover:scale-105">
                    </span>
                    <span class="mt-2 block truncate text-xs font-medium">{{ $item->original_name }}</span>
                    <span class="block text-[11px] text-fg-muted">{{ $item->humanSize() }}@if ($item->width) · {{ $item->width }}×{{ $item->height }}@endif</span>
                </button>
            </li>
        @endforeach
    </ul>
    <div class="border-t border-line px-4 py-3">{{ $items->links() }}</div>
@endif
