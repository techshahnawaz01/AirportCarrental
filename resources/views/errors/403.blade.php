@extends('layouts.error')

@section('code', '403')
@section('title', 'Access denied')
@section('message', 'You don’t have permission to view this page.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Go back</a>
@endsection
