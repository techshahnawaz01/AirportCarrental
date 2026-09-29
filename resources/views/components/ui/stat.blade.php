@props(['label', 'value', 'icon' => 'chart', 'href' => null, 'hint' => null])
<{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'card flex items-center gap-4 p-5 transition '.($href ? 'hover:border-primary/40 hover:shadow-md' : '')]) }}>
    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
        <x-icon :name="$icon" class="size-5" />
    </div>
    <div class="min-w-0">
        <p class="truncate text-sm text-fg-muted">{{ $label }}</p>
        <p class="text-2xl font-semibold tracking-tight text-fg">{{ number_format($value) }}</p>
        @if ($hint)<p class="truncate text-xs text-fg-muted">{{ $hint }}</p>@endif
    </div>
</{{ $href ? 'a' : 'div' }}>
