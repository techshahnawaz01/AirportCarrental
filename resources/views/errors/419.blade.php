@extends('layouts.error')

@section('code', '419')
@section('title', 'Session expired')
@section('message', 'Your session has expired for security reasons. Please go back, refresh the page and try again.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Go back</a>
@endsection
