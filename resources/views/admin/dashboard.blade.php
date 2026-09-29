@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    <x-admin.page-header :title="'Welcome back, '.strtok(auth()->user()->name, ' ')" description="Here’s what’s happening on your website.">
        <x-ui.button :href="route('admin.pages.create')" icon="plus">New page</x-ui.button>
    </x-admin.page-header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Total pages" :value="$stats['pages']" icon="document" :href="route('admin.pages.index')" :hint="$stats['published'].' published'" />
        <x-ui.stat label="Enquiries" :value="$stats['enquiries']" icon="inbox" :href="route('admin.enquiries.index')" :hint="$stats['unread_enquiries'].' unread'" />
        <x-ui.stat label="Total submissions" :value="$stats['submissions']" icon="chat" hint="Enquiries, comments & sign-ups" />
        <x-ui.stat label="Users" :value="$stats['users']" icon="users" :href="auth()->user()->can('manage-site') ? route('admin.users.index') : null" :hint="$stats['media'].' media files'" />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-ui.card title="Recent enquiries" class="xl:col-span-2" :padding="false">
            <x-slot:actions><a href="{{ route('admin.enquiries.index') }}" class="text-sm font-medium text-primary hover:underline">View all</a></x-slot:actions>
            @forelse ($recentEnquiries as $enquiry)
                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="flex items-center gap-4 border-b border-line px-5 py-3.5 last:border-0 hover:bg-surface-muted">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">{{ mb_strtoupper(mb_substr($enquiry->name, 0, 1)) }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-2"><span class="truncate text-sm font-semibold">{{ $enquiry->name }}</span>@unless ($enquiry->read_at)<x-ui.badge tone="primary">New</x-ui.badge>@endunless</span>
                        <span class="block truncate text-sm text-fg-muted">{{ $enquiry->subject ?: \Illuminate\Support\Str::limit($enquiry->message, 80) }}</span>
                    </span>
                    <time class="shrink-0 text-xs text-fg-muted">{{ $enquiry->created_at->diffForHumans() }}</time>
                </a>
            @empty
                <x-ui.empty-state icon="inbox" title="No enquiries yet" description="Messages sent through the contact form will appear here." />
            @endforelse
        </x-ui.card>

        <x-ui.card title="Website status">
            <ul class="space-y-3.5">
                @foreach ($status as $item)
                    <li class="flex items-start justify-between gap-3 text-sm">
                        <span class="flex items-center gap-2">
                            <span @class(['size-2 rounded-full', 'bg-emerald-500' => $item['ok'], 'bg-amber-500' => ! $item['ok'] && ($item['optional'] ?? false), 'bg-red-500' => ! $item['ok'] && ! ($item['optional'] ?? false)])></span>
                            {{ $item['label'] }}
                        </span>
                        <span class="text-right text-fg-muted">{{ $item['text'] }}</span>
                    </li>
                @endforeach
            </ul>
            @can('manage-site')
                <button type="button" class="btn btn-secondary btn-sm mt-5 w-full" data-action="{{ route('admin.cache.clear') }}" data-method="POST"><x-icon name="refresh" class="size-4" /> Clear cache</button>
            @endcan
        </x-ui.card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-ui.card title="Content overview">
            <ul class="space-y-3">
                @foreach (config('cms.page_types') as $type => $config)
                    @php($row = $pageCounts->get($type))
                    <li>
                        <a href="{{ route('admin.pages.index', ['type' => $type]) }}" class="flex items-center justify-between gap-3 rounded-lg p-2 -m-2 hover:bg-surface-muted">
                            <span class="flex items-center gap-3 text-sm font-medium"><x-icon :name="$config['icon']" class="size-5 text-fg-muted" />{{ $config['label'] }}</span>
                            <span class="text-sm text-fg-muted"><span class="font-semibold text-fg">{{ (int) ($row->total ?? 0) }}</span> · {{ (int) ($row->published ?? 0) }} live</span>
                        </a>
                    </li>
                @endforeach
                <li class="flex items-center justify-between border-t border-line pt-3 text-sm">
                    <span class="text-fg-muted">Pending comments</span>
                    <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}" class="font-semibold text-primary">{{ $stats['pending_comments'] }}</a>
                </li>
                <li class="flex items-center justify-between text-sm">
                    <span class="text-fg-muted">Newsletter subscribers</span>
                    <span class="font-semibold">{{ $stats['subscribers'] }}</span>
                </li>
            </ul>
        </x-ui.card>

        <x-ui.card title="Recently edited" :padding="false">
            @forelse ($recentPages as $page)
                <a href="{{ route('admin.pages.edit', $page) }}" class="flex items-center justify-between gap-3 border-b border-line px-5 py-3 last:border-0 hover:bg-surface-muted">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium">{{ $page->title }}</span>
                        <span class="text-xs text-fg-muted">{{ $page->typeLabel() }} · {{ $page->updated_at->diffForHumans() }}</span>
                    </span>
                    <x-ui.badge :tone="$page->is_active ? 'success' : 'neutral'">{{ $page->is_active ? 'Live' : 'Draft' }}</x-ui.badge>
                </a>
            @empty
                <x-ui.empty-state icon="document" title="No pages yet" />
            @endforelse
        </x-ui.card>

        <x-ui.card title="Recent activity" :padding="false">
            @can('manage-site')
                <x-slot:actions><a href="{{ route('admin.activity') }}" class="text-sm font-medium text-primary hover:underline">View all</a></x-slot:actions>
            @endcan
            @include('admin.activity._list', ['logs' => $recentActivity, 'compact' => true])
        </x-ui.card>
    </div>
@endsection
