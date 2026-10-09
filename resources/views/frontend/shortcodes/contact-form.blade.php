<div class="not-prose card-premium relative overflow-hidden p-6 sm:p-10">
    <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-primary via-primary-light to-accent"></div>
    @if ($title)
        <div class="flex items-center gap-4">
            <span class="icon-tile"><x-icon name="chat" class="size-6" /></span>
            <div>
                <h2 class="text-xl font-bold text-secondary sm:text-2xl">{{ $title }}</h2>
                <p class="text-sm text-muted">Fill in the form and we’ll get back to you.</p>
            </div>
        </div>
    @endif
    <form action="{{ route('enquiries.store') }}" method="POST" data-ajax-form data-reset class="@if ($title) mt-8 @endif grid gap-5 sm:grid-cols-2" novalidate>
        @csrf
        <x-ui.form-alert class="sm:col-span-2" />
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
        <x-ui.input name="name" label="Full name" required autocomplete="name" maxlength="120" />
        <x-ui.input name="email" type="email" label="Email" required autocomplete="email" />
        <x-ui.input name="phone" type="tel" label="Phone" autocomplete="tel" help="Optional" />
        <x-ui.input name="subject" label="Subject" :value="$subject" maxlength="190" />
        <x-ui.textarea name="message" label="Message" rows="5" required minlength="10" maxlength="5000" class="sm:col-span-2" />
        <div class="flex flex-col-reverse gap-4 border-t border-line pt-5 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="flex items-center gap-2 text-xs text-muted"><x-icon name="lock" class="size-4 text-emerald-600" /> We only use your details to respond to your message.</p>
            <button class="btn btn-accent btn-lg" data-loading-text="Sending…"><x-icon name="mail" class="size-4" /> Send message</button>
        </div>
    </form>
</div>
