@extends('layouts.app')

@section('content')
    <x-site.page-hero :page="$page" :subtitle="$page->excerpt" />

    <div class="container-site py-12 lg:py-16">
        <div class="content-prose mx-auto max-w-5xl">{!! $content !!}</div>

        @if ($page->faqs->isNotEmpty())
            <x-site.faq-list :faqs="$page->faqs" class="mt-16" />
        @endif
    </div>
@endsection
