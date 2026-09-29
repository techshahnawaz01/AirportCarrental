@extends('layouts.error')

@section('code', '429')
@section('title', 'Too many requests')
@section('message', 'You’re making requests too quickly. Please wait a moment and try again.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Go back</a>
@endsection
