<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Notifications\BlogFavorited;
use App\Services\LineMessageService;
use App\Services\DopaService;

class FavoriteController extends Controller
{
    public function store(
        Request $request,
        Blog $blog,
        LineMessageService $lineMessageService,
        DopaService $dopaService,
    )
    {
        $user = $request->user();

        $favorite = $user->favorites()->firstOrCreate([
            'blog_id' => $blog->id,
        ]);

        if ($favorite->wasRecentlyCreated) {
            $owner = $blog->user;
    
            if ($owner && $owner->id !== $user->id) {
                $owner->notify(
                    new BlogFavorited($blog, $user)
                );
    
                // $lineMessageService->send(
                //     $owner->line_user_id,
                //     "{$user->name}さんがあなたの記事「{$blog->title}」にいいねしました！\n"
                //     . "https://dopa-log.com/blogs/{$blog->id}"
                // );
            }
        }

        $dopa = $dopaService->getOrCreate($request->user());

        $dopaService->reward(
            $dopa,
            'favorite_created',
            $favorite
        );

        return response()->json([
            'message' => 'いいねしました。',
            'favorite' => $favorite,
        ], 201);
    }

    public function destroy(Request $request, Blog $blog)
    {
        $request->user()
            ->favorites()
            ->where('blog_id', $blog->id)
            ->delete();

        return response()->json([
            'message' => 'いいねを取り消しました。',
        ]);
    }
}