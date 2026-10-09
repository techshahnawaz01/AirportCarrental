@extends('layouts.app')

@section('content')
    <x-site.page-hero :page="$page" :subtitle="$page->excerpt" />

    <div class="container-site py-12 lg:py-20">
        @if (trim(strip_tags($content, '<img><iframe>')))
            <div class="content-prose mb-12 max-w-4xl">{!! $content !!}</div>
        @endif

        @if ($children->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
                @foreach ($children as $child)
                    <x-site.page-card :page="$child" :show-date="$child->type === 'post'" />
                @endforeach
            </div>
            <div class="mt-12">{{ $children->links() }}</div>
        @else
            <x-ui.empty-state icon="document" title="Nothing here yet" description="New content will appear here soon." class="rounded-3xl border-2 border-dashed border-line bg-soft" />
        @endif

        @if ($page->faqs->isNotEmpty())
            <x-site.faq-list :faqs="$page->faqs" class="mt-16" />
        @endif
    </div>
@endsection
