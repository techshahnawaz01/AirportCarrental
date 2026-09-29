@php
    $networks = collect(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'twitter' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'whatsapp' => 'WhatsApp'])
        ->filter(fn ($label, $key) => filled(settings("social.{$key}")));
@endphp
@if ($networks->isNotEmpty())
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
        @foreach ($networks as $key => $label)
            <li>
                <a href="{{ settings("social.{$key}") }}" target="_blank" rel="noopener" aria-label="{{ $label }}"
                   class="flex size-9 items-center justify-center rounded-full bg-white/10 text-white/80 transition hover:bg-white hover:text-secondary">
                    <x-icon :name="$key" class="size-4" />
                </a>
            </li>
        @endforeach
    </ul>
@endif
