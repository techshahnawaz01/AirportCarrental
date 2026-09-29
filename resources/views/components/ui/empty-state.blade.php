@props(['icon' => 'inbox', 'title', 'description' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-16 text-center']) }}>
    <div class="flex size-12 items-center justify-center rounded-full bg-surface-muted text-fg-muted">
        <x-icon :name="$icon" class="size-6" />
    </div>
    <h3 class="mt-4 text-sm font-semibold text-fg">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-fg-muted">{{ $description }}</p>
    @endif
    @if (trim($slot))
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
