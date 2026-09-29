<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:5', 'max:2000'],
            'website' => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return ['body' => 'comment'];
    }
}
