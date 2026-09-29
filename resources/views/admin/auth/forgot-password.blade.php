@extends('layouts.auth')
@section('title', 'Forgot password')

@section('content')
    <h1 class="text-2xl font-bold tracking-tight">Forgot your password?</h1>
    <p class="mt-1 text-sm text-fg-muted">Enter your email and we’ll send you a link to choose a new one.</p>

    <form method="POST" action="{{ route('admin.password.email') }}" data-ajax-form data-reset class="mt-8 space-y-5">
        @csrf
        <x-ui.input name="email" type="email" label="Email" required autofocus autocomplete="username" />
        <button class="btn btn-primary w-full" data-loading-text="Sending…">Email reset link</button>
    </form>
    <a href="{{ route('admin.login') }}" class="mt-6 inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"><x-icon name="arrow-left" class="size-4" /> Back to sign in</a>
@endsection
