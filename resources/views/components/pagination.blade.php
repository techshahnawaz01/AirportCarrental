@if ($paginator->hasPages())
    <nav aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-fg-muted">
            @if ($paginator->firstItem())
                Showing <span class="font-medium text-fg">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-fg">{{ $paginator->lastItem() }}</span>
                of <span class="font-medium text-fg">{{ $paginator->total() }}</span>
            @endif
        </p>
        <ul class="flex items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="btn-icon cursor-not-allowed opacity-40" aria-disabled="true" aria-label="Previous"><x-icon name="chevron-left" class="size-4" /></span>
                @else
                    <a class="btn-icon" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous"><x-icon name="chevron-left" class="size-4" /></a>
                @endif
            </li>
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="hidden px-2 text-fg-muted sm:block">…</li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="hidden sm:block">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex size-9 items-center justify-center rounded-lg bg-primary text-sm font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex size-9 items-center justify-center rounded-lg text-sm text-fg-muted hover:bg-surface-muted hover:text-fg">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
            <li class="px-2 text-sm text-fg-muted sm:hidden">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</li>
            <li>
                @if ($paginator->hasMorePages())
                    <a class="btn-icon" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next"><x-icon name="chevron-right" class="size-4" /></a>
                @else
                    <span class="btn-icon cursor-not-allowed opacity-40" aria-disabled="true" aria-label="Next"><x-icon name="chevron-right" class="size-4" /></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
