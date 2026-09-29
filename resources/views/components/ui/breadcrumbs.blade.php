{{-- $items: [['label' => 'Pages', 'url' => '…'], ['label' => 'Edit']] --}}
@props(['items' => [], 'home' => null])
<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-sm">
        @if ($home)
            <li><a href="{{ $home }}" class="opacity-80 hover:opacity-100 hover:underline">Home</a></li>
        @endif
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                @if ($home || ! $loop->first)<x-icon name="chevron-right" class="size-3.5 opacity-60" />@endif
                @if (! empty($item['url']) && ! $loop->last)
                    <a href="{{ $item['url'] }}" class="opacity-80 hover:opacity-100 hover:underline">{{ $item['label'] }}</a>
                @else
                    <span aria-current="page" class="font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
