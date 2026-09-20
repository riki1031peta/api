<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Notifications\BlogCommented;
use App\Services\LineMessageService;
use App\Models\Comment;
use App\Notifications\CommentReplied;
use App\Services\DopaService;

class CommentController extends Controller
{
    public function index(Blog $blog)
    {
        $comments = $blog->comments()
            ->whereNull('parent_id')
            ->with([
                'user',
                'replies.user',
            ])
            ->latest()
            ->get();

        return response()->json($comments);
    }

    public function store(
        Request $request,
        Blog $blog,
        LineMessageService $lineMessageService,
        DopaService $dopaService,
    )
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:comments,id',
            ],
        ]);

        $user = $request->user();
        $parent = null;

        if (!empty($validated['parent_id'])) {
            $parent = Comment::where('id', $validated['parent_id'])
                ->where('blog_id', $blog->id)
                ->firstOrFail();
        }

        $comment = $user->comments()->create([
            'blog_id' => $blog->id,
            'parent_id' => $parent?->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');
        $owner = $blog->user;

        if ($parent) {
            $targetUser = $parent->user;
            if ($targetUser && $targetUser->id !== $user->id) {
                $targetUser->notify(
                    new CommentReplied(
                        $blog,
                        $user,
                        $comment
                    )
                );
    
                $lineMessageService->send(
                    $targetUser->line_user_id,
                    "{$user->name}さんがあなたのコメントに返信しました！\n\n"
                    . "「{$comment->content}」\n\n"
                    . "https://dopa-log.com/blogs/{$blog->id}"
                );
            }
        } else {
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
        }

        $dopa = $dopaService->getOrCreate($request->user());

        $dopaService->reward(
            $dopa,
            'comment_created',
            $comment
        );

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