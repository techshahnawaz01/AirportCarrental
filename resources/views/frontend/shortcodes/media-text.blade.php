<section class="not-prose grid items-center gap-10 lg:grid-cols-2 lg:gap-16" data-reveal>
    @if ($image)
        <div @class(['relative', 'lg:order-last' => $reverse])>
            <div @class(['absolute -inset-3 -z-10 rounded-[2rem] bg-gradient-to-br from-primary-soft to-accent-soft sm:-inset-4', 'rotate-2' => ! $reverse, '-rotate-2' => $reverse])></div>
            <div class="overflow-hidden rounded-3xl shadow-float">
                <img src="{{ $image->url }}" {{ $image->responsive('(min-width: 1024px) 600px, 100vw') }} alt="{{ $image->alt }}" width="{{ $image->width }}" height="{{ $image->height }}" loading="lazy" class="aspect-[4/3] w-full object-cover transition duration-700 hover:scale-105">
            </div>
            <span @class(['absolute -bottom-5 hidden size-20 items-center justify-center rounded-3xl bg-gradient-to-br from-accent to-accent-dark text-on-accent shadow-glow-accent sm:flex', '-right-5' => ! $reverse, '-left-5' => $reverse])>
                <x-icon name="plane" class="size-9" />
            </span>
        </div>
    @endif
    <div @class(['lg:col-span-2' => ! $image])>
        @if ($title)
            <span class="block h-1 w-12 rounded-full bg-gradient-to-r from-accent to-primary"></span>
            <{{ $heading }} class="section-title mt-5">{{ $title }}</{{ $heading }}>
        @endif
        <div class="rich-text mt-6 text-[15px] sm:text-base">{!! $content !!}</div>
    </div>
</section>
