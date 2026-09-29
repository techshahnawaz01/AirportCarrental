{{-- Switch-style checkbox that always submits a value (0/1). --}}
@props(['name', 'label', 'checked' => false, 'help' => null, 'id' => null])
@php $id ??= 'f-'.\Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', $name)); @endphp
<div {{ $attributes->merge(['class' => 'flex items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        <label for="{{ $id }}" class="text-sm font-medium text-fg">{{ $label }}</label>
        @if ($help)
            <p class="form-help mt-0.5">{{ $help }}</p>
        @endif
    </div>
    <label class="relative inline-flex shrink-0 cursor-pointer items-center">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" class="peer sr-only" @checked(old(preg_replace(['/\]\[|\[/', '/\]/'], ['.', ''], $name), $checked))>
        <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40 dark:bg-slate-600"></span>
        <span class="absolute left-0.5 top-0.5 size-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
    </label>
</div>
