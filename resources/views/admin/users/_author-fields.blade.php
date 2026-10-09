{{-- Public author profile (shown on blog posts and /author/{slug}). Used by the user form and the profile page. --}}
<div class="grid gap-6 lg:grid-cols-3">
    <div>
        <x-ui.image-field name="avatar_id" upload="avatar_upload" key="avatar" label="Photo" :value="$user->avatar_id" :url="$user->avatar?->url"
                          aspect="aspect-square" help="Square photo, at least 300×300px." :max-kb="config('cms.uploads.max_image_size')" />
    </div>
    <div class="space-y-5 lg:col-span-2">
        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.input name="job_title" label="Job title" :value="$user->job_title" maxlength="120" help="E.g. “Travel writer”." />
            <x-ui.input name="slug" label="Author URL" :value="$user->slug" maxlength="140" :help="'Your page: '.url('/author').'/'.($user->slug ?: 'your-name')" />
        </div>
        <x-ui.textarea name="bio" label="Biographical info" rows="4" maxlength="2000" :value="$user->bio" help="A short introduction shown under your posts and on your author page." />
    </div>
</div>
<div class="mt-6 border-t border-line pt-5">
    <p class="form-label">Social profiles</p>
    <div class="mt-3 grid gap-4 sm:grid-cols-2">
        @foreach (\App\Models\User::SOCIAL as $key => $label)
            <x-ui.input name="social[{{ $key }}]" type="url" :label="$label" :value="$user->social[$key] ?? null" placeholder="https://" maxlength="255" />
        @endforeach
    </div>
</div>
