{{--
    Image picker with preview, direct upload, "choose from library" and remove.
    value-key="id"   → hidden input stores the media id (pages, menu items)
    value-key="path" → hidden input stores the storage path (settings)
--}}
@props([
    'name', 'label' => null, 'value' => null, 'url' => null, 'upload' => null, 'help' => null,
    'valueKey' => 'id', 'destroyUrl' => null, 'key' => null, 'accept' => 'image/png,image/jpeg,image/webp,image/gif',
    'maxKb' => null, 'aspect' => 'aspect-video',
])
@php $errorKey = preg_replace(['/\]\[|\[/', '/\]/'], ['.', ''], $upload ?: $name); @endphp
<div data-image-field data-value-key="{{ $valueKey }}" @if ($key) data-key="{{ $key }}" @endif
     @if ($destroyUrl) data-destroy-url="{{ $destroyUrl }}" @endif @if ($maxKb) data-max-kb="{{ $maxKb }}" @endif
     {{ $attributes->merge(['class' => '']) }}>
    @if ($label)<p class="form-label">{{ $label }}</p>@endif
    <div class="overflow-hidden rounded-xl border border-dashed border-line bg-surface-muted">
        <div class="relative flex {{ $aspect }} items-center justify-center bg-[repeating-conic-gradient(var(--color-surface)_0_25%,var(--color-surface-muted)_0_50%)] bg-[length:16px_16px]">
            <img data-image-preview src="{{ $url }}" alt="" class="max-h-full max-w-full object-contain p-2" @unless ($url) hidden @endunless>
            <div data-image-empty class="flex flex-col items-center gap-1 text-fg-muted" @if ($url) hidden @endif>
                <x-icon name="photo" class="size-8" />
                <span class="text-xs">No image selected</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 border-t border-line bg-surface p-2">
            <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-image-value>
            @if ($upload)
                <label class="btn btn-secondary btn-sm cursor-pointer">
                    <x-icon name="upload" class="size-4" /> Upload
                    <input type="file" name="{{ $upload }}" accept="{{ $accept }}" class="sr-only">
                </label>
            @endif
            <button type="button" class="btn btn-secondary btn-sm" data-image-pick><x-icon name="photo" class="size-4" /> Library</button>
            <button type="button" class="btn btn-ghost btn-sm text-red-600" data-image-remove @unless ($url) hidden @endunless><x-icon name="trash" class="size-4" /> Remove</button>
            <span data-image-name class="ml-auto truncate text-xs text-fg-muted"></span>
        </div>
    </div>
    @if ($help)<p class="form-help">{{ $help }}</p>@endif
    <p class="form-error" data-error-for="{{ $errorKey }}" role="alert" hidden></p>
</div>
