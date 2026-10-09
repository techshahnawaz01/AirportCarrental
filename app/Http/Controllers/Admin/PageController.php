<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Models\User;
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
        $type = $request->user()->canManageAllContent() ? ($filters['type'] ?? 'page') : 'post';

        $pages = Page::query()
            ->ofType($type)
            ->when(! $request->user()->canManageAllContent(), fn ($q) => $q->where('author_id', $request->user()->id))
            ->with(['parent:id,title', 'author:id,name'])
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
            'trashedCount' => Page::onlyTrashed()->ofType($type)
                ->when(! $request->user()->canManageAllContent(), fn ($q) => $q->where('author_id', $request->user()->id))->count(),
        ]);
    }

    public function create(Request $request)
    {
        $type = array_key_exists($request->query('type'), config('cms.page_types')) ? $request->query('type') : 'page';
        $type = $request->user()->canManageAllContent() ? $type : 'post';
        $parentSetting = config("cms.page_types.{$type}.parent_setting");

        $page = new Page([
            'type' => $type,
            'template' => config("cms.page_types.{$type}.default_template", 'default'),
            'is_active' => true,
            'allow_comments' => (bool) config("cms.page_types.{$type}.comments"),
            'parent_id' => $parentSetting ? settings($parentSetting) : null,
            'author_id' => $request->user()->id,
        ]);

        return view('admin.pages.form', $this->formData($page));
    }

    public function store(PageRequest $request)
    {
        abort_if(! $request->user()->canManageAllContent() && $request->input('type') !== 'post', 403);

        $page = $this->pages->save(new Page, $this->pageData($request), $request->allFiles());

        return $this->success($page->typeLabel().' created.', ['redirect' => route('admin.pages.edit', $page)], 201);
    }

    public function edit(Page $page)
    {
        $this->authorizePage($page);
        $page->load('faqs', 'featuredImage', 'ogImage');

        return view('admin.pages.form', $this->formData($page));
    }

    public function update(PageRequest $request, Page $page)
    {
        $this->authorizePage($page);
        abort_if(! $request->user()->canManageAllContent() && $request->input('type') !== 'post', 403);

        $this->pages->save($page, $this->pageData($request), $request->allFiles());
        $page->load('featuredImage', 'ogImage');

        $timezone = PageService::timezone($request->input('client_timezone'));
        $message = $page->is_active && $page->published_at?->isFuture()
            ? $page->typeLabel().' saved. Scheduled to go live '.$page->published_at->copy()->timezone($timezone)->format('M j, Y g:i A').' (your time) – not visible until then.'
            : $page->typeLabel().' saved.';

        return $this->success($message, [
            'url' => $page->url(),
            'path' => $page->path,
            'featured_image' => $page->featuredImage?->toPickerArray(),
            'og_image' => $page->ogImage?->toPickerArray(),
        ]);
    }

    public function toggle(Page $page)
    {
        $this->authorizePage($page);
        // Publishing from the list means "live now", even if a future date was set earlier.
        $publishing = ! $page->is_active;
        $page->forceFill([
            'is_active' => $publishing,
            'published_at' => $publishing && (! $page->published_at || $page->published_at->isFuture()) ? now() : ($page->published_at ?? now()),
        ])->save();
        $this->log('updated', ($page->is_active ? 'Published ' : 'Unpublished ').'"'.$page->title.'"', $page);

        return $this->success($page->is_active ? 'Page published.' : 'Page moved to drafts.', ['active' => $page->is_active]);
    }

    public function destroy(Page $page)
    {
        $this->authorizePage($page);

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
        $this->authorizePage($page);
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

    /** Validated fields; only administrators and editors may choose a post's author. */
    private function pageData(PageRequest $request): array
    {
        $data = $request->safe()->except(['featured_image_upload', 'og_image_upload']);

        if (! $request->user()->canManageAllContent()) {
            $data['author_id'] = $request->user()->id;
        } elseif (blank($data['author_id'] ?? null)) {
            unset($data['author_id']);
        }

        return $data;
    }

    /** Authors can only work on their own blog posts. */
    private function authorizePage(Page $page): void
    {
        $user = auth()->user();

        abort_if(! $user->canManageAllContent() && ($page->type !== 'post' || $page->author_id !== $user->id), 403);
    }

    private function formData(Page $page): array
    {
        return [
            'page' => $page,
            'authors' => auth()->user()->canManageAllContent()
                ? User::where('is_active', true)->orderBy('name')->pluck('name', 'id')->all()
                : [],
            'typeConfig' => config("cms.page_types.{$page->type}"),
            'parents' => Page::parentOptions($page->exists ? $page : null),
            'templates' => config('cms.templates'),
            'types' => collect(config('cms.page_types'))->map(fn ($t) => $t['singular'])->all(),
        ];
    }
}
