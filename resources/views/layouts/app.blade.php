@php($settings = settings())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-site.seo :seo="$seo ?? []" />
    @if ($favicon = $settings->url('branding.favicon'))
        <link rel="icon" href="{{ $favicon }}">
        <link rel="apple-touch-icon" href="{{ $favicon }}">
    @endif
    <meta name="theme-color" content="{{ $settings->get('theme.primary_color') }}">
    @setting('seo.google_site_verification')<meta name="google-site-verification" content="{{ $settings->get('seo.google_site_verification') }}">@endsetting
    @setting('seo.bing_site_verification')<meta name="msvalidate.01" content="{{ $settings->get('seo.bing_site_verification') }}">@endsetting
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    {{-- Theme colours from Admin → Settings → Theme --}}
    <style>:root{ {{ $settings->cssVariables() }} }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @if (($gaId = $settings->get('seo.google_analytics_id')) && app()->isProduction())
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',@json($gaId),{anonymize_ip:true});</script>
    @endif
</head>
<body class="flex min-h-screen flex-col bg-page font-sans text-ink">
    <a href="#main" class="sr-only z-50 rounded-lg bg-primary px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

    @if (! empty($isPreview))
        <div class="bg-amber-400 px-4 py-2 text-center text-sm font-medium text-amber-950">
            Preview — this page is not published and is only visible to signed-in staff.
        </div>
    @endif

    <x-site.header />

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    <x-site.footer />

    @if ($settings->bool('contact.show_call_bar') && ($phone = $settings->get('contact.phone')))
        <a href="{{ tel_href($phone) }}" class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-center gap-2 bg-button px-4 py-3 font-semibold text-white shadow-[0_-4px_12px_rgba(0,0,0,.15)] md:hidden">
            <x-icon name="phone" class="size-5" /> Call {{ $phone }} @if ($label = $settings->get('contact.phone_label'))<span class="font-normal opacity-80">({{ $label }})</span>@endif
        </a>
        <div class="h-12 md:hidden" aria-hidden="true"></div>
    @endif

    @foreach (['success', 'error'] as $type)
        @if (session($type))<span hidden data-flash="{{ $type }}" data-message="{{ session($type) }}"></span>@endif
    @endforeach
    @stack('scripts')
</body>
</html>
