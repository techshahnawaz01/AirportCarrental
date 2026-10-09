@props(['size' => 'md'])
@php
    $networks = collect(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'twitter' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'whatsapp' => 'WhatsApp'])
        ->filter(fn ($label, $key) => filled(settings("social.{$key}")));
@endphp
@if ($networks->isNotEmpty())
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
        @foreach ($networks as $key => $label)
            <li>
                <a href="{{ settings("social.{$key}") }}" target="_blank" rel="noopener" aria-label="{{ $label }}"
                   @class([
                       'flex items-center justify-center rounded-full transition',
                       'size-7 text-white/70 hover:bg-white/10 hover:text-accent' => $size === 'sm',
                       'size-10 bg-white/10 text-white/80 ring-1 ring-white/10 hover:-translate-y-0.5 hover:bg-accent hover:text-on-accent hover:ring-accent' => $size !== 'sm',
                   ])>
                    <x-icon :name="$key" @class(['size-3.5' => $size === 'sm', 'size-4' => $size !== 'sm']) />
                </a>
            </li>
        @endforeach
    </ul>
@endif
