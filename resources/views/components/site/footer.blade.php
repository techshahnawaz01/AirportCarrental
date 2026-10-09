@php
    $menus = app(\App\Services\MenuService::class);
    $columns = $menus->items('footer');
    $legal = $menus->items('legal');
    $s = settings();
    $phone = site_phone();
    $email = $s->get('contact.contact_email') ?: $s->get('general.site_email');
    $address = $s->get('contact.business_address') ?: $s->get('general.address');
@endphp
<footer class="mt-24">
    {{-- Newsletter band, lifted over the footer --}}
    <div class="container-site relative z-10">
        <div class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-primary-dark via-primary to-primary-light px-6 py-10 text-white shadow-float sm:px-10 lg:flex lg:items-center lg:justify-between lg:gap-10 lg:px-14 lg:py-12">
            <div class="hero-blob -top-20 -right-10 -z-10 size-72 bg-accent/30"></div>
            <div class="hero-blob -bottom-24 left-10 -z-10 size-72 bg-white/10"></div>
            <div class="max-w-xl">
                <p class="eyebrow-light"><x-icon name="mail" class="size-3.5 text-accent" /> Newsletter</p>
                <p class="mt-4 font-display text-2xl font-bold tracking-tight text-balance sm:text-3xl">Stay updated</p>
                <p class="mt-2 text-white/80">Travel tips, airport news and updates, straight to your inbox.</p>
            </div>
            <form action="{{ route('subscribers.store') }}" method="POST" data-ajax-form data-reset class="mt-6 w-full lg:mt-0 lg:max-w-md">
                @csrf
                <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                <label for="footer-email" class="sr-only">Email address</label>
                <div class="flex flex-col gap-2 rounded-2xl bg-white p-2 shadow-card sm:flex-row">
                    <div class="relative min-w-0 flex-1">
                        <x-icon name="mail" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-slate-400" />
                        <input id="footer-email" type="email" name="email" required placeholder="you@example.com" autocomplete="email"
                               class="w-full rounded-xl border-0 bg-transparent py-3 pr-3 pl-11 text-sm text-secondary placeholder:text-slate-400 focus:ring-0">
                    </div>
                    <button class="btn btn-accent py-3" data-loading-text="Subscribing…">Subscribe <x-icon name="arrow-right" class="size-4" /></button>
                </div>
                <p class="form-error rounded-lg bg-white px-3 py-1" data-error-for="email" hidden></p>
            </form>
        </div>
    </div>

    <div class="-mt-20 bg-secondary pt-20 text-slate-300">
        <div class="container-site grid gap-10 py-16 sm:grid-cols-2 lg:grid-cols-12">
            <div class="sm:col-span-2 lg:col-span-4">
                {{-- The main logo is inverted to white when no dedicated footer logo is uploaded. --}}
                <x-site.logo setting="branding.footer_logo" fallback="branding.logo" :light="true" :img-class="$s->get('branding.footer_logo') ? 'h-12 w-auto' : 'h-12 w-auto brightness-0 invert'" />
                @if ($tagline = $s->get('branding.tagline'))
                    <p class="mt-5 max-w-sm text-sm leading-6 text-slate-400">{{ $tagline }}</p>
                @endif
                <ul class="mt-6 space-y-3 text-sm">
                    @if ($phone)<li><a href="{{ tel_href($phone) }}" class="group flex items-center gap-3 transition hover:text-white"><span class="flex size-9 items-center justify-center rounded-xl bg-white/5 text-accent ring-1 ring-white/10 transition group-hover:bg-accent group-hover:text-on-accent"><x-icon name="phone" class="size-4" /></span><span class="font-semibold">{{ $phone }}</span></a></li>@endif
                    @if ($email)<li><a href="mailto:{{ $email }}" class="group flex items-center gap-3 transition hover:text-white"><span class="flex size-9 items-center justify-center rounded-xl bg-white/5 text-accent ring-1 ring-white/10 transition group-hover:bg-accent group-hover:text-on-accent"><x-icon name="mail" class="size-4" /></span><span class="break-all">{{ $email }}</span></a></li>@endif
                    @if ($address)<li class="flex items-start gap-3"><span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white/5 text-accent ring-1 ring-white/10"><x-icon name="map-pin" class="size-4" /></span><span class="pt-2">{{ $address }}</span></li>@endif
                </ul>
                <x-site.social-links class="mt-7" />
            </div>

            @foreach ($columns as $column)
                <div class="lg:col-span-2">
                    <h4 class="text-sm font-semibold tracking-wider text-white uppercase">{{ $column->label }}</h4>
                    <span class="mt-3 block h-0.5 w-8 rounded-full bg-accent"></span>
                    <ul class="mt-5 space-y-3 text-sm">
                        @foreach ($column->children as $link)
                            <li><a href="{{ $link->href() ?? '#' }}" class="group inline-flex items-start gap-1.5 transition hover:text-white" @if ($link->open_in_new_tab) target="_blank" rel="noopener" @endif><x-icon name="chevron-right" class="mt-1 size-3 shrink-0 text-accent/70 transition group-hover:translate-x-0.5 group-hover:text-accent" />{{ $link->label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="border-t border-white/10">
            <div class="container-site flex flex-col gap-4 py-6 text-xs text-slate-400 md:flex-row md:items-center md:justify-between">
                <p>{{ $s->copyright() }}</p>
                @if ($legal->isNotEmpty())
                    <ul class="flex flex-wrap gap-x-5 gap-y-2">
                        @foreach ($legal as $link)
                            <li><a href="{{ $link->href() ?? '#' }}" class="transition hover:text-accent">{{ $link->label }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @if ($disclaimer = $s->get('general.disclaimer'))
                <div class="container-site pb-8">
                    <p class="flex gap-3 rounded-2xl bg-white/5 p-4 text-xs leading-5 text-slate-400 ring-1 ring-white/10"><x-icon name="info" class="size-4 shrink-0 text-slate-500" />{{ $disclaimer }}</p>
                </div>
            @endif
        </div>
    </div>
</footer>
