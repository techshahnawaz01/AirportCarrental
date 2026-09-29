@extends('layouts.app')

@section('content')
    <x-site.page-hero :page="$page" :subtitle="$page->excerpt" />

    <div class="container-site grid gap-10 py-12 lg:grid-cols-12 lg:py-16">
        <article class="min-w-0 lg:col-span-8">
            <div class="content-prose">{!! $content !!}</div>

            @if ($page->faqs->isNotEmpty())
                <x-site.faq-list :faqs="$page->faqs" class="mt-14" />
            @endif

            @isset($comments)
                <x-site.comments :page="$page" :comments="$comments" />
            @endisset
        </article>

        <aside class="lg:col-span-4">
            @include('frontend.partials.sidebar')
        </aside>
    </div>
@endsection
