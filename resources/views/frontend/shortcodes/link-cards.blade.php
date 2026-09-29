<section class="not-prose">
    @if ($title)
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold tracking-tight text-secondary sm:text-3xl">{{ $title }}</h2>
            @if ($subtitle)<p class="mt-2 text-slate-600">{{ $subtitle }}</p>@endif
        </div>
    @endif

    @if ($style === 'icons')
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item->href() ?? '#' }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif
                       class="group flex h-full flex-col items-center gap-3 rounded-2xl border border-line bg-white p-5 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md">
                        <span class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-white">
                            <x-icon :name="$item->icon ?: 'arrow-right'" class="size-6" />
                        </span>
                        <span class="text-sm font-semibold text-secondary">{{ $item->label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <ul class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item->href() ?? '#' }}" @if ($item->open_in_new_tab) target="_blank" rel="noopener" @endif class="group relative block aspect-[4/5] overflow-hidden rounded-2xl bg-secondary shadow-sm sm:aspect-[4/3]">
                        @if ($item->image)
                            <img src="{{ $item->image->url }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-500 group-hover:scale-105">
                        @endif
                        <span class="absolute inset-0 bg-gradient-to-t from-secondary/90 via-secondary/30 to-transparent"></span>
                        <span class="absolute inset-x-0 bottom-0 p-4">
                            <span class="flex items-center justify-between gap-2 font-semibold text-white">
                                {{ $item->label }} <x-icon name="arrow-right" class="size-4 shrink-0 transition group-hover:translate-x-1" />
                            </span>
                            @if ($item->description)<span class="mt-1 hidden text-xs text-white/75 sm:line-clamp-2">{{ $item->description }}</span>@endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
