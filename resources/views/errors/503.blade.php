@extends('layouts.error')

@section('code', '503')
@section('title', 'Be right back')
@section('message', 'We’re performing scheduled maintenance. Please check back in a few minutes.')
@section('actions')
    <a href="{{ url()->current() }}" class="btn btn-secondary">Try again</a>
@endsection
