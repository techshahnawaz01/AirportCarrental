<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validation and saving for the public author profile (photo, job title, bio, social links),
 * shared by Users (administrators) and Profile (every user).
 */
trait SavesAuthorProfile
{
    protected function authorProfileRules(?User $user): array
    {
        return [
            'slug' => ['nullable', 'string', 'max:140', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('users', 'slug')->ignore($user)],
            'job_title' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'avatar_id' => ['nullable', 'integer', 'exists:media,id'],
            'avatar_upload' => ['nullable', 'image', 'mimes:'.implode(',', config('cms.uploads.image_mimes')), 'max:'.config('cms.uploads.max_image_size')],
            'social' => ['nullable', 'array'],
            'social.*' => ['nullable', 'url:http,https', 'max:255'],
        ];
    }

    /** Normalises the slug before validation (typed names become valid slugs). */
    protected function prepareAuthorSlug(Request $request): void
    {
        if ($request->filled('slug')) {
            $request->merge(['slug' => Str::slug((string) $request->input('slug'))]);
        }
    }

    /** Turns validated input into user attributes, storing a newly uploaded photo. */
    protected function authorProfileData(Request $request, array $validated): array
    {
        if (($file = $request->file('avatar_upload')) instanceof UploadedFile) {
            $validated['avatar_id'] = app(MediaService::class)->upload($file, 'avatars', $request->user()->id)->id;
        }
        unset($validated['avatar_upload']);

        if (array_key_exists('social', $validated)) {
            $validated['social'] = array_filter(
                array_intersect_key((array) $validated['social'], User::SOCIAL),
                fn ($url) => filled($url),
            ) ?: null;
        }

        return $validated;
    }
}
