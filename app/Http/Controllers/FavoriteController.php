<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(Request $request, Blog $blog)
    {
        $favorite = $request->user()->favorites()->firstOrCreate([
            'blog_id' => $blog->id,
        ]);

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