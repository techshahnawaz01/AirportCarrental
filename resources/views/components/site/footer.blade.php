@php
    $menus = app(\App\Services\MenuService::class);
    $columns = $menus->items('footer');
    $legal = $menus->items('legal');
    $s = settings();
    $phone = $s->get('contact.phone') ?: $s->get('general.phone');
    $email = $s->get('contact.contact_email') ?: $s->get('general.site_email');
    $address = $s->get('contact.business_address') ?: $s->get('general.address');
@endphp
<footer class="mt-16 bg-secondary text-slate-300">
    <div class="container-site grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-12">
        <div class="sm:col-span-2 lg:col-span-4">
            {{-- The main logo is inverted to white when no dedicated footer logo is uploaded. --}}
            <x-site.logo setting="branding.footer_logo" fallback="branding.logo" :img-class="$s->get('branding.footer_logo') ? 'h-11 w-auto' : 'h-11 w-auto brightness-0 invert'" />
            @if ($tagline = $s->get('branding.tagline'))
                <p class="mt-4 max-w-sm text-sm leading-6 text-slate-400">{{ $tagline }}</p>
            @endif
            <ul class="mt-6 space-y-2.5 text-sm">
                @if ($phone)<li class="flex items-center gap-2.5"><x-icon name="phone" class="size-4 text-accent" /><a href="{{ tel_href($phone) }}" class="hover:text-white">{{ $phone }}</a></li>@endif
                @if ($email)<li class="flex items-center gap-2.5"><x-icon name="mail" class="size-4 text-accent" /><a href="mailto:{{ $email }}" class="hover:text-white">{{ $email }}</a></li>@endif
                @if ($address)<li class="flex items-start gap-2.5"><x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-accent" /><span>{{ $address }}</span></li>@endif
            </ul>
            <x-site.social-links class="mt-6" />
        </div>

        @foreach ($columns as $column)
            <div class="lg:col-span-2">
                <h2 class="text-sm font-semibold tracking-wide text-white uppercase">{{ $column->label }}</h2>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach ($column->children as $link)
                        <li><a href="{{ $link->href() ?? '#' }}" class="transition hover:text-white" @if ($link->open_in_new_tab) target="_blank" rel="noopener" @endif>{{ $link->label }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div class="sm:col-span-2 lg:col-span-2 {{ $columns->count() < 3 ? 'lg:col-span-4' : '' }}">
            <h2 class="text-sm font-semibold tracking-wide text-white uppercase">Stay updated</h2>
            <p class="mt-4 text-sm text-slate-400">Travel tips and updates, straight to your inbox.</p>
            <form action="{{ route('subscribers.store') }}" method="POST" data-ajax-form data-reset class="mt-4">
                @csrf
                <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                <label for="footer-email" class="sr-only">Email address</label>
                <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                    <input id="footer-email" type="email" name="email" required placeholder="you@example.com" autocomplete="email"
                           class="min-w-0 flex-1 rounded-lg border-white/10 bg-white/5 text-sm text-white placeholder:text-slate-500 focus:border-accent focus:ring-accent/30">
                    <button class="btn btn-accent" data-loading-text="Subscribing…">Subscribe</button>
                </div>
                <p class="form-error" data-error-for="email" hidden></p>
            </form>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container-site flex flex-col gap-4 py-6 text-xs text-slate-400 md:flex-row md:items-center md:justify-between">
            <p>{{ $s->copyright() }}</p>
            @if ($legal->isNotEmpty())
                <ul class="flex flex-wrap gap-x-5 gap-y-2">
                    @foreach ($legal as $link)
                        <li><a href="{{ $link->href() ?? '#' }}" class="hover:text-white">{{ $link->label }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
        @if ($disclaimer = $s->get('general.disclaimer'))
            <div class="container-site pb-8">
                <p class="rounded-lg bg-white/5 p-4 text-xs leading-5 text-slate-400">{{ $disclaimer }}</p>
            </div>
        @endif
    </div>
</footer>
