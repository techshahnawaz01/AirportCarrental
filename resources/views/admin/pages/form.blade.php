@php
    $isNew = ! $page->exists;
    $label = $typeConfig['singular'];
    $faqs = old('faqs', $page->exists ? $page->faqs->map(fn ($f) => ['question' => $f->question, 'answer' => preg_replace('#<br\s*/?>#i', '', $f->answer)])->all() : []);
    $rooms = old('data.rooms', $page->meta('rooms', []));
@endphp
@extends('layouts.admin', ['breadcrumbs' => [['label' => $typeConfig['label'], 'url' => route('admin.pages.index', ['type' => $page->type])], ['label' => $isNew ? 'New' : 'Edit']]])
@section('title', $isNew ? 'New '.strtolower($label) : 'Edit: '.$page->title)

@section('content')
    <form method="POST" action="{{ $isNew ? route('admin.pages.store') : route('admin.pages.update', $page) }}" enctype="multipart/form-data" data-ajax-form data-dirty-check novalidate>
        @csrf
        @unless ($isNew) @method('PUT') @endunless
        <input type="hidden" name="type" value="{{ $page->type }}">

        <x-admin.page-header :title="$isNew ? 'New '.strtolower($label) : $page->title">
            @unless ($isNew)
                <a href="{{ $page->url() }}" target="_blank" rel="noopener" data-view-link class="btn btn-secondary"><x-icon name="external" class="size-4" /> View</a>
            @endunless
            <button class="btn btn-primary" data-loading-text="Saving…"><x-icon name="check" class="size-4" /> {{ $isNew ? 'Create '.strtolower($label) : 'Save changes' }}</button>
        </x-admin.page-header>

        <x-ui.form-alert />

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="min-w-0 space-y-6 xl:col-span-2">
                <x-ui.card>
                    <div class="space-y-5">
                        <x-ui.input name="title" label="Title" :value="$page->title" required maxlength="255" data-slug-source="#f-slug" />
                        <x-ui.field label="URL slug" for="f-slug" name="slug" required>
                            <input id="f-slug" name="slug" value="{{ old('slug', $page->slug) }}" class="form-control font-mono" maxlength="190" pattern="[a-z0-9-]+" required>
                            <p class="form-help truncate">URL: <span class="font-mono" data-url-preview data-base="{{ url('/') }}" data-slug="#f-slug" data-parent="#f-parent-id"></span></p>
                        </x-ui.field>
                        <x-ui.textarea name="excerpt" label="Summary" :value="$page->excerpt" rows="2" maxlength="1000" help="Shown under the title and on listing cards. Also used as the meta description fallback." />
                    </div>
                </x-ui.card>

                <x-ui.card title="Content" description="Tip: embed blocks like [contact_form], [faqs] or [latest_posts] anywhere in the content.">
                    <x-ui.field name="content">
                        <textarea name="content" data-editor rows="18" class="form-control font-mono text-xs" aria-label="Content">{{ old('content', $page->content) }}</textarea>
                    </x-ui.field>
                </x-ui.card>

                @if ($page->type === 'hotel' || $page->template === 'hotel')
                    @include('admin.pages._hotel-fields')
                @endif

                <x-ui.card title="FAQs" description="Shown as an accordion and added to search results as FAQ structured data.">
                    <div data-repeater="faqs">
                        <div data-repeater-items class="space-y-4">
                            @foreach ($faqs as $i => $faq)
                                @include('admin.pages._faq-row', ['index' => $i, 'faq' => $faq])
                            @endforeach
                        </div>
                        <p data-repeater-empty class="rounded-lg border border-dashed border-line p-6 text-center text-sm text-fg-muted">No FAQs yet.</p>
                        <template data-repeater-template>@include('admin.pages._faq-row', ['index' => '__INDEX__', 'faq' => []])</template>
                        <button type="button" class="btn btn-secondary btn-sm mt-4" data-repeater-add><x-icon name="plus" class="size-4" /> Add question</button>
                    </div>
                </x-ui.card>

                <x-ui.card title="Search engine optimisation">
                    <div class="space-y-5">
                        <x-ui.input name="meta_title" label="Meta title" :value="$page->meta_title" maxlength="255" help="Defaults to the page title. Aim for 50–60 characters." />
                        <x-ui.textarea name="meta_description" label="Meta description" :value="$page->meta_description" rows="2" maxlength="500" help="Aim for 120–160 characters." />
                        <x-ui.input name="meta_keywords" label="Keywords" :value="$page->meta_keywords" maxlength="500" help="Comma separated." />
                        <x-ui.input name="canonical_url" type="url" label="Canonical URL" :value="$page->canonical_url" help="Only set this if the content lives at another URL. Pages with a canonical URL are left out of the sitemap." />
                        <x-ui.image-field name="og_image_id" upload="og_image_upload" key="og_image" label="Social sharing image" :value="$page->og_image_id" :url="$page->ogImage?->url" help="1200×630 recommended. Falls back to the featured image." :max-kb="config('cms.uploads.max_image_size')" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.toggle name="noindex" label="Hide from search engines" :checked="$page->noindex" help="Adds noindex and removes it from the sitemap." />
                            <x-ui.toggle name="nofollow" label="Don’t follow links" :checked="$page->nofollow" />
                        </div>
                        <x-ui.input name="sitemap_priority" type="number" step="0.1" min="0" max="1" label="Sitemap priority" :value="$page->sitemap_priority" help="0.0–1.0. Leave empty for the default." class="sm:w-48" />
                    </div>
                </x-ui.card>
            </div>

            <div class="space-y-6">
                <x-ui.card title="Publishing">
                    <div class="space-y-5">
                        <x-ui.toggle name="is_active" label="Published" :checked="$page->is_active" help="Drafts are only visible to signed-in staff." />
                        <x-ui.input name="published_at" type="datetime-local" label="Publish date" :value="optional($page->published_at)->format('Y-m-d\TH:i')" help="Future dates schedule the page." />
                        <x-ui.toggle name="allow_comments" label="Allow comments" :checked="$page->allow_comments" />
                    </div>
                </x-ui.card>

                <x-ui.card title="Page attributes">
                    <div class="space-y-5">
                        <x-ui.select name="template" label="Template" :options="$templates" :value="$page->template" required help="Changing to Hotel shows hotel fields after saving." />
                        <x-ui.field label="Parent" for="f-parent-id" name="parent_id" help="Nest this page under another to build its URL.">
                            <select id="f-parent-id" name="parent_id" class="form-control">
                                <option value="" data-path="">— None (top level) —</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}" data-path="{{ $parent->path }}" @selected((string) old('parent_id', $page->parent_id) === (string) $parent->id)>{{ str_repeat('— ', substr_count($parent->path, '/')) }}{{ $parent->title }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.input name="sort_order" type="number" label="Order" :value="$page->sort_order ?? 0" help="Lower numbers appear first in listings." />
                    </div>
                </x-ui.card>

                <x-ui.card title="Featured image">
                    <x-ui.image-field name="featured_image_id" upload="featured_image_upload" key="featured_image" :value="$page->featured_image_id" :url="$page->featuredImage?->url" help="Used for the page header and cards." :max-kb="config('cms.uploads.max_image_size')" />
                </x-ui.card>

                @unless ($isNew)
                    <x-ui.card>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-fg-muted">Created</dt><dd>{{ local_date($page->created_at, 'M j, Y') }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-fg-muted">Last updated</dt><dd>{{ $page->updated_at->diffForHumans() }}</dd></div>
                            @if ($page->author)<div class="flex justify-between gap-3"><dt class="text-fg-muted">Author</dt><dd>{{ $page->author->name }}</dd></div>@endif
                        </dl>
                        @unless ($page->isHome())
                            <button type="button" class="btn btn-ghost btn-sm mt-4 w-full text-red-600" data-action="{{ route('admin.pages.destroy', $page) }}" data-method="DELETE"
                                    data-confirm="“{{ $page->title }}” will be moved to the trash." data-confirm-button="Move to trash"
                                    data-redirect-after="{{ route('admin.pages.index', ['type' => $page->type]) }}"><x-icon name="trash" class="size-4" /> Move to trash</button>
                        @endunless
                    </x-ui.card>
                @endunless
            </div>
        </div>
    </form>
@endsection
