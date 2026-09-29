{{-- $options: [value => label]. Pass a slot instead for custom <option>s. --}}
@props(['name', 'label' => null, 'options' => [], 'value' => null, 'help' => null, 'id' => null, 'placeholder' => null, 'required' => false])
@php
    $id ??= 'f-'.\Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', $name));
    $dotted = preg_replace(['/\]\[|\[/', '/\]/'], ['.', ''], $name);
    $selected = (string) old($dotted, $value);
@endphp
<x-ui.field :label="$label" :for="$id" :name="$name" :help="$help" :required="$required" {{ $attributes->only('class') }}>
    <select id="{{ $id }}" name="{{ $name }}" @if ($required) required @endif {{ $attributes->except('class')->merge(['class' => 'form-control']) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>
</x-ui.field>
