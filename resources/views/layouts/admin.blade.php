@php($settings = settings())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') · {{ $settings->get('branding.site_name') }} Admin</title>
    @if ($favicon = $settings->url('branding.favicon'))<link rel="icon" href="{{ $favicon }}">@endif
    {{-- Apply the saved light/dark preference before first paint. --}}
    <script>try{if(localStorage.getItem('admin-theme')==='dark'||(!localStorage.getItem('admin-theme')&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark')}catch(e){}</script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>:root{ {{ $settings->cssVariables() }} }</style>
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-surface-muted font-sans text-fg antialiased">
    <x-admin.sidebar />

    <div class="lg:pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-line bg-surface/90 px-4 backdrop-blur sm:px-6">
            <button type="button" class="btn-icon lg:hidden" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open menu"><x-icon name="menu" /></button>

            <div class="hidden min-w-0 flex-1 text-fg-muted md:block">
                <x-ui.breadcrumbs :items="array_merge([['label' => 'Dashboard', 'url' => route('admin.dashboard')]], $breadcrumbs ?? [])" />
            </div>

            <form action="{{ route('admin.pages.index') }}" class="relative ml-auto w-full max-w-56 md:ml-0" role="search">
                <label for="admin-search" class="sr-only">Search pages</label>
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-fg-muted" />
                <input id="admin-search" type="search" name="q" placeholder="Search pages…" class="form-control py-2 pl-9">
            </form>

            <button type="button" class="btn-icon" data-theme-toggle aria-label="Toggle dark mode">
                <x-icon name="moon" class="size-5 dark:hidden" /><x-icon name="sun" class="hidden size-5 dark:block" />
            </button>

            <div class="relative" data-menu>
                <button type="button" data-menu-button aria-expanded="false" aria-haspopup="true" class="flex items-center gap-2 rounded-full p-0.5 hover:ring-2 hover:ring-line">
                    <span class="flex size-9 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white">{{ auth()->user()->initials() }}</span>
                    <span class="sr-only">Account menu</span>
                </button>
                <div data-menu-panel hidden class="absolute right-0 mt-2 w-60 rounded-xl border border-line bg-surface-raised p-1.5 shadow-xl">
                    <div class="border-b border-line px-3 py-2.5">
                        <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-fg-muted">{{ auth()->user()->email }} · {{ auth()->user()->roleLabel() }}</p>
                    </div>
                    <a href="{{ route('admin.profile.edit') }}" class="mt-1 flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-surface-muted"><x-icon name="user" class="size-4" /> Profile</a>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm hover:bg-surface-muted"><x-icon name="external" class="size-4" /> View website</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-500/10"><x-icon name="logout" class="size-4" /> Sign out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:py-8">
            @yield('content')
        </main>
    </div>

    {{-- Media picker modal (cloned by resources/js/admin/media-picker.js) --}}
    <template id="media-picker-template">
        <dialog class="m-auto h-[min(720px,calc(100%-2rem))] w-[calc(100%-2rem)] max-w-4xl flex-col rounded-2xl border border-line bg-surface-raised p-0 text-fg shadow-2xl backdrop:bg-slate-950/50 open:flex"
                data-url="{{ route('admin.media.picker') }}" data-upload-url="{{ route('admin.media.store') }}" aria-label="Media library">
            <div class="flex flex-wrap items-center gap-3 border-b border-line px-5 py-4">
                <h2 class="text-base font-semibold">Media library</h2>
                <div class="relative ml-auto w-full sm:w-64">
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-fg-muted" />
                    <input type="search" data-picker-search placeholder="Search images…" class="form-control py-2 pl-9" aria-label="Search images">
                </div>
                <label class="btn btn-secondary btn-sm cursor-pointer" data-picker-upload-label>
                    <x-icon name="upload" class="size-4" /> Upload
                    <input type="file" data-picker-upload multiple accept="image/*" class="sr-only">
                </label>
                <button type="button" class="btn-icon" data-modal-close aria-label="Close"><x-icon name="x" /></button>
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                <div data-picker-grid class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6"></div>
                <p data-picker-empty hidden class="py-16 text-center text-sm text-fg-muted">No images found. Upload one to get started.</p>
                <div class="mt-5 text-center"><button type="button" data-picker-more hidden class="btn btn-secondary btn-sm">Load more</button></div>
            </div>
            <div class="flex justify-end gap-2 border-t border-line px-5 py-3">
                <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                <button type="button" class="btn btn-primary" data-picker-confirm hidden disabled>Select images</button>
            </div>
        </dialog>
    </template>

    @foreach (['success', 'error'] as $type)
        @if (session($type))<span hidden data-flash="{{ $type }}" data-message="{{ session($type) }}"></span>@endif
    @endforeach
    @stack('scripts')
</body>
</html>
