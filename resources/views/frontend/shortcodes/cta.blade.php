<section class="not-prose relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-secondary px-6 py-14 text-center text-white shadow-lg sm:px-12">
    @if ($image)
        <img src="{{ $image }}" alt="" loading="lazy" class="absolute inset-0 -z-10 size-full object-cover opacity-20 mix-blend-luminosity">
    @endif
    <h2 class="text-2xl font-bold tracking-tight text-balance sm:text-3xl">{{ $title }}</h2>
    @if ($text)<p class="mx-auto mt-3 max-w-2xl text-white/85">{{ $text }}</p>@endif
    @if ($button && $url)
        <a href="{{ $url }}" class="btn btn-accent btn-lg mt-8">{{ $button }} <x-icon name="arrow-right" class="size-4" /></a>
    @endif
</section>
