@extends('layouts.app')

@section('content')
    <x-site.page-hero :title="$term ? 'Search results' : 'Search'" :subtitle="$term ? '“'.$term.'” — '.$results->total().' '.\Illuminate\Support\Str::plural('result', $results->total()) : null" :crumbs="[['label' => 'Search']]">
        <form action="{{ route('search') }}" role="search" data-live-search class="relative mt-8 max-w-xl">
            <label for="search-q" class="sr-only">Search</label>
            <div class="flex overflow-hidden rounded-xl bg-white shadow-xl">
                <input id="search-q" type="search" name="q" value="{{ $term }}" autocomplete="off" placeholder="What are you looking for?" class="min-w-0 flex-1 border-0 px-4 py-3.5 text-slate-900 focus:ring-0">
                <button class="btn btn-primary m-1.5"><x-icon name="search" class="size-4" /> Search</button>
            </div>
            <div data-live-results role="listbox" hidden class="absolute inset-x-0 top-full z-20 mt-2 max-h-96 overflow-y-auto rounded-xl border border-line bg-white p-1 text-left shadow-2xl"></div>
        </form>
    </x-site.page-hero>

    <div class="container-site py-12">
        @if ($results->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($results as $result)
                    <x-site.page-card :page="$result" />
                @endforeach
            </div>
            <div class="mt-10">{{ $results->links() }}</div>
        @elseif ($term)
            <x-ui.empty-state icon="search" title="No results found" description="Try different keywords or browse the menu above." class="rounded-2xl border border-dashed border-line" />
        @else
            <x-ui.empty-state icon="search" title="Start typing to search" description="Search across every page, guide and article on the site." class="rounded-2xl border border-dashed border-line" />
        @endif
    </div>
@endsection
