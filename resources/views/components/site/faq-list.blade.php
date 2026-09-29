@props(['faqs', 'title' => 'Frequently Asked Questions', 'subtitle' => null])
@if ($faqs->isNotEmpty())
    <section {{ $attributes->merge(['class' => 'mx-auto max-w-3xl']) }} aria-labelledby="faq-heading-{{ $faqs->first()->page_id }}">
        <div class="text-center">
            <h2 id="faq-heading-{{ $faqs->first()->page_id }}" class="text-2xl font-bold tracking-tight text-secondary sm:text-3xl">{{ $title }}</h2>
            @if ($subtitle)<p class="mt-2 text-slate-600">{{ $subtitle }}</p>@endif
        </div>
        <div class="mt-8 divide-y divide-line overflow-hidden rounded-2xl border border-line bg-white">
            @foreach ($faqs as $faq)
                <details class="group" @if ($loop->first) open @endif>
                    <summary class="flex cursor-pointer items-start justify-between gap-4 px-5 py-4 text-left font-semibold text-secondary transition hover:bg-slate-50 sm:px-6">
                        <span>{{ $faq->question }}</span>
                        <span class="details-icon mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary transition">
                            <x-icon name="plus" class="size-4" />
                        </span>
                    </summary>
                    <div class="content-prose px-5 pb-5 text-slate-600 sm:px-6">{!! $faq->answer !!}</div>
                </details>
            @endforeach
        </div>
    </section>
@endif
