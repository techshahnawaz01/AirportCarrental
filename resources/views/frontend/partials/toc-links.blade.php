<ul class="space-y-0.5 text-sm">
    @foreach ($toc as $item)
        <li>
            <a href="#{{ $item['id'] }}" data-toc-link="{{ $item['id'] }}"
               @class([
                   '-ml-px block border-l-2 border-transparent py-2 pr-2 text-slate-600 transition hover:border-primary/40 hover:text-primary aria-[current=true]:border-primary aria-[current=true]:font-semibold aria-[current=true]:text-primary',
                   'pl-3' => $item['level'] === 2,
                   'pl-6 text-[13px]' => $item['level'] === 3,
               ])>{{ $item['text'] }}</a>
        </li>
    @endforeach
</ul>
