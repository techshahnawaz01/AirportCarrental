<div @class(['flex items-center gap-3 px-4 py-3', 'pl-10' => $level])>
    @if ($item->image)
        <img src="{{ $item->image->url }}" alt="" class="size-10 shrink-0 rounded-lg object-cover">
    @else
        <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-surface-muted text-fg-muted"><x-icon :name="$item->icon ?: 'link'" class="size-5" /></span>
    @endif
    <div class="min-w-0 flex-1">
        <p class="flex items-center gap-2 truncate text-sm font-medium">{{ $item->label }} @unless ($item->is_active)<x-ui.badge>Hidden</x-ui.badge>@endunless</p>
        <p class="truncate text-xs text-fg-muted">{{ $item->page ? 'Page: '.$item->page->title : ($item->url ?: '—') }}</p>
    </div>
    <div class="flex shrink-0 gap-1">
        <button type="button" class="btn-icon size-8" data-move="up" aria-label="Move up"><x-icon name="arrow-up" class="size-4" /></button>
        <button type="button" class="btn-icon size-8" data-move="down" aria-label="Move down"><x-icon name="arrow-down" class="size-4" /></button>
        @unless ($level)
            <button type="button" class="btn-icon size-8" data-add-item data-parent="{{ $item->id }}" aria-label="Add child item" title="Add child item"><x-icon name="plus" class="size-4" /></button>
        @endunless
        <button type="button" class="btn-icon size-8" aria-label="Edit" data-edit-item="{{ json_encode([
            'id' => $item->id, 'label' => $item->label, 'url' => $item->url, 'page_id' => $item->page_id, 'parent_id' => $item->parent_id,
            'icon' => $item->icon, 'description' => $item->description, 'image_id' => $item->image_id, 'image_url' => $item->image?->url,
            'open_in_new_tab' => $item->open_in_new_tab, 'is_active' => $item->is_active,
            'update_url' => route('admin.menus.items.update', [$item->menu_id, $item->id]),
        ]) }}"><x-icon name="pencil" class="size-4" /></button>
        <button type="button" class="btn-icon size-8 hover:text-red-600" aria-label="Delete" data-action="{{ route('admin.menus.items.destroy', [$item->menu_id, $item->id]) }}" data-method="DELETE" data-confirm="Remove “{{ $item->label }}”{{ $level ? '' : ' and its child items' }}?" data-confirm-button="Remove" data-remove="[data-item-id]"><x-icon name="trash" class="size-4" /></button>
    </div>
</div>
