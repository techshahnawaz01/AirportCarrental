@props(['name', 'class' => 'size-5'])
{{-- Icon paths live in App\Support\Icons. Unknown names render nothing. --}}
@php($icons = app(\App\Support\Icons::class))
@if ($icons->has($name))
    @if ($icons->usesSprite())
        @php($icons->use($name))
        <svg {{ $attributes->merge(['class' => $class]) }} aria-hidden="true"><use href="#icon-{{ $name }}"/></svg>
    @else
        <svg {{ $attributes->merge(['class' => $class]) }} {!! $icons->svgAttributes($name) !!} aria-hidden="true">{!! $icons->shapes($name) !!}</svg>
    @endif
@endif
