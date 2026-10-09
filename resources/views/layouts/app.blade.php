@php($settings = settings())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Hero images push a preload here, so the browser starts the LCP download before anything else. --}}
    @stack('preload')
    <x-site.seo :seo="$seo ?? []" />
    @if ($favicon = $settings->url('branding.favicon'))
        <link rel="icon" href="{{ $favicon }}">
        <link rel="apple-touch-icon" href="{{ $favicon }}">
    @endif
    <meta name="theme-color" content="{{ $settings->get('theme.primary_color') }}">
    @setting('seo.google_site_verification')<meta name="google-site-verification" content="{{ $settings->get('seo.google_site_verification') }}">@endsetting
    @setting('seo.bing_site_verification')<meta name="msvalidate.01" content="{{ $settings->get('seo.bing_site_verification') }}">@endsetting
    <link rel="preload" href="{{ Vite::asset('resources/fonts/inter-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ Vite::asset('resources/fonts/poppins-latin-700-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    {{-- Theme colours from Admin → Settings → Theme --}}
    <style>:root{ {{ $settings->cssVariables() }} }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @if (($gaId = $settings->get('seo.google_analytics_id')) && app()->isProduction())
        {{-- gtag.js is loaded on the first interaction (or 3.5s after load) so it never competes with the page for bandwidth or the main thread. Events queue in dataLayer until then. --}}
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',@json($gaId),{anonymize_ip:true});
        (function(){var done=false,events=['scroll','pointerdown','keydown','touchstart'];function load(){if(done)return;done=true;events.forEach(function(e){removeEventListener(e,load)});var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(@json($gaId));document.head.appendChild(s)}events.forEach(function(e){addEventListener(e,load,{once:true,passive:true})});addEventListener('load',function(){setTimeout(load,3500)})})();</script>
    @endif
</head>
<body class="flex min-h-screen flex-col bg-page font-sans text-ink">
    <a href="#main" class="sr-only z-[60] rounded-lg bg-primary px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

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

    @if ($settings->bool('contact.show_call_bar') && ($phone = site_phone()))
        <div class="fixed inset-x-0 bottom-0 z-40 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] md:hidden">
            <a href="{{ tel_href($phone) }}" class="flex items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-br from-accent to-accent-dark px-4 py-3.5 font-semibold text-on-accent shadow-float">
                <span class="relative flex size-2.5"><span class="absolute inline-flex size-full animate-ping rounded-full bg-white/70"></span><span class="relative inline-flex size-2.5 rounded-full bg-white"></span></span>
                <x-icon name="phone" class="size-5" /> Call {{ $phone }} @if ($label = $settings->get('contact.phone_label'))<span class="font-normal opacity-85">({{ $label }})</span>@endif
            </a>
        </div>
        <div class="h-20 md:hidden" aria-hidden="true"></div>
    @endif

    @foreach (['success', 'error'] as $type)
        @if (session($type))<span hidden data-flash="{{ $type }}" data-message="{{ session($type) }}"></span>@endif
    @endforeach
    <x-icon-sprite />
    @stack('scripts')
</body>
</html>
