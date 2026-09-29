@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Users']]])
@section('title', 'Users')

@section('content')
    <x-admin.page-header title="Users" description="Administrators manage everything; editors manage content, media and enquiries.">
        <x-ui.button :href="route('admin.users.create')" icon="plus">New user</x-ui.button>
    </x-admin.page-header>
    <div class="card overflow-hidden" data-ajax-table data-url="{{ route('admin.users.index') }}">
        <form data-table-filters class="flex flex-col gap-3 border-b border-line p-4 sm:flex-row">
            <div class="relative flex-1">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-fg-muted" />
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name or email…" class="form-control pl-9" aria-label="Search">
            </div>
            <select name="role" class="form-control sm:w-44" aria-label="Role">
                <option value="">All roles</option>
                @foreach (\App\Models\User::ROLES as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
        <div data-table-body>@include('admin.users._table')</div>
    </div>
@endsection
