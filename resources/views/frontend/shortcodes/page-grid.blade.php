@if ($pages->isNotEmpty())
    <section class="not-prose" data-reveal>
        @if ($title)<h2 class="section-title mb-8">{{ $title }}</h2>@endif
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($pages as $item)
                <x-site.page-card :page="$item" :excerpt="false" />
            @endforeach
        </div>
    </section>
@endif
