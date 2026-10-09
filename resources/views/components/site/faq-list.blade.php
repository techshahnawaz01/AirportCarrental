@props(['faqs', 'title' => 'Frequently Asked Questions', 'subtitle' => null])
@if ($faqs->isNotEmpty())
    @php(app(\App\Support\FaqRegistry::class)->add($faqs))
    <section {{ $attributes->merge(['class' => 'mx-auto max-w-3xl']) }} aria-labelledby="faq-heading-{{ $faqs->first()->page_id }}" data-reveal>
        <div class="text-center">
            <span class="eyebrow"><x-icon name="chat" class="size-3.5" /> FAQ</span>
            <h2 id="faq-heading-{{ $faqs->first()->page_id }}" class="section-title mt-4">{{ $title }}</h2>
            @if ($subtitle)<p class="section-lead">{{ $subtitle }}</p>@endif
        </div>
        <div class="mt-10 space-y-3">
            @foreach ($faqs as $faq)
                <details class="group rounded-2xl border border-line bg-white shadow-sm transition open:border-primary/25 open:shadow-card" @if ($loop->first) open @endif>
                    <summary class="flex cursor-pointer items-start justify-between gap-4 px-5 py-4 text-left font-semibold text-secondary transition hover:text-primary sm:px-6 sm:py-5">
                        <span class="flex gap-3"><span class="font-display text-primary/60 tabular-nums">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $faq->question }}</span>
                        <span class="details-icon mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary transition duration-300">
                            <x-icon name="plus" class="size-4" />
                        </span>
                    </summary>
                    <div class="content-prose px-5 pb-5 pl-14 text-slate-600 sm:px-6 sm:pl-[3.75rem]">{!! $faq->answer !!}</div>
                </details>
            @endforeach
        </div>
    </section>
@endif
