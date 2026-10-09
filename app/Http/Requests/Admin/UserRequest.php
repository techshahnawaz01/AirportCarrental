<?php

namespace App\Http\Requests\Admin;

use App\Http\Controllers\Admin\Concerns\SavesAuthorProfile;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    use SavesAuthorProfile;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active'), 'email' => mb_strtolower((string) $this->input('email'))]);
        $this->prepareAuthorSlug($this);
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'is_active' => ['boolean'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            ...$this->authorProfileRules($user),
        ];
    }
}
