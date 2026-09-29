@props(['title', 'description' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        <h1 class="truncate text-2xl font-bold tracking-tight text-fg">{{ $title }}</h1>
        @if ($description)<p class="mt-1 text-sm text-fg-muted">{{ $description }}</p>@endif
    </div>
    @if (trim($slot))
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
