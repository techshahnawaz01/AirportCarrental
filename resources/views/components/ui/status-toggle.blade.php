{{-- Badge button that flips a boolean via AJAX (expects { data: { active } }). --}}
@props(['url', 'active', 'on' => 'Published', 'off' => 'Draft'])
<button type="button" data-action="{{ $url }}" data-method="PATCH" data-toggle-scope aria-pressed="{{ $active ? 'true' : 'false' }}"
        title="Click to toggle" {{ $attributes->merge(['class' => 'rounded-full focus-visible:ring-2 focus-visible:ring-primary']) }}>
    <span data-on class="badge badge-success" @unless ($active) hidden @endunless><span class="size-1.5 rounded-full bg-current"></span>{{ $on }}</span>
    <span data-off class="badge badge-neutral" @if ($active) hidden @endif><span class="size-1.5 rounded-full bg-current"></span>{{ $off }}</span>
</button>
