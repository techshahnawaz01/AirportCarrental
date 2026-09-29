<section class="not-prose grid items-center gap-8 lg:grid-cols-2 lg:gap-14">
    @if ($image)
        <div @class(['overflow-hidden rounded-3xl shadow-xl', 'lg:order-last' => $reverse])>
            <img src="{{ $image->url }}" alt="{{ $image->alt }}" width="{{ $image->width }}" height="{{ $image->height }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
        </div>
    @endif
    <div @class(['lg:col-span-2' => ! $image])>
        @if ($title)
            <{{ $heading }} class="text-3xl font-extrabold tracking-tight text-balance text-secondary sm:text-4xl">{{ $title }}</{{ $heading }}>
        @endif
        <div class="rich-text mt-5">{!! $content !!}</div>
    </div>
</section>
