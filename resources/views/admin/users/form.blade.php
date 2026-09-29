@php($isNew = ! $user->exists)
@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Users', 'url' => route('admin.users.index')], ['label' => $isNew ? 'New' : $user->name]]])
@section('title', $isNew ? 'New user' : 'Edit '.$user->name)

@section('content')
    <x-admin.page-header :title="$isNew ? 'New user' : 'Edit user'" />
    <form method="POST" action="{{ $isNew ? route('admin.users.store') : route('admin.users.update', $user) }}" data-ajax-form class="max-w-2xl" autocomplete="off">
        @csrf
        @unless ($isNew) @method('PUT') @endunless
        <x-ui.form-alert />
        <x-ui.card>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input name="name" label="Name" :value="$user->name" required maxlength="120" />
                <x-ui.input name="email" type="email" label="Email" :value="$user->email" required />
                <x-ui.select name="role" label="Role" :options="\App\Models\User::ROLES" :value="$user->role" required />
                <div class="flex items-end"><x-ui.toggle name="is_active" label="Active" :checked="$user->is_active" class="w-full" /></div>
                <x-ui.input name="password" type="password" label="{{ $isNew ? 'Password' : 'New password' }}" :required="$isNew" autocomplete="new-password" :help="$isNew ? 'At least 8 characters with letters and numbers.' : 'Leave blank to keep the current password.'" />
                <x-ui.input name="password_confirmation" type="password" label="Confirm password" :required="$isNew" autocomplete="new-password" />
            </div>
            <div class="mt-6 flex justify-end gap-2 border-t border-line pt-5">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" data-loading-text="Saving…">{{ $isNew ? 'Create user' : 'Save changes' }}</button>
            </div>
        </x-ui.card>
    </form>
@endsection
