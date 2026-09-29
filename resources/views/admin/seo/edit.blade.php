@extends('layouts.admin', ['breadcrumbs' => [['label' => 'SEO']]])
@section('title', 'SEO')

@section('content')
    <x-admin.page-header title="SEO" description="Search defaults, analytics, robots.txt and redirects.">
        <a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="btn btn-secondary"><x-icon name="external" class="size-4" /> sitemap.xml</a>
        <a href="{{ route('robots') }}" target="_blank" rel="noopener" class="btn btn-secondary"><x-icon name="external" class="size-4" /> robots.txt</a>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-5">
        <form method="POST" action="{{ route('admin.settings.update', 'seo') }}" enctype="multipart/form-data" data-ajax-form class="xl:col-span-3" novalidate>
            @csrf @method('PUT')
            <x-ui.form-alert />
            <x-ui.card title="Defaults & verification" :description="$definition['description']">
                <div class="space-y-6">
                    @foreach ($definition['fields'] as $key => $field)
                        @include('admin.settings._field', ['group' => 'seo', 'pages' => collect()])
                    @endforeach
                </div>
                <div class="mt-6 flex justify-end border-t border-line pt-5">
                    <button class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> Save SEO settings</button>
                </div>
            </x-ui.card>
        </form>

        <x-ui.card title="Redirects" description="Send old URLs to new ones (e.g. after changing a slug)." class="xl:col-span-2" :padding="false">
            <form method="POST" action="{{ route('admin.redirects.store') }}" data-ajax-form data-reset data-prepend-to="#redirect-list" data-hide-on-success="#redirect-empty" class="space-y-3 border-b border-line p-5">
                @csrf
                <x-ui.input name="from_path" label="From path" placeholder="/old-page" required />
                <x-ui.input name="to_url" label="To" placeholder="/new-page or https://…" required />
                <div class="flex items-end gap-3">
                    <x-ui.select name="status_code" label="Type" :options="[301 => '301 Permanent', 302 => '302 Temporary']" value="301" class="flex-1" />
                    <button class="btn btn-primary" data-loading-text="Adding…"><x-icon name="plus" class="size-4" /> Add</button>
                </div>
            </form>
            <ul id="redirect-list" class="divide-y divide-line">
                @foreach ($redirects as $redirect)
                    @include('admin.seo._redirect-row')
                @endforeach
            </ul>
            <p id="redirect-empty" @class(['p-6 text-center text-sm text-fg-muted', 'hidden' => $redirects->isNotEmpty()])>No redirects yet.</p>
        </x-ui.card>
    </div>

@endsection
