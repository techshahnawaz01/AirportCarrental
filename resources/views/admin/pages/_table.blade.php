@php($trash = ($filters['status'] ?? null) === 'trashed')
@if ($pages->isEmpty())
    <x-ui.empty-state :icon="$typeConfig['icon']" :title="$trash ? 'Trash is empty' : 'No '.strtolower($typeConfig['label']).' found'"
        :description="! empty($filters['q']) ? 'Try a different search term.' : null">
        @unless ($trash || ! empty($filters['q']))
            <x-ui.button :href="route('admin.pages.create', ['type' => $type])" icon="plus">Create the first one</x-ui.button>
        @endunless
    </x-ui.empty-state>
@else
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col" class="hidden md:table-cell">Template</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="hidden lg:table-cell">Updated</th>
                    <th scope="col" class="text-right"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pages as $page)
                    <tr>
                        <td class="max-w-md">
                            @if ($trash)
                                <p class="truncate font-medium">{{ $page->title }}</p>
                            @else
                                <a href="{{ route('admin.pages.edit', $page) }}" class="block truncate font-medium hover:text-primary">{{ $page->title }}</a>
                            @endif
                            <p class="truncate text-xs text-fg-muted">/{{ $page->path }} @if ($page->isHome())<x-ui.badge tone="primary" class="ml-1">Home</x-ui.badge>@endif</p>
                        </td>
                        <td class="hidden text-fg-muted md:table-cell">{{ config('cms.templates.'.$page->template, $page->template) }}</td>
                        <td>
                            @if ($trash)
                                <x-ui.badge tone="danger">In trash</x-ui.badge>
                            @else
                                <x-ui.status-toggle :url="route('admin.pages.toggle', $page)" :active="$page->is_active" />
                            @endif
                        </td>
                        <td class="hidden text-fg-muted lg:table-cell" title="{{ local_date($page->updated_at, 'M j, Y g:i A') }}">{{ $page->updated_at->diffForHumans() }}</td>
                        <td>
                            <div class="flex justify-end gap-1">
                                @if ($trash)
                                    <button type="button" class="btn-icon" title="Restore" aria-label="Restore" data-action="{{ route('admin.pages.restore', $page->id) }}" data-method="PATCH" data-remove="tr"><x-icon name="restore" class="size-4" /></button>
                                    @can('manage-site')
                                        <button type="button" class="btn-icon hover:text-red-600" title="Delete permanently" aria-label="Delete permanently" data-action="{{ route('admin.pages.force-delete', $page->id) }}" data-method="DELETE" data-confirm="This permanently deletes “{{ $page->title }}” and its FAQs and comments. This cannot be undone." data-confirm-button="Delete forever" data-remove="tr"><x-icon name="trash" class="size-4" /></button>
                                    @endcan
                                @else
                                    <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="btn-icon" title="View" aria-label="View on site"><x-icon name="eye" class="size-4" /></a>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn-icon" title="Edit" aria-label="Edit"><x-icon name="pencil" class="size-4" /></a>
                                    <button type="button" class="btn-icon hover:text-red-600" title="Move to trash" aria-label="Move to trash" data-action="{{ route('admin.pages.destroy', $page) }}" data-method="DELETE" data-confirm="“{{ $page->title }}” will be moved to the trash. You can restore it later." data-confirm-button="Move to trash" data-remove="tr"><x-icon name="trash" class="size-4" /></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="border-t border-line px-4 py-3">{{ $pages->links() }}</div>
@endif
