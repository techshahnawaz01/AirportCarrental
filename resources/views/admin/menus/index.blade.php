@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Navigation']]])
@section('title', 'Navigation')

@section('content')
    <x-admin.page-header title="Navigation" description="Menus shown in the header, footer and on the home page." />
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($locations as $location => $label)
            @php($menu = $menus->get($location))
            <div class="card flex flex-col p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ $label }}</h2>
                        <p class="mt-0.5 font-mono text-xs text-fg-muted">{{ $location }}</p>
                    </div>
                    <span class="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><x-icon name="list" class="size-5" /></span>
                </div>
                <p class="mt-4 text-sm text-fg-muted">{{ $menu ? $menu->items_count.' '.\Illuminate\Support\Str::plural('item', $menu->items_count) : 'Not created yet' }}</p>
                <div class="mt-auto pt-5">
                    @if ($menu)
                        <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-secondary w-full"><x-icon name="pencil" class="size-4" /> Edit items</a>
                    @else
                        <form method="POST" action="{{ route('admin.menus.store') }}" data-ajax-form>
                            @csrf
                            <input type="hidden" name="location" value="{{ $location }}">
                            <button class="btn btn-primary w-full"><x-icon name="plus" class="size-4" /> Create menu</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
