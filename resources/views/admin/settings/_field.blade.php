{{-- Renders one schema-defined setting. Expects $group, $key, $field. --}}
@php
    $fullKey = "{$group}.{$key}";
    $name = "values[{$key}]";
    $value = settings($fullKey);
@endphp
@switch($field['type'])
    @case('image')
        <x-ui.image-field :name="$name" :upload="'uploads['.$key.']'" :key="$key" value-key="path" :label="$field['label']"
            :value="$value" :url="settings()->url($fullKey)" :help="$field['help'] ?? null"
            :destroy-url="route('admin.settings.image.destroy', $fullKey)"
            :accept="$key === 'favicon' ? 'image/png,image/webp,image/x-icon,.ico' : 'image/png,image/jpeg,image/webp'"
            :aspect="$key === 'favicon' ? 'aspect-[3/1]' : 'aspect-[3/1]'" />
        @break
    @case('color')
        <x-ui.field :label="$field['label']" :for="'f-'.$key" :name="$name" :help="$field['help'] ?? null">
            <div class="flex items-center gap-2" data-color-field>
                <input type="color" value="{{ $value }}" class="h-10 w-14 cursor-pointer rounded-lg border border-line bg-surface p-1" aria-label="{{ $field['label'] }} picker" data-color-picker>
                <input id="f-{{ $key }}" name="{{ $name }}" value="{{ $value }}" class="form-control font-mono uppercase" pattern="#[0-9a-fA-F]{6}" maxlength="7" data-color-text>
            </div>
        </x-ui.field>
        @break
    @case('textarea')
    @case('code')
        <x-ui.textarea :name="$name" :label="$field['label']" :value="$value" :help="$field['help'] ?? null" :rows="$field['type'] === 'code' ? 6 : 3" @class(['font-mono text-xs' => $field['type'] === 'code']) />
        @break
    @case('toggle')
        <x-ui.toggle :name="$name" :label="$field['label']" :checked="filter_var($value, FILTER_VALIDATE_BOOLEAN)" :help="$field['help'] ?? null" />
        @break
    @case('select')
        <x-ui.select :name="$name" :label="$field['label']" :options="$field['options']" :value="$value" :help="$field['help'] ?? null" />
        @break
    @case('timezone')
        <x-ui.select :name="$name" :label="$field['label']" :options="array_combine(timezone_identifiers_list(), timezone_identifiers_list())" :value="$value" :help="$field['help'] ?? null" />
        @break
    @case('page')
        <x-ui.select :name="$name" :label="$field['label']" :options="$pages->mapWithKeys(fn ($p) => [$p->id => $p->title.' (/'.$p->path.')'])->all()" :value="$value" placeholder="— Not set —" :help="$field['help'] ?? null" />
        @break
    @default
        <x-ui.input :name="$name" :type="in_array($field['type'], ['email', 'url', 'tel']) ? $field['type'] : 'text'" :label="$field['label']" :value="$value" :help="$field['help'] ?? null" />
@endswitch
