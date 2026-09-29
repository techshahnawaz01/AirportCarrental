@php($settings = settings())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · {{ $settings->get('branding.site_name') }}</title>
    @if ($favicon = $settings->url('branding.favicon'))<link rel="icon" href="{{ $favicon }}">@endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>:root{ {{ $settings->cssVariables() }} }</style>
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-surface-muted font-sans text-fg antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-secondary lg:block">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/80 to-secondary"></div>
            <div class="relative flex h-full flex-col justify-between p-12 text-white">
                <p class="text-lg font-semibold">{{ $settings->get('branding.site_name') }}</p>
                <div>
                    <p class="text-3xl font-bold tracking-tight">Manage your website with confidence.</p>
                    <p class="mt-3 max-w-md text-white/75">Pages, media, navigation, SEO and branding — all in one place.</p>
                </div>
                <p class="text-sm text-white/60">{{ $settings->copyright() }}</p>
            </div>
        </div>
        <div class="flex items-center justify-center px-4 py-12 sm:px-8">
            <div class="w-full max-w-sm">
                <a href="{{ url('/') }}" class="mb-10 inline-flex">
                    @if ($logo = $settings->url('branding.admin_logo', 'branding.logo'))
                        <img src="{{ $logo }}" alt="{{ $settings->get('branding.site_name') }}" class="h-10 w-auto">
                    @else
                        <span class="text-xl font-bold">{{ $settings->get('branding.site_name') }}</span>
                    @endif
                </a>
                @yield('content')
            </div>
        </div>
    </div>
    @foreach (['success', 'error'] as $type)
        @if (session($type))<span hidden data-flash="{{ $type }}" data-message="{{ session($type) }}"></span>@endif
    @endforeach
</body>
</html>
