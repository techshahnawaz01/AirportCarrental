@extends('layouts.admin', ['breadcrumbs' => [['label' => 'Enquiries', 'url' => route('admin.enquiries.index')], ['label' => $enquiry->name]]])
@section('title', 'Enquiry from '.$enquiry->name)

@section('content')
    <x-admin.page-header :title="$enquiry->subject ?: 'Enquiry from '.$enquiry->name" :description="'Received '.local_date($enquiry->created_at, 'M j, Y \a\t g:i A')">
        <a href="mailto:{{ $enquiry->email }}?subject={{ rawurlencode('Re: '.($enquiry->subject ?: 'Your enquiry')) }}" class="btn btn-primary"><x-icon name="mail" class="size-4" /> Reply</a>
        <button type="button" class="btn btn-secondary" data-action="{{ route('admin.enquiries.read', $enquiry) }}" data-method="PATCH" data-toggle-scope>
            <span data-on>Mark as unread</span><span data-off hidden>Mark as read</span>
        </button>
        <button type="button" class="btn btn-ghost text-red-600" data-action="{{ route('admin.enquiries.destroy', $enquiry) }}" data-method="DELETE" data-confirm="Delete this enquiry?" data-confirm-button="Delete" data-redirect-after="{{ route('admin.enquiries.index') }}"><x-icon name="trash" class="size-4" /> Delete</button>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Message" class="lg:col-span-2">
            <p class="leading-7 whitespace-pre-line">{{ $enquiry->message }}</p>
        </x-ui.card>
        <x-ui.card title="Sender">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-fg-muted">Name</dt><dd class="font-medium">{{ $enquiry->name }}</dd></div>
                <div><dt class="text-fg-muted">Email</dt><dd><a href="mailto:{{ $enquiry->email }}" class="font-medium text-primary break-all">{{ $enquiry->email }}</a></dd></div>
                @if ($enquiry->phone)<div><dt class="text-fg-muted">Phone</dt><dd><a href="{{ tel_href($enquiry->phone) }}" class="font-medium text-primary">{{ $enquiry->phone }}</a></dd></div>@endif
                @if ($enquiry->source_url)<div><dt class="text-fg-muted">Sent from</dt><dd class="break-all">{{ $enquiry->source_url }}</dd></div>@endif
                <div><dt class="text-fg-muted">IP address</dt><dd>{{ $enquiry->ip_address ?? '—' }}</dd></div>
            </dl>
        </x-ui.card>
    </div>
@endsection
