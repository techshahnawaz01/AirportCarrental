<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'open_in_new_tab' => $this->boolean('open_in_new_tab'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $menu = $this->route('menu');

        return [
            'label' => ['required', 'string', 'max:120'],
            'page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'url' => ['nullable', 'required_without:page_id', 'string', 'max:500', 'regex:/^(\/|#|https?:\/\/|mailto:|tel:)/i'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('menu_id', $menu->id)->whereNull('parent_id')],
            'icon' => ['nullable', Rule::in(config('cms.icons'))],
            'image_id' => ['nullable', 'integer', 'exists:media,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'open_in_new_tab' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required_without' => 'Choose a page or enter a URL.',
            'url.regex' => 'Links must start with /, #, http(s)://, mailto: or tel:.',
            'parent_id.exists' => 'Only top-level items can have children.',
        ];
    }
}
