<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesAuthorProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends AdminController
{
    use SavesAuthorProfile;

    public function edit(Request $request)
    {
        return view('admin.profile.edit', ['user' => $request->user()->load('avatar')]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $this->prepareAuthorSlug($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('users')->ignore($user)],
            ...$this->authorProfileRules($user),
        ]);
        $user->update($this->authorProfileData($request, $validated));

        return $this->success('Profile updated.', ['name' => $user->name, 'slug' => $user->slug, 'avatar' => $user->load('avatar')->avatar?->toPickerArray()]);
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => $data['password']]);
        $this->log('updated', 'Changed own password');

        return $this->success('Password updated.', ['reset' => true]);
    }
}
