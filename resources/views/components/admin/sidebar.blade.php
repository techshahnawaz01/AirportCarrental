@php
    $unread = \App\Models\Enquiry::unread()->count();
    $pending = \App\Models\Comment::where('is_approved', false)->count();
    $currentType = request()->routeIs('admin.pages.*') ? (request('type') ?? (request()->route('page')?->type ?? 'page')) : null;
    $logo = settings()->url('branding.admin_logo', 'branding.logo');
@endphp
<aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-slate-900 transition-transform duration-200 lg:translate-x-0" aria-label="Admin navigation">
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
            @if ($logo)
                <span class="flex h-9 items-center rounded-lg bg-white px-2"><img src="{{ $logo }}" alt="" class="h-7 w-auto max-w-32 object-contain"></span>
            @else
                <span class="flex size-9 items-center justify-center rounded-lg bg-primary font-bold text-white">{{ mb_substr(settings('branding.site_name'), 0, 1) }}</span>
            @endif
            <span class="truncate text-sm font-semibold text-white">{{ settings('branding.site_name') }}</span>
        </a>
        <button type="button" class="ml-auto rounded-lg p-1.5 text-slate-400 hover:text-white lg:hidden" data-sidebar-toggle aria-label="Close menu"><x-icon name="x" /></button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
        <div class="space-y-1">
            <x-admin.nav-link :href="route('admin.dashboard')" icon="chart" :active="request()->routeIs('admin.dashboard')">Dashboard</x-admin.nav-link>
        </div>

        <div>
            <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Website</p>
            <div class="space-y-1">
                @foreach (config('cms.page_types') as $type => $config)
                    <x-admin.nav-link :href="route('admin.pages.index', ['type' => $type])" :icon="$config['icon']" :active="$currentType === $type">{{ $config['label'] }}</x-admin.nav-link>
                @endforeach
                <x-admin.nav-link :href="route('admin.media.index')" icon="photo" :active="request()->routeIs('admin.media.*')">Media</x-admin.nav-link>
                @can('manage-site')
                    <x-admin.nav-link :href="route('admin.menus.index')" icon="list" :active="request()->routeIs('admin.menus.*')">Navigation</x-admin.nav-link>
                @endcan
            </div>
        </div>

        <div>
            <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Engagement</p>
            <div class="space-y-1">
                <x-admin.nav-link :href="route('admin.enquiries.index')" icon="inbox" :active="request()->routeIs('admin.enquiries.*')" :badge="$unread ?: null">Enquiries</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.comments.index')" icon="chat" :active="request()->routeIs('admin.comments.*')" :badge="$pending ?: null">Comments</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.subscribers.index')" icon="mail" :active="request()->routeIs('admin.subscribers.*')">Subscribers</x-admin.nav-link>
            </div>
        </div>

        @can('manage-site')
            <div>
                <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Configuration</p>
                <div class="space-y-1">
                    <x-admin.nav-link :href="route('admin.settings.edit')" icon="cog" :active="request()->routeIs('admin.settings.*')">Settings</x-admin.nav-link>
                    <x-admin.nav-link :href="route('admin.seo.edit')" icon="globe" :active="request()->routeIs('admin.seo.*')">SEO</x-admin.nav-link>
                    <x-admin.nav-link :href="route('admin.users.index')" icon="users" :active="request()->routeIs('admin.users.*')">Users</x-admin.nav-link>
                    <x-admin.nav-link :href="route('admin.activity')" icon="bolt" :active="request()->routeIs('admin.activity')">Activity log</x-admin.nav-link>
                </div>
            </div>
        @endcan

        <div>
            <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-slate-500 uppercase">Account</p>
            <div class="space-y-1">
                <x-admin.nav-link :href="route('admin.profile.edit')" icon="user" :active="request()->routeIs('admin.profile.*')">Profile</x-admin.nav-link>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white">
                        <x-icon name="logout" class="size-5 text-slate-400 group-hover:text-white" /> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="border-t border-white/10 p-4">
        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 rounded-lg bg-white/5 px-3 py-2 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white">
            <x-icon name="external" class="size-4" /> View website
        </a>
    </div>
</aside>
<div id="admin-sidebar-overlay" class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"></div>
