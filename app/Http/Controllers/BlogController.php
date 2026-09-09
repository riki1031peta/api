<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    /**
     * ブログ作成
     */
    public function store(Request $request)
    {
        \Log::info('BLOG STORE', [
            'all' => $request->except('thumbnail'),
            'has_thumbnail' => $request->hasFile('thumbnail'),
            'thumbnail' => $request->file('thumbnail')?->getClientOriginalName(),
        ]);
    
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'author' => ['nullable', 'string', 'max:100'],
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120',
            ],
        ]);
    
        $thumbnailPath = null;
    
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')
                ->store('blogs', 'public');
        }
    
        $blog = Blog::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'author' => $validated['author'] ?? null,
            'thumbnail' => $thumbnailPath,
        ]);

        return response()->json($blog, 201);
    }

    /**
     * ブログ一覧
     */
    public function index()
    {
        return response()->json(
            Blog::latest()->get()
        );
    }

    /**
     * ブログ詳細
     */
    public function show(Blog $blog)
    {
        return response()->json($blog);
    }

    /**
     * ブログ更新
     */
    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'author' => ['nullable', 'string', 'max:100'],
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120',
            ],
        ]);
    
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')
                ->store('blogs', 'public');
    
            $validated['thumbnail'] = $thumbnailPath;
        }

        $blog->save();

        return response()->json($blog);
    }

    /**
     * ブログ削除
     */
    public function destroy(Blog $blog)
    {
        if ($blog->thumbnail) {
            Storage::disk('public')->delete($blog->thumbnail);
        }

        $blog->delete();

        return response()->json([
            'message' => '記事を削除しました。'
        ], 200);
    }
}