@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Media']]])
@section('title', 'Media library')

@section('content')
    <x-admin.page-header title="Media library" description="Images you upload here can be used in pages, menus and settings." />

    <label data-dropzone data-url="{{ route('admin.media.store') }}" class="mb-6 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-line bg-surface px-6 py-10 text-center transition hover:border-primary/50">
        <span class="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary"><x-icon name="upload" class="size-6" /></span>
        <span class="text-sm font-semibold">Drop images here or click to upload</span>
        <span class="text-xs text-fg-muted">JPG, PNG, WebP or GIF · up to {{ round(config('cms.uploads.max_image_size') / 1024) }} MB each · max 20 at once</span>
        <span data-dropzone-status class="flex items-center gap-2 text-sm text-primary" aria-live="polite"></span>
        <input type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only">
    </label>

    <div class="card overflow-hidden" data-ajax-table data-url="{{ route('admin.media.index') }}">
        <form data-table-filters class="border-b border-line p-4">
            <div class="relative max-w-md">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-fg-muted" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by file name or alt text…" class="form-control pl-9" aria-label="Search media">
            </div>
        </form>
        <div data-table-body>@include('admin.media._grid')</div>
    </div>

    <x-ui.modal id="media-details" title="Image details" size="max-w-3xl">
        <div class="grid gap-6 p-5 md:grid-cols-2">
            <div class="flex items-center justify-center rounded-xl bg-surface-muted p-2"><img src="" alt="" class="max-h-80 w-auto rounded-lg object-contain"></div>
            <div class="min-w-0 space-y-4">
                <div>
                    <p data-field="name" class="truncate font-semibold"></p>
                    <p data-field="meta" class="text-sm text-fg-muted"></p>
                </div>
                <div>
                    <label class="form-label" for="media-url">Public URL</label>
                    <div class="flex gap-2"><input id="media-url" data-field="url" readonly class="form-control text-xs"><button type="button" class="btn btn-secondary" data-copy aria-label="Copy URL"><x-icon name="copy" class="size-4" /></button></div>
                </div>
                <div>
                    <label class="form-label" for="media-path">Path (for shortcodes)</label>
                    <div class="flex gap-2"><input id="media-path" data-field="path" readonly class="form-control font-mono text-xs"><button type="button" class="btn btn-secondary" data-copy aria-label="Copy path"><x-icon name="copy" class="size-4" /></button></div>
                </div>
                <form method="POST" action="#" data-ajax-form class="space-y-3">
                    @csrf @method('PUT')
                    <x-ui.input name="alt" label="Alt text" id="media-alt" maxlength="255" help="Describe the image for screen readers and SEO." />
                    <div class="flex justify-between gap-2">
                        <button type="button" data-delete data-action="" data-method="DELETE" data-confirm="This file will be permanently deleted and removed from any page, menu or setting that uses it." data-confirm-button="Delete file" class="btn btn-ghost text-red-600"><x-icon name="trash" class="size-4" /> Delete</button>
                        <button class="btn btn-primary" data-loading-text="Saving…">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </x-ui.modal>
@endsection
