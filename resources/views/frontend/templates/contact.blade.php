@extends('layouts.app')

@section('content')
    <x-site.page-hero :page="$page" :subtitle="$page->excerpt" />
    @php
        $s = settings();
        $details = array_filter([
            ['icon' => 'phone', 'label' => 'Phone', 'value' => site_phone(), 'href' => site_phone() ? tel_href(site_phone()) : null],
            ['icon' => 'mail', 'label' => 'Email', 'value' => $s->get('contact.contact_email'), 'href' => 'mailto:'.$s->get('contact.contact_email')],
            ['icon' => 'info', 'label' => 'Support', 'value' => $s->get('contact.support_email'), 'href' => 'mailto:'.$s->get('contact.support_email')],
            ['icon' => 'whatsapp', 'label' => 'WhatsApp', 'value' => $s->get('contact.whatsapp'), 'href' => $s->get('contact.whatsapp') ? 'https://wa.me/'.preg_replace('/\D/', '', $s->get('contact.whatsapp')) : null],
            ['icon' => 'map-pin', 'label' => 'Address', 'value' => $s->get('contact.business_address'), 'href' => null],
        ], fn ($d) => filled($d['value']));
    @endphp

    <div class="container-site grid gap-10 py-12 lg:grid-cols-12 lg:gap-12 lg:py-20">
        <div class="lg:col-span-5">
            @if (trim(strip_tags($content)))
                <div class="content-prose">{!! $content !!}</div>
            @endif
            @if ($details)
                <ul class="mt-8 space-y-4">
                    @foreach ($details as $detail)
                        <li class="card-premium card-hover flex items-center gap-4 p-5">
                            <span class="icon-tile"><x-icon :name="$detail['icon']" class="size-5" /></span>
                            <span class="min-w-0">
                                <span class="block text-xs font-bold tracking-wider text-muted uppercase">{{ $detail['label'] }}</span>
                                @if ($detail['href'])
                                    <a href="{{ $detail['href'] }}" class="font-semibold break-words text-secondary hover:text-primary">{{ $detail['value'] }}</a>
                                @else
                                    <span class="font-semibold whitespace-pre-line text-secondary">{{ $detail['value'] }}</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="lg:col-span-7">
            @include('frontend.shortcodes.contact-form', ['title' => 'Send us a message', 'subject' => null])
        </div>
    </div>

    @if ($map = $s->get('contact.map_embed_url'))
        <div class="container-site">
            <iframe src="{{ $map }}" title="Map" class="h-[28rem] w-full rounded-3xl border border-line shadow-card" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
    @endif

    @if ($page->faqs->isNotEmpty())
        <div class="container-site py-20"><x-site.faq-list :faqs="$page->faqs" /></div>
    @endif
@endsection
