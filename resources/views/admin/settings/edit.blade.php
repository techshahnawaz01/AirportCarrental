@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Settings'], ['label' => $definition['label']]]])
@section('title', $definition['label'].' settings')

@section('content')
    <x-admin.page-header title="Settings" description="Configure your website without touching code." />

    <div class="grid gap-6 lg:grid-cols-4">
        <nav aria-label="Settings sections" class="lg:col-span-1">
            <ul class="flex gap-1 overflow-x-auto lg:flex-col">
                @foreach ($groups as $key => $item)
                    <li>
                        <a href="{{ route('admin.settings.edit', $key) }}" @if ($key === $group) aria-current="page" @endif
                           @class(['block rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition', 'bg-surface text-primary shadow-sm ring-1 ring-line' => $key === $group, 'text-fg-muted hover:bg-surface hover:text-fg' => $key !== $group])>{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <form method="POST" action="{{ route('admin.settings.update', $group) }}" enctype="multipart/form-data" data-ajax-form data-dirty-check class="lg:col-span-3" novalidate>
            @csrf @method('PUT')
            <x-ui.form-alert />
            <x-ui.card :title="$definition['label']" :description="$definition['description'] ?? null">
                <div @class(['grid gap-6', 'sm:grid-cols-2' => ! in_array($group, ['general', 'contact'])])>
                    @foreach ($definition['fields'] as $key => $field)
                        <div @class(['sm:col-span-2' => in_array($field['type'], ['textarea', 'code', 'toggle', 'secret'])])>
                            @include('admin.settings._field')
                        </div>
                    @endforeach
                </div>
                @if ($group === 'theme')
                    <div class="mt-6 rounded-xl border border-line p-4">
                        <p class="text-sm font-medium">Preview</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="btn btn-primary">Button</span><span class="btn btn-accent">Accent</span>
                            <span class="badge badge-primary">Primary</span>
                            <span class="rounded-lg bg-secondary px-3 py-1.5 text-sm text-white">Secondary</span>
                        </div>
                        <p class="form-help">Save to update the preview and the live website.</p>
                    </div>
                @endif
                <div class="mt-6 flex flex-col-reverse gap-2 border-t border-line pt-5 sm:flex-row sm:justify-end">
                    @if ($group === 'integrations')
                        <button type="button" class="btn btn-secondary" data-action="{{ route('admin.settings.integrations.test') }}" data-method="POST"
                                data-confirm="This fetches fresh wait times and flight data now (flight refreshes use your Aviationstack quota). Save any changes first." data-confirm-title="Test & refresh live data?" data-confirm-button="Test now" data-tone="primary">
                            <x-icon name="refresh" class="size-4" /> Test &amp; refresh data
                        </button>
                    @endif
                    <button class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> Save {{ strtolower($definition['label']) }}</button>
                </div>
            </x-ui.card>
        </form>
    </div>
@endsection
