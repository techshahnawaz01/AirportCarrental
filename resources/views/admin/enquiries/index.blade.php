@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Enquiries']]])
@section('title', 'Enquiries')

@section('content')
    <x-admin.page-header title="Enquiries" description="Messages submitted through the website contact forms." />
    <div class="card overflow-hidden" data-ajax-table data-url="{{ route('admin.enquiries.index') }}">
        <form data-table-filters class="flex flex-col gap-3 border-b border-line p-4 sm:flex-row">
            <div class="relative flex-1">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-fg-muted" />
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name, email or subject…" class="form-control pl-9" aria-label="Search">
            </div>
            <select name="status" class="form-control sm:w-44" aria-label="Status">
                <option value="">All</option>
                <option value="unread" @selected(($filters['status'] ?? '') === 'unread')>Unread</option>
                <option value="read" @selected(($filters['status'] ?? '') === 'read')>Read</option>
            </select>
        </form>
        <div data-table-body>@include('admin.enquiries._table')</div>
    </div>
@endsection
