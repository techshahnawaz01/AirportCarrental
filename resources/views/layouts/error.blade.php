@php($settings = settings())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ $settings->get('branding.site_name') }}</title>
    @if ($favicon = $settings->url('branding.favicon'))<link rel="icon" href="{{ $favicon }}">@endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,800&display=swap" rel="stylesheet">
    <style>:root{ {{ $settings->cssVariables() }} }</style>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-4 py-16 font-sans text-ink">
    <main class="w-full max-w-lg text-center">
        <a href="{{ url('/') }}" class="inline-block">
            @if ($logo = $settings->url('branding.logo'))
                <img src="{{ $logo }}" alt="{{ $settings->get('branding.site_name') }}" class="mx-auto h-10 w-auto">
            @else
                <span class="text-xl font-extrabold text-secondary">{{ $settings->get('branding.site_name') }}</span>
            @endif
        </a>
        <p class="mt-12 text-7xl font-extrabold tracking-tight text-primary">@yield('code')</p>
        <h1 class="mt-4 text-2xl font-bold text-secondary sm:text-3xl">@yield('title')</h1>
        <p class="mt-3 text-slate-600">@yield('message')</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            @yield('actions')
            <a href="{{ url('/') }}" class="btn btn-primary">Go to home page</a>
        </div>
    </main>
</body>
</html>
