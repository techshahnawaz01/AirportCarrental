{{-- Label + control slot + help text + AJAX/server error slot. --}}
@props(['label' => null, 'for' => null, 'name' => null, 'help' => null, 'required' => false])
@php $errorKey = $name ? preg_replace(['/\]\[|\[/', '/\]/'], ['.', ''], $name) : null; @endphp
<div {{ $attributes }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="form-label">
            {{ $label }} @if ($required)<span class="text-red-600" aria-hidden="true">*</span>@endif
        </label>
    @endif
    {{ $slot }}
    @if ($help)
        <p class="form-help">{{ $help }}</p>
    @endif
    @if ($errorKey)
        <p class="form-error" data-error-for="{{ $errorKey }}" role="alert" @unless ($errors->has($errorKey)) hidden @endunless>{{ $errors->first($errorKey) }}</p>
    @endif
</div>
