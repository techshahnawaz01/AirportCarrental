@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'help' => null, 'id' => null, 'required' => false])
@php
    $id ??= 'f-'.\Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', $name));
    $dotted = preg_replace(['/\]\[|\[/', '/\]/'], ['.', ''], $name);
@endphp
<x-ui.field :label="$label" :for="$id" :name="$name" :help="$help" :required="$required" {{ $attributes->only('class') }}>
    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
        @if ($type !== 'password') value="{{ old($dotted, $value) }}" @endif
        @if ($required) required @endif
        @if ($help) aria-describedby="{{ $id }}-help" @endif
        @error($dotted) aria-invalid="true" @enderror
        {{ $attributes->except('class')->merge(['class' => 'form-control']) }}>
</x-ui.field>
