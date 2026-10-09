<article class="not-prose card-premium card-hover group my-8 grid overflow-hidden sm:grid-cols-5">
    @if ($image)
        <div class="overflow-hidden sm:col-span-2">
            <img src="{{ $image->url }}" {{ $image->responsive('(min-width: 640px) 400px, 100vw') }} alt="{{ $image->alt ?: $title }}" loading="lazy" class="aspect-video size-full object-cover transition duration-700 group-hover:scale-105 sm:aspect-auto">
        </div>
    @endif
    <div @class(['flex flex-col p-6 sm:p-8', 'sm:col-span-3' => $image, 'sm:col-span-5' => ! $image])>
        <h3 class="text-xl font-bold text-secondary">{{ $title }}</h3>
        <div class="rich-text mt-3 text-sm">{!! $content !!}</div>
        @if ($button && $url)
            <a href="{{ $url }}" class="btn btn-primary mt-6 self-start">{{ $button }} <x-icon name="arrow-right" class="size-4" /></a>
        @endif
    </div>
</article>
