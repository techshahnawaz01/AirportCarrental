@php
    $items = app(\App\Services\MenuService::class)->items('header');
    $phone = site_phone();
    $phoneLabel = settings('contact.phone_label');
    $email = settings('contact.contact_email') ?: settings('general.site_email');
    $topBarText = trim((string) settings('branding.top_bar_text'));
    $hasSocial = collect(['facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'whatsapp'])->contains(fn ($network) => filled(settings("social.{$network}")));
@endphp
{{-- Utility bar: text from Settings → Branding → Top bar text; hidden when there is nothing to show. --}}
@if ($topBarText || $email || $phone || $hasSocial)
<div class="hidden bg-secondary text-xs text-white/80 md:block">
    <div class="container-site flex h-10 items-center justify-between gap-6">
        <p class="flex min-w-0 items-center gap-2">
            @if ($topBarText)
                <x-icon name="shield" class="size-4 shrink-0 text-accent" />
                <span class="truncate">{{ $topBarText }}</span>
            @endif
        </p>
        <div class="flex shrink-0 items-center gap-5">
            @if ($email)
                <a href="mailto:{{ $email }}" class="hidden items-center gap-1.5 transition hover:text-accent lg:flex"><x-icon name="mail" class="size-3.5 text-primary-light" /> {{ $email }}</a>
            @endif
            @if ($phone)
                <a href="{{ tel_href($phone) }}" class="flex items-center gap-1.5 font-semibold text-white transition hover:text-accent"><x-icon name="phone" class="size-3.5 text-primary-light" /> {{ $phone }}</a>
            @endif
            <x-site.social-links class="hidden gap-1 lg:flex" size="sm" />
        </div>
    </div>
</div>
@endif

<header data-site-header class="sticky top-0 z-40 border-b border-line/80 bg-white transition-shadow">
    <div class="container-site flex h-[72px] items-center gap-4 lg:h-20">
        <x-site.logo img-class="h-9 w-auto transition-transform duration-300 group-hover:scale-105 lg:h-12" class="group" />

        <nav aria-label="Main" class="ml-auto hidden lg:block">
            <ul class="flex items-center gap-1">
                @foreach ($items as $item)
                    @if ($item->children->isNotEmpty())
                        <li class="relative" data-dropdown>
                            <button type="button" data-dropdown-button aria-expanded="false" @class(['group flex items-center gap-1 rounded-xl px-3 py-2 text-sm font-semibold whitespace-nowrap transition hover:bg-primary-soft hover:text-primary aria-expanded:bg-primary-soft aria-expanded:text-primary', 'text-primary' => $item->children->contains(fn ($c) => $c->isCurrent()), 'text-slate-700' => ! $item->children->contains(fn ($c) => $c->isCurrent())])>
                                {{ $item->label }} <x-icon name="chevron-down" class="size-4 opacity-60 transition group-aria-expanded:rotate-180" />
                            </button>
                            <div data-dropdown-menu hidden class="absolute top-full left-1/2 -translate-x-1/2 pt-3">
                                <ul class="w-72 rounded-2xl border border-line bg-white p-2 shadow-float ring-1 ring-black/5">
                                    @foreach ($item->children as $child)
                                        <li>
                                            <a href="{{ $child->href() ?? '#' }}" @if ($child->open_in_new_tab) target="_blank" rel="noopener" @endif
                                               @class(['nav-dropdown-link group/item', 'is-current' => $child->isCurrent()])>
                                                <span class="nav-dropdown-icon">
                                                    <x-icon :name="$child->icon ?: 'arrow-right'" class="size-4" />
                                                </span>
                                                {{ $child->label }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @else
                        <li>
                            <a href="{{ $item->href() ?? '#' }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                               @class(['block rounded-xl px-3 py-2 text-sm font-semibold whitespace-nowrap transition hover:bg-primary-soft hover:text-primary', 'bg-primary-soft text-primary' => $item->isCurrent(), 'text-slate-700' => ! $item->isCurrent()])
                               @if ($item->isCurrent()) aria-current="page" @endif>{{ $item->label }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>

        <div class="ml-auto flex items-center gap-2 lg:ml-3">
            <a href="{{ route('search') }}" class="btn-icon border border-line text-slate-600 hover:border-primary/30 hover:bg-primary-soft hover:text-primary" aria-label="Search"><x-icon name="search" /></a>
            @if ($phone)
                <a href="{{ tel_href($phone) }}" class="hidden items-center gap-3 rounded-2xl border border-accent/25 bg-accent-soft py-1.5 pr-4 pl-1.5 shadow-sm transition hover:-translate-y-px hover:shadow-glow-accent xl:flex">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-accent to-accent-dark text-on-accent shadow-glow-accent"><x-icon name="phone" class="size-4" /></span>
                    <span class="leading-tight whitespace-nowrap">
                        <span class="block text-[10px] font-bold tracking-wider text-accent-ink uppercase">Call now @if ($phoneLabel)· {{ $phoneLabel }}@endif</span>
                        <span class="block text-sm font-bold text-secondary">{{ $phone }}</span>
                    </span>
                </a>
                <a href="{{ tel_href($phone) }}" class="btn-icon bg-gradient-to-br from-accent to-accent-dark text-on-accent shadow-glow-accent hover:text-white lg:hidden" aria-label="Call {{ $phone }}"><x-icon name="phone" class="size-5" /></a>
            @endif
            <button type="button" class="btn-icon border border-line text-slate-700 lg:hidden" data-nav-toggle aria-controls="mobile-nav" aria-expanded="false" aria-label="Open menu">
                <x-icon name="menu" class="size-6" />
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div id="mobile-nav" hidden class="fixed inset-0 z-50 bg-secondary/50 backdrop-blur-sm lg:hidden">
        <div class="ml-auto flex h-full w-full max-w-sm flex-col overflow-y-auto bg-white shadow-2xl">
            <div class="flex h-[72px] items-center justify-between border-b border-line px-4">
                <x-site.logo img-class="h-8 w-auto" />
                <button type="button" class="btn-icon border border-line" data-nav-toggle aria-label="Close menu"><x-icon name="x" class="size-6" /></button>
            </div>
            <form action="{{ route('search') }}" class="border-b border-line p-4" role="search">
                <label for="mobile-search" class="sr-only">Search</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400" />
                    <input id="mobile-search" type="search" name="q" placeholder="Search the site…" class="form-control bg-soft pl-10">
                </div>
            </form>
            <nav aria-label="Mobile" class="flex-1 p-3">
                <ul class="space-y-1">
                    @foreach ($items as $item)
                        <li>
                            @if ($item->children->isNotEmpty())
                                <details class="group">
                                    <summary class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 font-semibold text-slate-800 transition hover:bg-primary-soft hover:text-primary">
                                        {{ $item->label }} <x-icon name="chevron-down" class="size-4 transition group-open:rotate-180" />
                                    </summary>
                                    <ul class="mb-2 ml-4 space-y-0.5 border-l-2 border-primary/15 pl-3">
                                        @foreach ($item->children as $child)
                                            <li><a href="{{ $child->href() ?? '#' }}" class="nav-mobile-link">@if ($child->icon)<x-icon :name="$child->icon" class="size-4 text-primary" />@endif{{ $child->label }}</a></li>
                                        @endforeach
                                    </ul>
                                </details>
                            @else
                                <a href="{{ $item->href() ?? '#' }}" class="block rounded-xl px-4 py-3 font-semibold text-slate-800 transition hover:translate-x-1 hover:bg-primary-soft hover:text-primary">{{ $item->label }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
            @if ($phone)
                <div class="border-t border-line bg-soft p-4">
                    <p class="text-center text-xs font-semibold tracking-wider text-muted uppercase">Talk to a travel expert</p>
                    <a href="{{ tel_href($phone) }}" class="btn btn-accent btn-lg mt-3 w-full"><x-icon name="phone" class="size-4" /> {{ $phone }}</a>
                </div>
            @endif
        </div>
    </div>
</header>
