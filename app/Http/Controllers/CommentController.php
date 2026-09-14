<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Notifications\BlogCommented;
use App\Services\LineMessageService;

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

    public function store(
        Request $request,
        Blog $blog,
        LineMessageService $lineMessageService,
    )
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        $comment = $user->comments()->create([
            'blog_id' => $blog->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        $owner = $blog->user;

        if ($owner && $owner->id !== $user->id) {
            $owner->notify(
                new BlogCommented(
                    $blog,
                    $user,
                    $comment
                )
            );
    
            $lineMessageService->send(
                $owner->line_user_id,
                "{$user->name}さんがあなたの記事「{$blog->title}」にコメントしました！\n\n"
                . "「{$comment->content}」\n\n"
                . "https://dopa-log.com/blogs/{$blog->id}"
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