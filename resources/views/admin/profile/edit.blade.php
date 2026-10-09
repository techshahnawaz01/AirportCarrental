@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Profile']]])
@section('title', 'Profile')

@section('content')
    <x-admin.page-header title="Your profile" :description="$user->roleLabel().' · member since '.local_date($user->created_at)" />
    <div class="grid max-w-5xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" data-ajax-form class="space-y-6 lg:col-span-2">
            @csrf @method('PUT')
            <x-ui.card title="Account details">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input name="name" label="Name" :value="$user->name" required maxlength="120" help="Shown as the author name on your posts." />
                    <x-ui.input name="email" type="email" label="Email" :value="$user->email" required />
                </div>
            </x-ui.card>
            <x-ui.card title="Author profile" description="Shown on your blog posts and on your author page.">
                @include('admin.users._author-fields')
                <div class="mt-6 flex items-center justify-end gap-3 border-t border-line pt-5">
                    @if ($user->posts()->published()->where('type', 'post')->exists())
                        <a href="{{ $user->url() }}" target="_blank" rel="noopener" class="btn btn-secondary"><x-icon name="external" class="size-4" /> View author page</a>
                    @endif
                    <button class="btn btn-primary" data-loading-text="Saving…">Save profile</button>
                </div>
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
