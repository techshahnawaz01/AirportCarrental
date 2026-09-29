@if ($pages->isNotEmpty())
    <section class="not-prose">
        @if ($title)<h2 class="mb-6 text-2xl font-bold tracking-tight text-secondary sm:text-3xl">{{ $title }}</h2>@endif
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($pages as $item)
                <x-site.page-card :page="$item" :excerpt="false" />
            @endforeach
        </div>
    </section>
@endif
