{{-- $items: [['label' => 'Pages', 'url' => '…'], ['label' => 'Edit']] --}}
{{-- Always one line: earlier crumbs keep their size and the current page shrinks with an ellipsis on narrow screens. --}}
@props(['items' => [], 'home' => null])
<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'max-w-full min-w-0']) }}>
    <ol class="flex min-w-0 items-center gap-1.5 text-sm whitespace-nowrap">
        @if ($home)
            {{-- The site name stands in for "Home"; it gives way first on narrow screens. --}}
            @php($siteName = settings('branding.site_name') ?: 'Home')
            <li class="min-w-0 max-w-[45%] shrink sm:max-w-none"><a href="{{ $home }}" class="block truncate opacity-80 hover:opacity-100 hover:underline" title="{{ $siteName }}">{{ $siteName }}</a></li>
        @endif
        @foreach ($items as $item)
            <li @class(['flex items-center gap-1.5', 'min-w-0' => $loop->last, 'shrink-0' => ! $loop->last, 'max-w-[40%] min-w-0 sm:max-w-none' => ! $loop->last && $loop->count > 2])>
                @if ($home || ! $loop->first)<x-icon name="chevron-right" class="size-3.5 shrink-0 opacity-60" />@endif
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="truncate opacity-80 hover:opacity-100 hover:underline" title="{{ $item['label'] }}">{{ $item['label'] }}</a>
                @else
                    <span aria-current="page" class="truncate font-medium" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
