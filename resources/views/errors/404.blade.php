@extends('layouts.error')

@section('code', '404')
@section('title', 'Page not found')
@section('message', 'Sorry, we couldn’t find the page you’re looking for. It may have moved or no longer exists.')
@section('actions')
    <a href="{{ route('search') }}" class="btn btn-secondary">Search the site</a>
@endsection
