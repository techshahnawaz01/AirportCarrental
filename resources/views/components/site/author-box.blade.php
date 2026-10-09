{{-- "About the author" card shown at the end of a blog post. --}}
@props(['author'])
<section {{ $attributes->merge(['class' => 'card-premium relative overflow-hidden p-6 sm:p-8']) }} aria-labelledby="about-author">
    <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-primary via-primary-light to-accent"></div>
    <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
        <a href="{{ $author->url() }}" class="shrink-0" tabindex="-1" aria-hidden="true">
            <x-site.author-avatar :author="$author" size="size-20" text="text-2xl" class="shadow-card" />
        </a>
        <div class="min-w-0 flex-1">
            <p id="about-author" class="text-xs font-bold tracking-wider text-accent-ink uppercase">About the author</p>
            <h2 class="mt-1 text-xl font-bold text-secondary"><a href="{{ $author->url() }}" class="hover:text-primary">{{ $author->name }}</a></h2>
            @if ($author->job_title)<p class="text-sm font-medium text-muted">{{ $author->job_title }}</p>@endif
            @if ($author->bio)<p class="mt-3 text-sm leading-6 text-slate-600">{{ $author->bio }}</p>@endif
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <a href="{{ $author->url() }}" class="btn btn-secondary btn-sm">View all posts <x-icon name="arrow-right" class="size-4" /></a>
                <x-site.author-social :author="$author" />
            </div>
        </div>
    </div>
</section>
