{{-- Every icon used on the page, drawn once with its viewBox and stroke/fill; <x-icon> references these with <use>. Must come after all icons. --}}
@php($icons = app(\App\Support\Icons::class))
@if ($icons->usesSprite() && $icons->used())
    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">
        @foreach ($icons->used() as $name)<symbol id="icon-{{ $name }}" {!! $icons->svgAttributes($name) !!}>{!! $icons->shapes($name) !!}</symbol>@endforeach
    </svg>
@endif
