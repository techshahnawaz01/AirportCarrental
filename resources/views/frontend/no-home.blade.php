@extends('layouts.app')

@section('content')
    <div class="container-site py-24 text-center">
        <h1 class="text-4xl font-extrabold tracking-tight text-secondary">{{ settings('branding.site_name') }}</h1>
        @if ($tagline = settings('branding.tagline'))<p class="mt-3 text-lg text-slate-600">{{ $tagline }}</p>@endif
        <p class="mx-auto mt-8 max-w-md text-slate-500">This website is being set up. Please check back soon.</p>
        @can('access-admin')
            <a href="{{ route('admin.settings.edit', 'general') }}" class="btn btn-primary mt-6">Choose a home page</a>
        @endcan
    </div>
@endsection
