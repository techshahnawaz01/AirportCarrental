@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Profile']]])
@section('title', 'Profile')

@section('content')
    <x-admin.page-header title="Your profile" :description="$user->roleLabel().' · member since '.local_date($user->created_at)" />
    <div class="grid max-w-5xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.profile.update') }}" data-ajax-form>
            @csrf @method('PUT')
            <x-ui.card title="Account details">
                <div class="space-y-5">
                    <x-ui.input name="name" label="Name" :value="$user->name" required maxlength="120" />
                    <x-ui.input name="email" type="email" label="Email" :value="$user->email" required />
                </div>
                <div class="mt-6 flex justify-end"><button class="btn btn-primary" data-loading-text="Saving…">Save</button></div>
            </x-ui.card>
        </form>
        <form method="POST" action="{{ route('admin.profile.password') }}" data-ajax-form>
            @csrf @method('PUT')
            <x-ui.card title="Change password">
                <div class="space-y-5">
                    <x-ui.input name="current_password" type="password" label="Current password" required autocomplete="current-password" />
                    <x-ui.input name="password" type="password" label="New password" required autocomplete="new-password" help="At least 8 characters with letters and numbers." />
                    <x-ui.input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
                </div>
                <div class="mt-6 flex justify-end"><button class="btn btn-primary" data-loading-text="Updating…">Update password</button></div>
            </x-ui.card>
        </form>
    </div>
@endsection
