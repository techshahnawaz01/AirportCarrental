@extends('layouts.error')

@section('code', '422')
@section('title', 'We couldn’t process that')
@section('message', 'Some of the information sent was invalid. Please go back, check the form and try again.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Go back</a>
@endsection
