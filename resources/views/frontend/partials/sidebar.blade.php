<div class="space-y-6 lg:sticky lg:top-28">
    @if ($phone = settings('contact.phone'))
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-secondary p-6 text-white shadow-lg">
            <p class="text-lg font-bold">Need help with your trip?</p>
            <p class="mt-1.5 text-sm text-white/80">Talk to a travel specialist about bookings, changes and cancellations.</p>
            <a href="{{ tel_href($phone) }}" class="btn btn-accent mt-5 w-full"><x-icon name="phone" class="size-4" /> {{ $phone }}</a>
            @if ($label = settings('contact.phone_label'))<p class="mt-2 text-center text-xs text-white/70">{{ $label }}</p>@endif
        </div>
    @endif

    @if (! empty($related) && $related->isNotEmpty())
        <nav aria-labelledby="related-heading" class="rounded-2xl border border-line bg-white p-5">
            <h2 id="related-heading" class="text-sm font-semibold tracking-wide text-slate-500 uppercase">{{ $page->type === 'post' ? 'Latest posts' : 'Explore more' }}</h2>
            <ul class="mt-4 space-y-4">
                @foreach ($related as $item)
                    <li>
                        <a href="{{ $item->url() }}" class="group flex items-center gap-3">
                            <span class="size-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                @if ($item->featuredImage)
                                    <img src="{{ $item->featuredImage->url }}" alt="" loading="lazy" class="size-full object-cover transition group-hover:scale-105">
                                @endif
                            </span>
                            <span class="text-sm leading-snug font-medium text-secondary group-hover:text-primary">{{ $item->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endif
</div>
