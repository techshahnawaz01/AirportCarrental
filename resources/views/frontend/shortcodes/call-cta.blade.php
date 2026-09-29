<aside class="not-prose my-8 flex flex-col items-start gap-4 rounded-2xl border border-primary/20 bg-primary/5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
    <div>
        <p class="text-lg font-bold text-secondary">{{ $title }}</p>
        @if ($text)<p class="mt-1 text-sm text-slate-600">{{ $text }}</p>@endif
    </div>
    <a href="{{ tel_href($phone) }}" class="btn btn-primary btn-lg w-full shrink-0 whitespace-normal text-center sm:w-auto">
        <x-icon name="phone" class="size-5" /> {{ $button }}: {{ $phone }}
        @if ($label)<span class="hidden font-normal opacity-80 sm:inline">({{ $label }})</span>@endif
    </a>
</aside>
