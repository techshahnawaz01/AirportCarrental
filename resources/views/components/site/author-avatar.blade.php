{{-- Round author photo, falling back to initials. --}}
@props(['author', 'size' => 'size-10', 'text' => 'text-sm', 'loading' => 'lazy'])
@if ($author->avatar)
    <img src="{{ $author->avatar->url }}" alt="{{ $author->name }}" width="{{ $author->avatar->width }}" height="{{ $author->avatar->height }}" loading="{{ $loading }}"
         {{ $attributes->merge(['class' => "{$size} shrink-0 rounded-full object-cover ring-2 ring-white"]) }}>
@else
    <span {{ $attributes->merge(['class' => "{$size} {$text} flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary-dark font-display font-semibold text-white ring-2 ring-white"]) }} aria-hidden="true">{{ $author->initials() }}</span>
@endif
