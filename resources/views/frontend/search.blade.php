@extends('layouts.app')

@section('content')
    <x-site.page-hero :title="$term ? 'Search results' : 'Search'" :subtitle="$term ? '“'.$term.'” — '.$results->total().' '.\Illuminate\Support\Str::plural('result', $results->total()) : null" :crumbs="[['label' => 'Search']]">
        <form action="{{ route('search') }}" role="search" data-live-search class="relative mt-8 max-w-xl">
            <label for="search-q" class="sr-only">Search</label>
            <div class="flex items-center gap-2 rounded-2xl bg-white p-2 shadow-float">
                <span class="ml-2 hidden size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary sm:flex"><x-icon name="search" class="size-5" /></span>
                <input id="search-q" type="search" name="q" value="{{ $term }}" autocomplete="off" placeholder="What are you looking for?" class="min-w-0 flex-1 border-0 px-2 py-3 text-base text-secondary focus:ring-0">
                <button class="btn btn-accent btn-lg px-5 sm:px-7"><x-icon name="search" class="size-4" /> <span class="hidden sm:inline">Search</span></button>
            </div>
            <div data-live-results role="listbox" hidden class="absolute inset-x-0 top-full z-20 mt-2 max-h-96 overflow-y-auto rounded-2xl border border-line bg-white p-1.5 text-left shadow-float"></div>
        </form>
    </x-site.page-hero>

    <div class="container-site py-14 lg:py-20">
        @if ($results->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
                @foreach ($results as $result)
                    <x-site.page-card :page="$result" />
                @endforeach
            </div>
            <div class="mt-12">{{ $results->links() }}</div>
        @elseif ($term)
            <x-ui.empty-state icon="search" title="No results found" description="Try different keywords or browse the menu above." class="rounded-3xl border-2 border-dashed border-line bg-soft" />
        @else
            <x-ui.empty-state icon="search" title="Start typing to search" description="Search across every page, guide and article on the site." class="rounded-3xl border-2 border-dashed border-line bg-soft" />
        @endif
    </div>
@endsection
