@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Navigation', 'url' => route('admin.menus.index')], ['label' => $menu->name]]])
@section('title', $menu->name)

@section('content')
    <div data-menu-builder data-store-url="{{ route('admin.menus.items.store', $menu) }}" data-reorder-url="{{ route('admin.menus.items.reorder', $menu) }}">
    <x-admin.page-header :title="$menu->name" description="Link items to pages so they keep working when URLs change. Use the arrows to reorder.">
        <button type="button" class="btn btn-ghost text-red-600" data-action="{{ route('admin.menus.destroy', $menu) }}" data-method="DELETE" data-confirm="Delete this menu and all of its items?" data-confirm-button="Delete menu"><x-icon name="trash" class="size-4" /> Delete menu</button>
        <button type="button" class="btn btn-primary" data-add-item><x-icon name="plus" class="size-4" /> Add item</button>
    </x-admin.page-header>

    <div class="card">
        @if ($menu->rootItems->isEmpty())
            <x-ui.empty-state icon="list" title="This menu is empty" description="Add your first link to get started." />
        @else
            <ul class="divide-y divide-line">
                @foreach ($menu->rootItems as $item)
                    <li data-item-id="{{ $item->id }}">
                        @include('admin.menus._item', ['item' => $item, 'level' => 0])
                        @if ($item->children->isNotEmpty())
                            <ul class="border-t border-line bg-surface-muted/50">
                                @foreach ($item->children as $child)
                                    <li data-item-id="{{ $child->id }}" class="border-b border-line last:border-0">@include('admin.menus._item', ['item' => $child, 'level' => 1])</li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    </div>

    <x-ui.modal id="menu-item-dialog" title="Add menu item" size="max-w-2xl">
        <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" data-ajax-form class="space-y-4 p-5" novalidate>
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="label" label="Label" required maxlength="120" id="mi-label" />
                <x-ui.field label="Link to page" for="mi-page" name="page_id">
                    <select id="mi-page" name="page_id" class="form-control">
                        <option value="">— Custom URL —</option>
                        @foreach ($pages as $p)
                            <option value="{{ $p->id }}" data-title="{{ $p->title }}">{{ $p->title }} (/{{ $p->path }})</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <x-ui.input name="url" label="Custom URL" id="mi-url" placeholder="/path, https://…, tel:…" help="Used when no page is selected. Use # for a heading with a dropdown." />
                <x-ui.field label="Parent item" for="mi-parent" name="parent_id">
                    <select id="mi-parent" name="parent_id" class="form-control">
                        <option value="">— Top level —</option>
                        @foreach ($menu->rootItems as $root)
                            <option value="{{ $root->id }}">{{ $root->label }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <x-ui.select name="icon" label="Icon" id="mi-icon" :options="array_combine($icons, $icons)" placeholder="— None —" help="Used by icon tiles." />
                <x-ui.input name="description" label="Description" id="mi-description" maxlength="500" help="Used by image link cards." />
            </div>
            <x-ui.image-field name="image_id" label="Image" aspect="aspect-[3/1]" help="Used by image link cards." />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.toggle name="open_in_new_tab" label="Open in new tab" id="mi-new-tab" />
                <x-ui.toggle name="is_active" label="Visible" :checked="true" id="mi-active" />
            </div>
            <div class="flex justify-end gap-2 border-t border-line pt-4">
                <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                <button class="btn btn-primary" data-loading-text="Saving…">Save item</button>
            </div>
        </form>
    </x-ui.modal>
@endsection
