<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PageController extends AdminController
{
    public function __construct(private readonly PageService $pages) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::in(array_keys(config('cms.page_types')))],
            'status' => ['nullable', Rule::in(['published', 'draft', 'trashed'])],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['updated', 'title', 'created'])],
        ]);
        $type = $filters['type'] ?? 'page';

        $pages = Page::query()
            ->ofType($type)
            ->with('parent:id,title')
            ->search($filters['q'] ?? null)
            ->when(($filters['status'] ?? null) === 'published', fn ($q) => $q->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'draft', fn ($q) => $q->where('is_active', false))
            ->when(($filters['status'] ?? null) === 'trashed', fn ($q) => $q->onlyTrashed())
            ->when(($filters['sort'] ?? 'updated') === 'title', fn ($q) => $q->orderBy('title'), fn ($q) => $q->latest(($filters['sort'] ?? null) === 'created' ? 'created_at' : 'updated_at'))
            ->paginate(config('cms.pagination.admin'))
            ->withQueryString();

        return $this->listing($request, 'admin.pages.index', 'admin.pages._table', [
            'pages' => $pages,
            'type' => $type,
            'typeConfig' => config("cms.page_types.{$type}"),
            'filters' => $filters,
            'trashedCount' => Page::onlyTrashed()->ofType($type)->count(),
        ]);
    }

    public function create(Request $request)
    {
        $type = array_key_exists($request->query('type'), config('cms.page_types')) ? $request->query('type') : 'page';
        $parentSetting = config("cms.page_types.{$type}.parent_setting");

        $page = new Page([
            'type' => $type,
            'template' => config("cms.page_types.{$type}.default_template", 'default'),
            'is_active' => true,
            'allow_comments' => (bool) config("cms.page_types.{$type}.comments"),
            'parent_id' => $parentSetting ? settings($parentSetting) : null,
        ]);

        return view('admin.pages.form', $this->formData($page));
    }

    public function store(PageRequest $request)
    {
        $page = $this->pages->save(new Page, $request->safe()->except(['featured_image_upload', 'og_image_upload']), $request->allFiles());

        return $this->success($page->typeLabel().' created.', ['redirect' => route('admin.pages.edit', $page)], 201);
    }

    public function edit(Page $page)
    {
        $page->load('faqs', 'featuredImage', 'ogImage');

        return view('admin.pages.form', $this->formData($page));
    }

    public function update(PageRequest $request, Page $page)
    {
        $this->pages->save($page, $request->safe()->except(['featured_image_upload', 'og_image_upload']), $request->allFiles());
        $page->load('featuredImage', 'ogImage');

        return $this->success($page->typeLabel().' saved.', [
            'url' => $page->url(),
            'path' => $page->path,
            'featured_image' => $page->featuredImage?->toPickerArray(),
            'og_image' => $page->ogImage?->toPickerArray(),
        ]);
    }

    public function toggle(Page $page)
    {
        $page->forceFill([
            'is_active' => ! $page->is_active,
            'published_at' => $page->published_at ?? now(),
        ])->save();
        $this->log('updated', ($page->is_active ? 'Published ' : 'Unpublished ').'"'.$page->title.'"', $page);

        return $this->success($page->is_active ? 'Page published.' : 'Page moved to drafts.', ['active' => $page->is_active]);
    }

    public function destroy(Page $page)
    {
        if ($page->isHome()) {
            return $this->failure('The home page cannot be deleted. Choose another home page in Settings first.', 422);
        }

        $page->delete();
        $this->log('deleted', 'Moved "'.$page->title.'" to trash', $page);

        return $this->success('Moved to trash.');
    }

    public function restore(int $id)
    {
        $page = Page::onlyTrashed()->findOrFail($id);
        $page->restore();
        $this->log('restored', 'Restored "'.$page->title.'"', $page);

        return $this->success('Restored.');
    }

    public function forceDelete(int $id)
    {
        Gate::authorize('manage-site');

        $page = Page::onlyTrashed()->findOrFail($id);
        $title = $page->title;
        $page->forceDelete();
        $this->log('deleted', 'Permanently deleted "'.$title.'"');

        return $this->success('Permanently deleted.');
    }

    private function formData(Page $page): array
    {
        return [
            'page' => $page,
            'typeConfig' => config("cms.page_types.{$page->type}"),
            'parents' => Page::parentOptions($page->exists ? $page : null),
            'templates' => config('cms.templates'),
            'types' => collect(config('cms.page_types'))->map(fn ($t) => $t['singular'])->all(),
        ];
    }
}
