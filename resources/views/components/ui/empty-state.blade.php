@props(['icon' => 'inbox', 'title', 'description' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-16 text-center']) }}>
    <div class="flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary ring-1 ring-primary/15">
        <x-icon :name="$icon" class="size-7" />
    </div>
    <h3 class="mt-5 text-base font-semibold text-fg">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-fg-muted">{{ $description }}</p>
    @endif
    @if (trim($slot))
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
