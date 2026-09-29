@props(['page', 'showDate' => false, 'excerpt' => true])
<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg']) }}>
    <div class="aspect-[16/10] overflow-hidden bg-slate-100">
        @if ($page->featuredImage)
            <img src="{{ $page->featuredImage->url }}" alt="{{ $page->featuredImage->alt ?: $page->title }}" loading="lazy"
                 width="{{ $page->featuredImage->width }}" height="{{ $page->featuredImage->height }}"
                 class="size-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex size-full items-center justify-center bg-gradient-to-br from-primary/15 to-accent/15 text-primary"><x-icon name="photo" class="size-10 opacity-50" /></div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5">
        @if ($showDate && $page->published_at)
            <time datetime="{{ $page->published_at->toDateString() }}" class="text-xs font-medium tracking-wide text-primary uppercase">{{ local_date($page->published_at) }}</time>
        @endif
        <h3 class="mt-1 text-lg leading-snug font-semibold text-secondary">
            <a href="{{ $page->url() }}" class="after:absolute after:inset-0 focus:outline-none">{{ $page->title }}</a>
        </h3>
        @if ($excerpt)
            <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $page->summary(24) }}</p>
        @endif
        <span class="mt-auto inline-flex items-center gap-1 pt-4 text-sm font-semibold text-primary">Read more <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></span>
    </div>
</article>
