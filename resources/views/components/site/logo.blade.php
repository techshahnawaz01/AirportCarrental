@props(['setting' => 'branding.logo', 'fallback' => null, 'imgClass' => 'h-10 w-auto', 'light' => false])
@php($url = settings()->url($setting, $fallback))
<a href="{{ url('/') }}" {{ $attributes->merge(['class' => 'inline-flex min-w-0 items-center gap-2']) }} aria-label="{{ settings('branding.site_name') }} – home">
    @if ($url)
        <img src="{{ $url }}" alt="{{ settings('branding.site_name') }}" class="{{ $imgClass }} max-w-full object-contain object-left">
    @else
        <span class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary-dark text-white shadow-glow-primary"><x-icon name="plane" class="size-5" /></span>
        <span @class(['font-display text-lg font-bold tracking-tight', 'text-white' => $light, 'text-secondary' => ! $light])>{{ settings('branding.site_name') }}</span>
    @endif
</a>
