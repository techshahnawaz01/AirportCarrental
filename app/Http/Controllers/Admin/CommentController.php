<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends AdminController
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $comments = Comment::with('page:id,title,path')
            ->when(($filters['status'] ?? null) === 'pending', fn ($q) => $q->where('is_approved', false))
            ->when(($filters['status'] ?? null) === 'approved', fn ($q) => $q->where('is_approved', true))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('body', 'like', "%{$term}%")))
            ->latest()
            ->paginate(config('cms.pagination.admin'))
            ->withQueryString();

        return $this->listing($request, 'admin.comments.index', 'admin.comments._table', compact('comments', 'filters'));
    }

    public function toggle(Comment $comment)
    {
        $comment->update(['is_approved' => ! $comment->is_approved]);
        $this->log('moderated', ($comment->is_approved ? 'Approved' : 'Unapproved').' a comment by '.$comment->name, $comment);

        return $this->success($comment->is_approved ? 'Comment approved.' : 'Comment hidden.', ['active' => $comment->is_approved]);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        $this->log('deleted', 'Deleted a comment by '.$comment->name);

        return $this->success('Comment deleted.');
    }
}
