<article class="not-prose my-6 grid overflow-hidden rounded-2xl border border-line bg-white shadow-sm sm:grid-cols-5">
    @if ($image)
        <img src="{{ $image->url }}" alt="{{ $image->alt ?: $title }}" loading="lazy" class="aspect-video size-full object-cover sm:col-span-2 sm:aspect-auto">
    @endif
    <div @class(['flex flex-col p-5 sm:p-6', 'sm:col-span-3' => $image, 'sm:col-span-5' => ! $image])>
        <h3 class="text-xl font-bold text-secondary">{{ $title }}</h3>
        <div class="rich-text mt-2 text-sm">{!! $content !!}</div>
        @if ($button && $url)
            <a href="{{ $url }}" class="btn btn-primary mt-5 self-start">{{ $button }}</a>
        @endif
    </div>
</article>
