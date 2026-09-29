@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Activity log']]])
@section('title', 'Activity log')

@section('content')
    <x-admin.page-header title="Activity log" description="A record of changes made in the admin panel." />
    <div class="card overflow-hidden" data-ajax-table data-url="{{ route('admin.activity') }}">
        <div data-table-body>@include('admin.activity._list')</div>
    </div>
@endsection
