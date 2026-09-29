<div class="not-prose rounded-2xl border border-line bg-white p-5 shadow-sm sm:p-8">
    @if ($title)<h2 class="text-xl font-bold text-secondary sm:text-2xl">{{ $title }}</h2>@endif
    <form action="{{ route('enquiries.store') }}" method="POST" data-ajax-form data-reset class="@if ($title) mt-6 @endif grid gap-4 sm:grid-cols-2" novalidate>
        @csrf
        <x-ui.form-alert class="sm:col-span-2" />
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
        <x-ui.input name="name" label="Full name" required autocomplete="name" maxlength="120" />
        <x-ui.input name="email" type="email" label="Email" required autocomplete="email" />
        <x-ui.input name="phone" type="tel" label="Phone" autocomplete="tel" help="Optional" />
        <x-ui.input name="subject" label="Subject" :value="$subject" maxlength="190" />
        <x-ui.textarea name="message" label="Message" rows="5" required minlength="10" maxlength="5000" class="sm:col-span-2" />
        <div class="flex flex-col-reverse gap-3 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">We only use your details to respond to your message.</p>
            <button class="btn btn-primary btn-lg" data-loading-text="Sending…"><x-icon name="mail" class="size-4" /> Send message</button>
        </div>
    </form>
</div>
