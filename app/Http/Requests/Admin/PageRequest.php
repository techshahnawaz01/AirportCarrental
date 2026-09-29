<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware already restricts to staff.
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('title'))),
            'is_active' => $this->boolean('is_active'),
            'allow_comments' => $this->boolean('allow_comments'),
            'noindex' => $this->boolean('noindex'),
            'nofollow' => $this->boolean('nofollow'),
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);
    }

    public function rules(): array
    {
        $image = ['nullable', 'image', 'mimes:'.implode(',', config('cms.uploads.image_mimes')), 'max:'.config('cms.uploads.max_image_size')];

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'type' => ['required', Rule::in(array_keys(config('cms.page_types')))],
            'template' => ['required', Rule::in(array_keys(config('cms.templates')))],
            'parent_id' => ['nullable', 'integer', 'exists:pages,id'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:1000000'],
            'is_active' => ['boolean'],
            'allow_comments' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['integer', 'between:-9999,9999'],
            'featured_image_id' => ['nullable', 'integer', 'exists:media,id'],
            'featured_image_upload' => $image,
            'og_image_id' => ['nullable', 'integer', 'exists:media,id'],
            'og_image_upload' => $image,
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'noindex' => ['boolean'],
            'nofollow' => ['boolean'],
            'sitemap_priority' => ['nullable', 'numeric', 'between:0,1'],
            'faqs' => ['nullable', 'array', 'max:50'],
            'faqs.*.question' => ['nullable', 'required_with:faqs.*.answer', 'string', 'max:500'],
            'faqs.*.answer' => ['nullable', 'required_with:faqs.*.question', 'string', 'max:5000'],
            'data' => ['nullable', 'array'],
            'data.address' => ['nullable', 'string', 'max:500'],
            'data.location_title' => ['nullable', 'string', 'max:255'],
            'data.location_description' => ['nullable', 'string', 'max:20000'],
            'data.map_embed_url' => ['nullable', 'url:https', 'max:1000'],
            'data.notice' => ['nullable', 'string', 'max:1000'],
            'data.gallery' => ['nullable', 'array', 'max:40'],
            'data.gallery.*' => ['integer', 'exists:media,id'],
            'data.rooms' => ['nullable', 'array', 'max:30'],
            'data.rooms.*.title' => ['nullable', 'string', 'max:255'],
            'data.rooms.*.description' => ['nullable', 'string', 'max:2000'],
            'data.rooms.*.image_id' => ['nullable', 'integer', 'exists:media,id'],
            'data.rooms.*.amenities' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $current = $this->route('page');
            $parent = $this->input('parent_id') ? Page::withTrashed()->find($this->input('parent_id')) : null;

            if ($current instanceof Page && $parent && ($parent->id === $current->id || str_starts_with($parent->path.'/', $current->path.'/'))) {
                $validator->errors()->add('parent_id', 'A page cannot be placed under itself or one of its children.');

                return;
            }

            $path = trim(($parent ? $parent->path.'/' : '').$this->input('slug'), '/');
            $conflict = Page::withTrashed()->where('path', $path)
                ->when($current instanceof Page, fn ($q) => $q->whereKeyNot($current->id))
                ->first();

            if ($conflict) {
                $validator->errors()->add('slug', $conflict->trashed()
                    ? 'This URL belongs to a page in the trash. Restore or permanently delete it first.'
                    : 'This URL is already used by "'.$conflict->title.'".');
            }

            if (in_array($path, ['admin', 'search', 'widgets', 'storage', 'build', 'enquiries', 'subscribe', 'sitemap.xml', 'robots.txt', 'up'], true)) {
                $validator->errors()->add('slug', 'This URL is reserved by the system.');
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'faqs.*.question' => 'question',
            'faqs.*.answer' => 'answer',
            'data.rooms.*.title' => 'room title',
            'data.map_embed_url' => 'map embed URL',
        ];
    }
}
