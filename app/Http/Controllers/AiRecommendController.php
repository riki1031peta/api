<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class AiRecommendController extends Controller
{
    public function recommend(
        Request $request,
        GeminiService $geminiService
    ) {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $blogs = Blog::with('category')
            ->latest()
            // ->limit(50)
            ->get();

        if ($blogs->isEmpty()) {
            return response()->json([
                'message' => 'おすすめできる記事がまだありません。',
            ], 404);
        }

        $candidates = $blogs->map(function ($blog) {
            return [
                'id' => $blog->id,
                'title' => $blog->title,
                'category' => $blog->category?->name ?? 'なし',
                'seriousness' => $blog->seriousness,
                'content' => mb_substr(
                    strip_tags($blog->content),
                    0,
                    300
                ),
            ];
        })->toArray();

        $result = $geminiService->recommend(
            $validated['message'],
            $candidates
        );

        $blog = Blog::with(['user', 'category'])
            ->whereIn('id', $blogs->pluck('id'))
            ->find($result['blog_id'] ?? null);

        if (!$blog) {
            return response()->json([
                'message' => '記事を選べませんでした。',
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'blog' => $blog,
        ]);
    }
}