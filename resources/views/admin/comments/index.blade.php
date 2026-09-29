@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Comments']]])
@section('title', 'Comments')

@section('content')
    <x-admin.page-header title="Comments" description="Visitor comments appear on the site after approval." />
    <div class="card overflow-hidden" data-ajax-table data-url="{{ route('admin.comments.index') }}">
        <form data-table-filters class="flex flex-col gap-3 border-b border-line p-4 sm:flex-row">
            <div class="relative flex-1">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-fg-muted" />
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search comments…" class="form-control pl-9" aria-label="Search">
            </div>
            <select name="status" class="form-control sm:w-48" aria-label="Status">
                <option value="">All comments</option>
                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending approval</option>
                <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Approved</option>
            </select>
        </form>
        <div data-table-body>@include('admin.comments._table')</div>
    </div>
@endsection
