<section class="not-prose relative isolate overflow-hidden rounded-[2rem] bg-primary-dark px-6 py-16 text-center text-white shadow-float sm:px-12 lg:py-20" data-reveal>
    @if ($image)
        <img src="{{ $image }}" alt="" loading="lazy" class="absolute inset-0 -z-20 size-full object-cover">
    @endif
    <div class="hero-overlay absolute inset-0 -z-10"></div>
    <div class="hero-blob -top-20 -left-20 -z-10 size-72 bg-white/10"></div>
    <div class="hero-blob -right-16 -bottom-24 -z-10 size-80 bg-accent/30"></div>
    <{{ $heading }} class="mx-auto max-w-3xl font-display text-3xl leading-tight font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl">{{ $title }}</{{ $heading }}>
    @if ($text)<p class="mx-auto mt-4 max-w-2xl text-lg text-white/85">{{ $text }}</p>@endif
    @if ($button && $url)
        <a href="{{ $url }}" class="btn btn-accent btn-lg mt-9">{{ $button }} <x-icon name="arrow-right" class="size-4" /></a>
    @endif
</section>
