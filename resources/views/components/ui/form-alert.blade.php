{{-- Summary banner filled by the AJAX form helper on validation errors. --}}
<div data-form-alert hidden {{ $attributes->merge(['class' => 'mb-5 flex gap-3 rounded-xl border border-red-500/20 bg-red-500/5 p-4 text-sm']) }} role="alert">
    <x-icon name="x-circle" class="size-5 shrink-0 text-red-600" />
    <p data-form-alert-text class="font-medium text-red-700 dark:text-red-400"></p>
</div>
