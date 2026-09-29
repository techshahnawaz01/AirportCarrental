<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Page;
use App\Models\Redirect;
use App\Services\SeoService;
use App\Services\ShortcodeRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    public function __construct(
        private readonly ShortcodeRenderer $shortcodes,
        private readonly SeoService $seo,
    ) {}

    public function home(Request $request): Response
    {
        $page = Page::find(settings('general.home_page_id'));

        if (! $page || (! $page->isPublished() && ! $this->canPreview($request))) {
            return response()->view('frontend.no-home', ['seo' => $this->seo->forPage(null)]);
        }

        return $this->render($request, $page);
    }

    public function show(Request $request, string $path): Response
    {
        $path = trim($path, '/');
        $page = Page::where('path', $path)->first();

        if ($page && ($page->isPublished() || $this->canPreview($request))) {
            return $page->isHome() ? redirect('/', 301) : $this->render($request, $page);
        }

        if ($redirect = Redirect::where('from_path', $path)->first()) {
            $redirect->increment('hits');

            return redirect($redirect->to_url, $redirect->status_code);
        }

        // Legacy URLs: an unknown prefix in front of a unique slug redirects to the canonical URL.
        $slug = basename($path);
        $matches = Page::published()->where('slug', $slug)->limit(2)->get(['id', 'path']);
        if ($matches->count() === 1 && $matches->first()->path !== $path) {
            return redirect($matches->first()->url(), 301);
        }

        abort(404);
    }

    private function render(Request $request, Page $page): Response
    {
        $page->load(['faqs', 'featuredImage', 'ogImage', 'parent.parent']);

        $data = [
            'page' => $page,
            'content' => $this->shortcodes->render($page->content, $page),
            'seo' => $this->seo->forPage($page, $request->query('page') > 1 ? ['noindex' => true] : []),
            'isPreview' => ! $page->isPublished(),
        ];

        $view = $page->templateView();

        if ($view === 'frontend.templates.listing') {
            $data['children'] = $page->children()->published()->with('featuredImage')
                ->orderBy('sort_order')->latest('published_at')->latest('id')
                ->paginate(config('cms.pagination.frontend'))->withQueryString();
        }

        if (in_array($view, ['frontend.templates.post', 'frontend.templates.default'], true)) {
            // Only suggest pages that have an image and are meant to be found (skips legal/utility pages).
            $data['related'] = Page::published()->with('featuredImage')
                ->whereNotIn('id', array_filter([$page->id, (int) settings('general.home_page_id')]))
                ->whereNotNull('featured_image_id')
                ->where('noindex', false)
                ->when($page->parent_id, fn ($q) => $q->where('parent_id', $page->parent_id), fn ($q) => $q->where('type', $page->type)->whereNull('parent_id'))
                ->latest('published_at')->latest('id')->limit(5)->get();
        }

        if ($page->allow_comments) {
            $data['comments'] = $page->comments()->approved()->latest()->get();
        }

        if ($view === 'frontend.templates.hotel') {
            $data['gallery'] = Media::whereIn('id', (array) $page->meta('gallery', []))->get()->sortBy(
                fn ($m) => array_search($m->id, (array) $page->meta('gallery', []))
            );
            $data['roomImages'] = Media::whereIn('id', collect($page->meta('rooms', []))->pluck('image_id')->filter())->get()->keyBy('id');
        }

        return response()->view($view, $data);
    }

    private function canPreview(Request $request): bool
    {
        return $request->user() !== null && Gate::allows('access-admin');
    }
}
