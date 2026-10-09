<section class="not-prose" data-reveal>
    @if ($title)
        <div class="mx-auto mb-10 max-w-2xl text-center">
            <span class="eyebrow"><x-icon name="bolt" class="size-3.5" /> Explore</span>
            <h2 class="section-title mt-4">{{ $title }}</h2>
            @if ($subtitle)<p class="section-lead">{{ $subtitle }}</p>@endif
        </div>
    @endif

    @if ($style === 'icons')
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-6">
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item->href() ?? '#' }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                       class="card-premium card-hover group flex h-full flex-col items-center gap-3 p-5 text-center sm:p-6">
                        <span class="icon-tile size-14 transition duration-300 group-hover:scale-110 group-hover:from-primary group-hover:to-primary-dark group-hover:text-white group-hover:shadow-glow-primary">
                            <x-icon :name="$item->icon ?: 'arrow-right'" class="size-6" />
                        </span>
                        <span class="text-sm font-semibold text-secondary transition group-hover:text-primary">{{ $item->label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <ul class="grid grid-cols-2 gap-4 lg:grid-cols-4 lg:gap-6">
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item->href() ?? '#' }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif class="group relative block aspect-[4/5] overflow-hidden rounded-3xl bg-secondary shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-card-hover sm:aspect-[4/3.4]">
                        @if ($item->image)
                            <img src="{{ $item->image->url }}" {{ $item->image->responsive('(min-width: 1024px) 300px, 50vw') }} alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-110">
                        @endif
                        <span class="absolute inset-0 bg-gradient-to-t from-secondary via-secondary/30 to-transparent"></span>
                        @if ($item->icon)
                            <span class="absolute top-4 left-4 flex size-10 items-center justify-center rounded-xl bg-black/30 text-white ring-1 ring-white/25"><x-icon :name="$item->icon" class="size-5" /></span>
                        @endif
                        <span class="absolute top-4 right-4 flex size-9 translate-x-2 items-center justify-center rounded-full bg-accent text-on-accent opacity-0 shadow-glow-accent transition duration-300 group-hover:translate-x-0 group-hover:opacity-100"><x-icon name="arrow-right" class="size-4" /></span>
                        <span class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
                            <span class="block font-display text-base font-semibold text-white sm:text-lg">{{ $item->label }}</span>
                            @if ($item->description)<span class="mt-1 hidden text-xs leading-5 text-white/75 sm:line-clamp-2">{{ $item->description }}</span>@endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
