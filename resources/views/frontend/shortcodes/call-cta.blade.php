<aside class="not-prose relative my-10 overflow-hidden rounded-3xl border border-accent/25 bg-gradient-to-br from-accent-soft via-white to-primary-soft p-6 shadow-card sm:p-8">
    <div class="hero-blob -top-16 -right-16 size-48 bg-accent/20"></div>
    <div class="relative flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-accent to-accent-dark text-on-accent shadow-glow-accent"><x-icon name="phone" class="size-6" /></span>
            <div>
                <p class="font-display text-lg font-bold text-secondary sm:text-xl">{{ $title }}</p>
                @if ($text)<p class="mt-1 text-sm text-muted">{{ $text }}</p>@endif
            </div>
        </div>
        <a href="{{ tel_href($phone) }}" class="btn btn-accent btn-lg w-full shrink-0 whitespace-normal text-center sm:w-auto">
            <x-icon name="phone" class="size-5" /> {{ $button }}: {{ $phone }}
            @if ($label)<span class="hidden font-normal opacity-85 sm:inline">({{ $label }})</span>@endif
        </a>
    </div>
</aside>
