<div data-repeater-item class="rounded-xl border border-line bg-surface-muted/50 p-4">
    <div class="mb-3 flex items-center justify-between">
        <span class="text-xs font-semibold text-fg-muted uppercase">Room <span data-repeater-number></span></span>
        <div class="flex gap-1">
            <button type="button" class="btn-icon size-7" data-repeater-move="up" aria-label="Move up"><x-icon name="arrow-up" class="size-4" /></button>
            <button type="button" class="btn-icon size-7" data-repeater-move="down" aria-label="Move down"><x-icon name="arrow-down" class="size-4" /></button>
            <button type="button" class="btn-icon size-7 hover:text-red-600" data-repeater-remove aria-label="Remove room"><x-icon name="trash" class="size-4" /></button>
        </div>
    </div>
    <div class="grid gap-4 md:grid-cols-5">
        <div class="md:col-span-2">
            <x-ui.image-field :name="'data[rooms]['.$index.'][image_id]'" :value="$room['image_id'] ?? null" :url="$image?->url" aspect="aspect-[4/3]" />
        </div>
        <div class="space-y-3 md:col-span-3">
            <input name="data[rooms][{{ $index }}][title]" value="{{ $room['title'] ?? '' }}" class="form-control" placeholder="Room name" aria-label="Room name" maxlength="255">
            <textarea name="data[rooms][{{ $index }}][description]" rows="2" class="form-control" placeholder="Short description" aria-label="Description" maxlength="2000">{{ $room['description'] ?? '' }}</textarea>
            <textarea name="data[rooms][{{ $index }}][amenities]" rows="4" class="form-control text-sm" placeholder="Amenities — one per line" aria-label="Amenities" maxlength="3000">{{ implode("\n", (array) ($room['amenities'] ?? [])) }}</textarea>
        </div>
    </div>
</div>
