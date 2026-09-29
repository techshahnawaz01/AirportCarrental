<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreCommentRequest;
use App\Models\Page;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Page $page)
    {
        abort_unless($page->allow_comments && $page->isPublished(), 404);

        $page->comments()->create($request->safe()->only(['name', 'email', 'rating', 'body']) + [
            'ip_address' => $request->ip(),
            'is_approved' => false,
        ]);

        $message = 'Thanks! Your comment has been submitted and will appear once approved.';

        return $request->expectsJson() ? $this->success($message, [], 201) : back()->with('success', $message);
    }
}
