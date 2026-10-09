{{-- An author's social profile links as round icon buttons. --}}
@props(['author', 'light' => false])
@php($links = $author->socialLinks())
@if ($links)
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
        @foreach ($links as $network => $url)
            <li>
                <a href="{{ $url }}" target="_blank" rel="noopener nofollow me" aria-label="{{ $author->name }} on {{ \App\Models\User::SOCIAL[$network] }}"
                   @class([
                       'flex size-9 items-center justify-center rounded-full transition',
                       'bg-white/15 text-white ring-1 ring-white/25 hover:bg-white hover:text-secondary' => $light,
                       'bg-soft text-muted ring-1 ring-line hover:bg-primary hover:text-white hover:ring-primary' => ! $light,
                   ])>
                    <x-icon :name="$network === 'website' ? 'globe' : $network" class="size-4" />
                </a>
            </li>
        @endforeach
    </ul>
@endif
