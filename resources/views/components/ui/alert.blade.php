@props(['tone' => 'info', 'title' => null])
@php
    $styles = [
        'info' => ['bg-primary/5 border-primary/20 text-fg', 'info', 'text-primary'],
        'success' => ['bg-emerald-500/5 border-emerald-500/20 text-fg', 'check-circle', 'text-emerald-600'],
        'warning' => ['bg-amber-500/5 border-amber-500/30 text-fg', 'warning', 'text-amber-600'],
        'danger' => ['bg-red-500/5 border-red-500/20 text-fg', 'x-circle', 'text-red-600'],
    ][$tone] ?? ['', 'info', ''];
@endphp
<div role="{{ $tone === 'danger' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => "flex gap-3 rounded-xl border p-4 text-sm {$styles[0]}"]) }}>
    <x-icon :name="$styles[1]" class="size-5 shrink-0 {{ $styles[2] }}" />
    <div class="min-w-0 flex-1">
        @if ($title)<p class="font-semibold">{{ $title }}</p>@endif
        <div @class(['mt-1 text-fg-muted' => $title])>{{ $slot }}</div>
    </div>
</div>
