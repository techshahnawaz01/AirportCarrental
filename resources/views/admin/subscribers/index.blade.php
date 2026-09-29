@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Subscribers']]])
@section('title', 'Subscribers')

@section('content')
    <x-admin.page-header title="Subscribers" description="People who signed up through the newsletter form.">
        <x-ui.button :href="route('admin.subscribers.export')" variant="secondary" icon="download">Export CSV</x-ui.button>
    </x-admin.page-header>
    <div class="card overflow-hidden" data-ajax-table data-url="{{ route('admin.subscribers.index') }}">
        <form data-table-filters class="border-b border-line p-4">
            <div class="relative max-w-md">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-fg-muted" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search emails…" class="form-control pl-9" aria-label="Search">
            </div>
        </form>
        <div data-table-body>@include('admin.subscribers._table')</div>
    </div>
@endsection
