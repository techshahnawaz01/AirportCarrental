<section class="not-prose" data-reveal>
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <span class="eyebrow"><x-icon name="newspaper" class="size-3.5" /> Travel journal</span>
            <h3 class="section-title mt-4">{{ $title }}</h3>
        </div>
        @if ($moreUrl)<a href="{{ $moreUrl }}" class="btn btn-secondary">View all <x-icon name="arrow-right" class="size-4" /></a>@endif
    </div>
    <div class="mt-10 grid gap-6 lg:grid-cols-2">
        @php($lead = $posts->first())
        <article class="group relative min-h-80 overflow-hidden rounded-3xl bg-secondary shadow-card transition hover:shadow-card-hover">
            @if ($lead->featuredImage)
                <img src="{{ $lead->featuredImage->url }}" {{ $lead->featuredImage->responsive('(min-width: 1024px) 600px, 100vw') }} alt="{{ $lead->featuredImage->alt ?: $lead->title }}" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-secondary via-secondary/50 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-6 text-white sm:p-8">
                @if ($lead->published_at)<time class="inline-flex items-center gap-1.5 rounded-full bg-accent px-3 py-1 text-xs font-semibold text-on-accent">{{ local_date($lead->published_at) }}</time>@endif
                <h4 class="mt-4 text-2xl leading-snug font-bold text-balance sm:text-3xl"><a href="{{ $lead->url() }}" class="after:absolute after:inset-0">{{ $lead->title }}</a></h4>
                <p class="mt-3 line-clamp-2 text-sm text-white/80 sm:text-base">{{ $lead->summary(30) }}</p>
                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-3">
                    @if ($lead->author)
                        <a href="{{ $lead->author->url() }}" class="relative z-10 inline-flex items-center gap-2 text-sm font-semibold text-white hover:text-accent" rel="author">
                            <x-site.author-avatar :author="$lead->author" size="size-8" text="text-[11px]" class="ring-white/30" />
                            {{ $lead->author->name }}
                        </a>
                    @endif
                    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-accent">Read article <x-icon name="arrow-right" class="size-4 transition group-hover:translate-x-1" /></span>
                </div>
            </div>
        </article>
        <div class="grid gap-4">
            @foreach ($posts->skip(1) as $post)
                <article class="card-premium card-hover group relative flex gap-4 p-3">
                    <div class="aspect-square w-24 shrink-0 overflow-hidden rounded-2xl bg-soft sm:w-32">
                        @if ($post->featuredImage)
                            <img src="{{ $post->featuredImage->url }}" {{ $post->featuredImage->responsive('128px') }} alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-110">
                        @endif
                    </div>
                    <div class="flex min-w-0 flex-col justify-center py-1 pr-2">
                        @if ($post->published_at)<time class="flex items-center gap-1.5 text-xs font-semibold text-accent-ink"><x-icon name="calendar" class="size-3.5" />{{ local_date($post->published_at) }}</time>@endif
                        <h4 class="mt-1.5 line-clamp-2 font-semibold text-secondary transition group-hover:text-primary"><a href="{{ $post->url() }}" class="after:absolute after:inset-0">{{ $post->title }}</a></h4>
                        <p class="mt-1 line-clamp-2 text-sm text-muted">{{ $post->summary(14) }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
