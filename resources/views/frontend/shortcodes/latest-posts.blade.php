<section class="not-prose">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <h2 class="text-2xl font-bold tracking-tight text-secondary sm:text-3xl">{{ $title }}</h2>
        @if ($moreUrl)<a href="{{ $moreUrl }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline">View all <x-icon name="arrow-right" class="size-4" /></a>@endif
    </div>
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        @php($lead = $posts->first())
        <article class="group relative overflow-hidden rounded-2xl bg-secondary shadow-sm">
            @if ($lead->featuredImage)
                <img src="{{ $lead->featuredImage->url }}" alt="{{ $lead->featuredImage->alt ?: $lead->title }}" loading="lazy" class="aspect-[4/3] size-full object-cover opacity-70 transition duration-500 group-hover:scale-105 lg:aspect-auto lg:h-full">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-secondary via-secondary/40 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-6 text-white">
                @if ($lead->published_at)<time class="text-xs font-semibold tracking-wide text-accent uppercase">{{ local_date($lead->published_at) }}</time>@endif
                <h3 class="mt-2 text-xl font-bold sm:text-2xl"><a href="{{ $lead->url() }}" class="after:absolute after:inset-0">{{ $lead->title }}</a></h3>
                <p class="mt-2 line-clamp-2 text-sm text-white/80">{{ $lead->summary(30) }}</p>
            </div>
        </article>
        <div class="grid gap-4">
            @foreach ($posts->skip(1) as $post)
                <article class="group relative flex gap-4 rounded-2xl border border-line bg-white p-3 transition hover:shadow-md">
                    <div class="aspect-square w-28 shrink-0 overflow-hidden rounded-xl bg-slate-100 sm:w-36">
                        @if ($post->featuredImage)
                            <img src="{{ $post->featuredImage->url }}" alt="" loading="lazy" class="size-full object-cover transition group-hover:scale-105">
                        @endif
                    </div>
                    <div class="min-w-0 py-1">
                        @if ($post->published_at)<time class="text-xs font-semibold text-primary">{{ local_date($post->published_at) }}</time>@endif
                        <h3 class="mt-1 line-clamp-2 font-semibold text-secondary group-hover:text-primary"><a href="{{ $post->url() }}" class="after:absolute after:inset-0">{{ $post->title }}</a></h3>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $post->summary(14) }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
