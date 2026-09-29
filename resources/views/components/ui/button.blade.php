{{-- <x-ui.button variant="primary|secondary|ghost|danger|accent" size="sm|lg" href="…" icon="plus"> --}}
@props(['variant' => 'primary', 'size' => null, 'href' => null, 'icon' => null, 'type' => 'submit'])
@php $classes = trim("btn btn-{$variant} ".($size ? "btn-{$size}" : '')); @endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" class="size-4" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" class="size-4" />@endif
        {{ $slot }}
    </button>
@endif
