<div class="space-y-6 lg:sticky lg:top-28">
    @if ($phone = site_phone())
        <div class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-primary via-primary-dark to-secondary p-7 text-white shadow-float">
            <div class="hero-blob -top-16 -right-16 -z-10 size-48 bg-accent/30"></div>
            <span class="flex size-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25"><x-icon name="phone" class="size-6 text-accent" /></span>
            <p class="mt-5 font-display text-xl font-bold">Need help with your trip?</p>
            <p class="mt-2 text-sm leading-6 text-white/80">Talk to a travel specialist about bookings, changes and cancellations.</p>
            <a href="{{ tel_href($phone) }}" class="btn btn-accent btn-lg mt-6 w-full"><x-icon name="phone" class="size-4" /> {{ $phone }}</a>
            @if ($label = settings('contact.phone_label'))<p class="mt-2.5 text-center text-xs text-white/70">{{ $label }}</p>@endif
        </div>
    @endif

    @if (! empty($related) && $related->isNotEmpty())
        <nav aria-labelledby="related-heading" class="card-premium p-6">
            <h2 id="related-heading" class="flex items-center gap-2 text-sm font-bold tracking-wider text-secondary uppercase"><span class="h-4 w-1 rounded-full bg-accent"></span>{{ $page->type === 'post' ? 'Latest posts' : 'Explore more' }}</h2>
            <ul class="mt-5 space-y-3">
                @foreach ($related as $item)
                    <li>
                        <a href="{{ $item->url() }}" class="group -mx-2 flex items-center gap-3 rounded-2xl p-2 transition hover:bg-soft">
                            <span class="size-16 shrink-0 overflow-hidden rounded-xl bg-soft">
                                @if ($item->featuredImage)
                                    <img src="{{ $item->featuredImage->url }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-110">
                                @else
                                    <span class="flex size-full items-center justify-center text-primary"><x-icon name="document" class="size-6 opacity-50" /></span>
                                @endif
                            </span>
                            <span class="text-sm leading-snug font-semibold text-secondary transition group-hover:text-primary">{{ $item->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif
</div>
