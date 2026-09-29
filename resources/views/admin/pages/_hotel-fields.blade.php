<x-ui.card title="Hotel details" description="Structured content for the hotel template.">
    <div class="space-y-5">
        <x-ui.input name="data[address]" label="Address" :value="$page->meta('address')" maxlength="500" />
        <x-ui.input name="data[location_title]" label="Location heading" :value="$page->meta('location_title')" maxlength="255" />
        <x-ui.field label="Location description" name="data[location_description]">
            <textarea name="data[location_description]" data-editor rows="8" class="form-control" aria-label="Location description">{{ old('data.location_description', $page->meta('location_description')) }}</textarea>
        </x-ui.field>
        <x-ui.input name="data[map_embed_url]" type="url" label="Google Maps embed URL" :value="$page->meta('map_embed_url')" help="In Google Maps choose Share → Embed a map and copy the src URL." />
        <x-ui.input name="data[notice]" label="Rooms notice" :value="$page->meta('notice')" maxlength="1000" help="Optional note shown below the rooms." />

        <div data-gallery-field data-name="data[gallery]">
            <p class="form-label">Gallery</p>
            <ul data-gallery-list class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                @foreach (\App\Models\Media::whereIn('id', (array) $page->meta('gallery', []))->get()->sortBy(fn ($m) => array_search($m->id, (array) $page->meta('gallery', []))) as $image)
                    <li class="group relative aspect-square overflow-hidden rounded-lg border border-line bg-surface-muted">
                        <img src="{{ $image->url }}" alt="" class="size-full object-cover">
                        <input type="hidden" name="data[gallery][]" value="{{ $image->id }}">
                        <div class="absolute inset-x-0 bottom-0 flex justify-between gap-1 bg-slate-950/60 p-1 sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-within:opacity-100">
                            <button type="button" data-move="-1" class="rounded px-1.5 text-white hover:bg-white/20" aria-label="Move left">←</button>
                            <button type="button" data-remove class="rounded px-1.5 text-white hover:bg-red-600" aria-label="Remove">✕</button>
                            <button type="button" data-move="1" class="rounded px-1.5 text-white hover:bg-white/20" aria-label="Move right">→</button>
                        </div>
                    </li>
                @endforeach
            </ul>
            <button type="button" data-gallery-add class="btn btn-secondary btn-sm mt-3"><x-icon name="photo" class="size-4" /> Add images</button>
            <p class="form-error" data-error-for="data.gallery" hidden></p>
        </div>

        <div data-repeater="rooms">
            <p class="form-label">Rooms &amp; suites</p>
            <div data-repeater-items class="space-y-4">
                @php($roomImages = \App\Models\Media::whereIn('id', collect($rooms)->pluck('image_id')->filter())->get()->keyBy('id'))
                @foreach ($rooms as $i => $room)
                    @include('admin.pages._room-row', ['index' => $i, 'room' => $room, 'image' => $roomImages->get($room['image_id'] ?? null)])
                @endforeach
            </div>
            <p data-repeater-empty class="rounded-lg border border-dashed border-line p-6 text-center text-sm text-fg-muted">No rooms yet.</p>
            <template data-repeater-template>@include('admin.pages._room-row', ['index' => '__INDEX__', 'room' => [], 'image' => null])</template>
            <button type="button" class="btn btn-secondary btn-sm mt-4" data-repeater-add><x-icon name="plus" class="size-4" /> Add room</button>
        </div>
    </div>
</x-ui.card>
