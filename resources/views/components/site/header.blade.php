@php
    $items = app(\App\Services\MenuService::class)->items('header');
    $phone = settings('contact.phone');
@endphp
<header data-site-header class="sticky top-0 z-40 border-b border-line/70 bg-white/95 backdrop-blur transition-shadow supports-[backdrop-filter]:bg-white/80">
    <div class="container-site flex h-16 items-center gap-4 lg:h-20">
        <x-site.logo img-class="h-9 w-auto lg:h-11" />

        <nav aria-label="Main" class="ml-auto hidden lg:block">
            <ul class="flex items-center gap-0.5">
                @foreach ($items as $item)
                    @if ($item->children->isNotEmpty())
                        <li class="relative" data-dropdown>
                            <button type="button" data-dropdown-button aria-expanded="false" class="flex items-center gap-1 rounded-lg px-2.5 py-2 text-sm font-medium whitespace-nowrap text-slate-700 transition hover:bg-slate-100 hover:text-primary">
                                {{ $item->label }} <x-icon name="chevron-down" class="size-4 opacity-60" />
                            </button>
                            <div data-dropdown-menu hidden class="absolute top-full left-0 pt-2">
                                <ul class="w-64 rounded-xl border border-line bg-white p-2 shadow-xl ring-1 ring-black/5">
                                    @foreach ($item->children as $child)
                                        <li>
                                            <a href="{{ $child->href() ?? '#' }}" @if ($child->open_in_new_tab) target="_blank" rel="noopener" @endif
                                               @class(['flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition hover:bg-slate-50 hover:text-primary', 'bg-primary/5 font-semibold text-primary' => $child->isCurrent(), 'text-slate-700' => ! $child->isCurrent()])>
                                                @if ($child->icon)<x-icon :name="$child->icon" class="size-4 text-primary" />@endif
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
                               @class(['rounded-lg px-2.5 py-2 text-sm font-medium whitespace-nowrap transition hover:bg-slate-100 hover:text-primary', 'text-primary' => $item->isCurrent(), 'text-slate-700' => ! $item->isCurrent()])
                               @if ($item->isCurrent()) aria-current="page" @endif>{{ $item->label }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>

        <div class="ml-auto flex items-center gap-1 lg:ml-2">
            <a href="{{ route('search') }}" class="btn-icon text-slate-600 hover:text-primary" aria-label="Search"><x-icon name="search" /></a>
            @if ($phone)
                <a href="{{ tel_href($phone) }}" class="btn btn-primary hidden 2xl:inline-flex"><x-icon name="phone" class="size-4" /> {{ $phone }}</a>
            @endif
            <button type="button" class="btn-icon text-slate-700 lg:hidden" data-nav-toggle aria-controls="mobile-nav" aria-expanded="false" aria-label="Open menu">
                <x-icon name="menu" class="size-6" />
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div id="mobile-nav" hidden class="fixed inset-0 z-50 bg-slate-950/40 backdrop-blur-sm lg:hidden">
        <div class="ml-auto flex h-full w-full max-w-sm flex-col overflow-y-auto bg-white shadow-2xl">
            <div class="flex h-16 items-center justify-between border-b border-line px-4">
                <x-site.logo img-class="h-8 w-auto" />
                <button type="button" class="btn-icon" data-nav-toggle aria-label="Close menu"><x-icon name="x" class="size-6" /></button>
            </div>
            <form action="{{ route('search') }}" class="border-b border-line p-4" role="search">
                <label for="mobile-search" class="sr-only">Search</label>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                    <input id="mobile-search" type="search" name="q" placeholder="Search the site…" class="form-control pl-9">
                </div>
            </form>
            <nav aria-label="Mobile" class="flex-1 p-2">
                <ul class="space-y-1">
                    @foreach ($items as $item)
                        <li>
                            @if ($item->children->isNotEmpty())
                                <details class="group">
                                    <summary class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-3 font-medium text-slate-800 hover:bg-slate-50">
                                        {{ $item->label }} <x-icon name="chevron-down" class="size-4 transition group-open:rotate-180" />
                                    </summary>
                                    <ul class="mb-2 ml-3 space-y-0.5 border-l border-line pl-3">
                                        @foreach ($item->children as $child)
                                            <li><a href="{{ $child->href() ?? '#' }}" class="block rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-primary">{{ $child->label }}</a></li>
                                        @endforeach
                                    </ul>
                                </details>
                            @else
                                <a href="{{ $item->href() ?? '#' }}" class="block rounded-lg px-3 py-3 font-medium text-slate-800 hover:bg-slate-50">{{ $item->label }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
            @if ($phone)
                <div class="border-t border-line p-4">
                    <a href="{{ tel_href($phone) }}" class="btn btn-primary w-full"><x-icon name="phone" class="size-4" /> Call {{ $phone }}</a>
                </div>
            @endif
        </div>
    </div>
</header>
