@props(['name', 'label' => null, 'value' => null, 'help' => null, 'id' => null, 'rows' => 4, 'required' => false])
@php
    $id ??= 'f-'.\Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', $name));
    $dotted = preg_replace(['/\]\[|\[/', '/\]/'], ['.', ''], $name);
@endphp
<x-ui.field :label="$label" :for="$id" :name="$name" :help="$help" :required="$required" {{ $attributes->only('class') }}>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @if ($required) required @endif
        @error($dotted) aria-invalid="true" @enderror
        {{ $attributes->except('class')->merge(['class' => 'form-control']) }}>{{ old($dotted, $value) }}</textarea>
</x-ui.field>
