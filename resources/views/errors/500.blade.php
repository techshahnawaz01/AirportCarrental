@extends('layouts.error')

@section('code', '500')
@section('title', 'Something went wrong')
@section('message', 'An unexpected error occurred on our side. Our team has been notified — please try again shortly.')
@section('actions')
    <a href="{{ url()->current() }}" class="btn btn-secondary">Try again</a>
@endsection
