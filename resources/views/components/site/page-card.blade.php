@props(['page', 'showDate' => false, 'excerpt' => true, 'showAuthor' => true])
{{-- Blog posts show their author when the listing eager-loaded it (author.avatar). --}}
@php($author = $showAuthor && $page->type === 'post' && $page->relationLoaded('author') ? $page->author : null)
<article {{ $attributes->merge(['class' => 'card-premium card-hover group relative flex flex-col overflow-hidden']) }}>
    <div class="relative aspect-[16/10] overflow-hidden bg-soft">
        @if ($page->featuredImage)
            <img src="{{ $page->featuredImage->url }}" {{ $page->featuredImage->responsive('(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw') }} alt="{{ $page->featuredImage->alt ?: $page->title }}" loading="lazy"
                 width="{{ $page->featuredImage->width }}" height="{{ $page->featuredImage->height }}"
                 class="size-full object-cover transition duration-700 group-hover:scale-110">
            <div class="absolute inset-0 bg-gradient-to-t from-secondary/40 via-transparent to-transparent opacity-0 transition duration-500 group-hover:opacity-100"></div>
        @else
            <div class="flex size-full items-center justify-center bg-gradient-to-br from-primary-soft to-accent-soft text-primary"><x-icon name="photo" class="size-10 opacity-50" /></div>
        @endif
        @if ($showDate && $page->published_at)
            <time datetime="{{ $page->published_at->toDateString() }}" class="absolute top-4 left-4 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-secondary shadow-sm">
                <x-icon name="calendar" class="size-3.5 text-accent" />{{ local_date($page->published_at) }}
            </time>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-6">
        <h3 class="text-lg leading-snug font-semibold text-secondary transition group-hover:text-primary">
            <a href="{{ $page->url() }}" class="after:absolute after:inset-0 focus:outline-none">{{ $page->title }}</a>
        </h3>
        @if ($excerpt)
            <p class="mt-2.5 line-clamp-3 text-sm leading-6 text-muted">{{ $page->summary(24) }}</p>
        @endif
        <span class="mt-auto flex items-center justify-between gap-3 pt-5 text-sm font-semibold text-primary">
            @if ($author)
                {{-- Sits above the card's stretched link so the author link stays clickable. --}}
                <a href="{{ $author->url() }}" class="relative z-10 flex min-w-0 items-center gap-2 text-secondary hover:text-primary" rel="author">
                    <x-site.author-avatar :author="$author" size="size-8" text="text-[11px]" />
                    <span class="truncate">{{ $author->name }}</span>
                </a>
            @else
                Read more
            @endif
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft transition group-hover:bg-accent group-hover:text-on-accent"><x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-0.5" /></span>
        </span>
    </div>
</article>
