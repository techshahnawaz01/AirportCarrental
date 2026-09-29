@props(['setting' => 'branding.logo', 'fallback' => null, 'imgClass' => 'h-10 w-auto'])
@php($url = settings()->url($setting, $fallback))
<a href="{{ url('/') }}" {{ $attributes->merge(['class' => 'inline-flex min-w-0 items-center gap-2']) }} aria-label="{{ settings('branding.site_name') }} – home">
    @if ($url)
        <img src="{{ $url }}" alt="{{ settings('branding.site_name') }}" class="{{ $imgClass }} max-w-full object-contain object-left">
    @else
        <span class="text-lg font-extrabold tracking-tight text-secondary">{{ settings('branding.site_name') }}</span>
    @endif
</a>
