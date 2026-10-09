<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\User;
use App\Services\SeoService;
use Illuminate\Support\Str;

/**
 * Public author archive (/author/{slug}): the author's profile and their published blog posts.
 * Only users with at least one published post have a page, so staff accounts stay private.
 */
class AuthorController extends Controller
{
    public function __invoke(string $slug, SeoService $seo)
    {
        $author = User::withPublicProfile()->with('avatar')->where('slug', $slug)->firstOrFail();

        $posts = Page::published()->where('type', 'post')->where('author_id', $author->id)
            ->with(['featuredImage', 'author.avatar'])
            ->latest('published_at')->latest('id')
            ->paginate(config('cms.pagination.frontend'))
            ->withQueryString();

        $siteName = settings('branding.site_name');

        return view('frontend.author', [
            'author' => $author,
            'posts' => $posts,
            'seo' => $seo->forPage(null, [
                'title' => $author->name.($author->job_title ? ', '.$author->job_title : '').' – '.$siteName,
                'description' => $author->bio ? Str::limit(strip_tags($author->bio), 160) : 'Articles by '.$author->name.' on '.$siteName.'.',
                'image' => $author->avatar?->url,
                'canonical' => $author->url(),
                'noindex' => $posts->currentPage() > 1,
                'type' => 'profile',
                'schema' => [
                    [
                        '@type' => 'ProfilePage',
                        'url' => $author->url(),
                        'mainEntity' => $seo->personSchema($author),
                    ],
                    [
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => [
                            ['@type' => 'ListItem', 'position' => 1, 'name' => $siteName, 'item' => url('/')],
                            ['@type' => 'ListItem', 'position' => 2, 'name' => $author->name, 'item' => $author->url()],
                        ],
                    ],
                ],
            ]),
        ]);
    }
}
