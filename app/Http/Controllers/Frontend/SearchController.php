<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function __invoke(Request $request, SeoService $seo)
    {
        $term = Str::limit(trim((string) $request->query('q')), 100, '');

        $query = Page::published()
            ->where('noindex', false)
            ->with('featuredImage')
            ->when(mb_strlen($term) >= 2, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', '%'.$term.'%')
                ->orWhere('excerpt', 'like', '%'.$term.'%')
                ->orWhere('content', 'like', '%'.$term.'%')), fn ($q) => $q->whereRaw('1 = 0'))
            ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ['%'.$term.'%'])
            ->latest('updated_at');

        if ($request->expectsJson()) {
            return $this->success('Results loaded.', [
                'results' => $query->limit(6)->get()->map(fn (Page $page) => [
                    'title' => $page->title,
                    'url' => $page->url(),
                    'type' => $page->typeLabel(),
                    'excerpt' => $page->summary(14),
                ]),
            ]);
        }

        return view('frontend.search', [
            'term' => $term,
            'results' => $query->paginate(config('cms.pagination.frontend'))->withQueryString(),
            'seo' => $seo->forPage(null, ['title' => $term ? "Search results for \"{$term}\"" : 'Search', 'noindex' => true]),
        ]);
    }
}
