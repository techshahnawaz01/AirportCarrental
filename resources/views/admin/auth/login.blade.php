@extends('layouts.auth')
@section('title', 'Sign in')

@section('content')
    <h1 class="text-2xl font-bold tracking-tight">Sign in</h1>
    <p class="mt-1 text-sm text-fg-muted">Welcome back. Please enter your details.</p>

    <form method="POST" action="{{ route('admin.login.store') }}" data-ajax-form data-no-toast class="mt-8 space-y-5">
        @csrf
        <x-ui.input name="email" type="email" label="Email" required autofocus autocomplete="username" />
        <x-ui.input name="password" type="password" label="Password" required autocomplete="current-password" />
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="form-check"> Remember me</label>
            <a href="{{ route('admin.password.request') }}" class="text-sm font-medium text-primary hover:underline">Forgot password?</a>
        </div>
        <button class="btn btn-primary w-full" data-loading-text="Signing in…">Sign in</button>
    </form>
@endsection
