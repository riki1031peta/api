<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Notifications\BlogCommented;

class CommentController extends Controller
{
    public function index(Blog $blog)
    {
        return response()->json(
            $blog->comments()
                ->with('user')
                ->latest()
                ->get()
        );
    }

    public function store(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $comment = $request->user()->comments()->create([
            'blog_id' => $blog->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        $owner = $blog->user;

        if ($owner && $owner->id !== $request->user()->id) {
            $owner->notify(
                new BlogCommented($blog, $request->user(), $comment)
            );
        }

        return response()->json($comment, 201);
    }

    public function destroy(Request $request, Comment $comment)
    {
        if (
            $request->user()->id !== $comment->user_id &&
            !$request->user()->isAdmin()
        ) {
            abort(403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'コメントを削除しました。',
        ]);
    }
}