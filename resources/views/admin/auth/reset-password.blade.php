@extends('layouts.auth')
@section('title', 'Reset password')

@section('content')
    <h1 class="text-2xl font-bold tracking-tight">Choose a new password</h1>
    <p class="mt-1 text-sm text-fg-muted">Use at least 8 characters with letters and numbers.</p>

    <form method="POST" action="{{ route('admin.password.update') }}" data-ajax-form class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-ui.input name="email" type="email" label="Email" :value="$email" required autocomplete="username" />
        <x-ui.input name="password" type="password" label="New password" required autocomplete="new-password" />
        <x-ui.input name="password_confirmation" type="password" label="Confirm password" required autocomplete="new-password" />
        <button class="btn btn-primary w-full" data-loading-text="Saving…">Reset password</button>
    </form>
@endsection
