@php($settings = settings())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ $settings->get('branding.site_name') }}</title>
    @if ($favicon = $settings->url('branding.favicon'))<link rel="icon" href="{{ $favicon }}">@endif
    <link rel="preload" href="{{ Vite::asset('resources/fonts/inter-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('resources/fonts/poppins-latin-700-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <style>:root{ {{ $settings->cssVariables() }} }</style>
    @vite(['resources/css/app.css'])
</head>
<body class="relative isolate flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-primary-soft via-white to-accent-soft px-4 py-16 font-sans text-ink">
    <div class="hero-blob -top-32 -left-32 -z-10 size-[28rem] bg-primary/15"></div>
    <div class="hero-blob -right-32 -bottom-32 -z-10 size-[28rem] bg-accent/15"></div>
    <main class="card-premium w-full max-w-xl px-6 py-12 text-center sm:px-12">
        <a href="{{ url('/') }}" class="inline-block">
            @if ($logo = $settings->url('branding.logo'))
                <img src="{{ $logo }}" alt="{{ $settings->get('branding.site_name') }}" class="mx-auto h-10 w-auto">
            @else
                <span class="text-xl font-bold text-secondary">{{ $settings->get('branding.site_name') }}</span>
            @endif
        </a>
        <p class="mt-10 bg-gradient-to-br from-primary to-accent bg-clip-text font-display text-8xl font-bold tracking-tight text-transparent sm:text-9xl">@yield('code')</p>
        <h1 class="mt-4 text-2xl font-bold text-secondary sm:text-3xl">@yield('title')</h1>
        <p class="mt-3 text-muted">@yield('message')</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            @yield('actions')
            <a href="{{ url('/') }}" class="btn btn-accent">Go to home page</a>
        </div>
    </main>
</body>
</html>
