{{-- Native <dialog>. Open with data-modal-open="id", close with data-modal-close. --}}
@props(['id', 'title', 'size' => 'max-w-lg'])
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title" {{ $attributes->merge(['class' => "m-auto w-[calc(100%-2rem)] {$size} rounded-2xl border border-line bg-surface-raised p-0 text-fg shadow-2xl backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm"]) }}>
    <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
        <h2 id="{{ $id }}-title" class="text-base font-semibold" data-dialog-title>{{ $title }}</h2>
        <button type="button" class="btn-icon -mr-2" data-modal-close aria-label="Close"><x-icon name="x" /></button>
    </div>
    {{ $slot }}
</dialog>
