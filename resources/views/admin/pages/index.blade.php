@extends('layouts.admin', ['breadcrumbs' => [['label' => $typeConfig['label']]]])
@section('title', $typeConfig['label'])

@section('content')
    <x-admin.page-header :title="$typeConfig['label']" :description="'Create and manage '.strtolower($typeConfig['label']).'.'">
        <x-ui.button :href="route('admin.pages.create', ['type' => $type])" icon="plus">New {{ strtolower($typeConfig['singular']) }}</x-ui.button>
    </x-admin.page-header>

    <div class="card overflow-hidden" data-ajax-table data-url="{{ route('admin.pages.index') }}">
        <form data-table-filters class="flex flex-col gap-3 border-b border-line p-4 sm:flex-row sm:items-center">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="relative flex-1">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-fg-muted" />
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by title or URL…" class="form-control pl-9" aria-label="Search">
            </div>
            <select name="status" class="form-control sm:w-44" aria-label="Status">
                <option value="">All statuses</option>
                <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
                <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Drafts</option>
                <option value="trashed" @selected(($filters['status'] ?? '') === 'trashed')>Trash ({{ $trashedCount }})</option>
            </select>
            <select name="sort" class="form-control sm:w-44" aria-label="Sort">
                <option value="updated">Recently updated</option>
                <option value="created" @selected(($filters['sort'] ?? '') === 'created')>Newest first</option>
                <option value="title" @selected(($filters['sort'] ?? '') === 'title')>Title A–Z</option>
            </select>
        </form>
        <div data-table-body>@include('admin.pages._table')</div>
    </div>
@endsection
