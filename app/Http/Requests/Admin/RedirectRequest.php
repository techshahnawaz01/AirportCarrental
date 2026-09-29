<?php

namespace App\Http\Requests\Admin;

use App\Models\Redirect;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['from_path' => Redirect::normalise((string) $this->input('from_path'))]);
    }

    public function rules(): array
    {
        return [
            'from_path' => ['required', 'string', 'max:500', 'regex:/^[A-Za-z0-9\-._~\/%]+$/', Rule::unique('redirects', 'from_path')->ignore($this->route('redirect'))],
            'to_url' => ['required', 'string', 'max:500', 'regex:/^(\/|https?:\/\/)/i', 'different:from_path'],
            'status_code' => ['required', Rule::in([301, 302])],
        ];
    }

    public function messages(): array
    {
        return ['to_url.regex' => 'The destination must start with / or http(s)://.'];
    }
}
