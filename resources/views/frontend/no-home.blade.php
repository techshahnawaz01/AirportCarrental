@extends('layouts.app')

@section('content')
    <section class="relative isolate overflow-hidden bg-gradient-to-br from-primary-soft via-white to-accent-soft">
        <div class="hero-blob -top-24 -left-24 -z-10 size-96 bg-primary/15"></div>
        <div class="hero-blob -right-24 -bottom-24 -z-10 size-96 bg-accent/15"></div>
        <div class="container-site py-28 text-center lg:py-36">
            <span class="icon-tile mx-auto size-16"><x-icon name="plane" class="size-8" /></span>
            <h1 class="mt-8 text-4xl font-bold tracking-tight text-secondary sm:text-5xl">{{ settings('branding.site_name') }}</h1>
            @if ($tagline = settings('branding.tagline'))<p class="mt-4 text-lg text-muted">{{ $tagline }}</p>@endif
            <p class="mx-auto mt-8 max-w-md text-muted">This website is being set up. Please check back soon.</p>
            @can('access-admin')
                <a href="{{ route('admin.settings.edit', 'general') }}" class="btn btn-accent btn-lg mt-8">Choose a home page</a>
            @endcan
        </div>
    </section>
@endsection
