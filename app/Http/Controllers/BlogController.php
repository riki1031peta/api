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
            'author' => ['required', 'string', 'max:100'],
            'thumbnail' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120',
            ],
        ]);

        // $validated = $request->validate([
        //     'title' => ['required', 'string', 'max:255'],
        //     'content' => ['required', 'string'],
        //     'author' => ['required', 'string', 'max:100'],
        //     'thumbnail' => [
        //         'required',
        //         'image',
        //         'mimes:jpeg,png,jpg,webp',
        //         'max:5120',
        //     ],
        // ]);

        $thumbnailPath = $request->file('thumbnail')
            ->store('blogs', 'public');

        $blog = Blog::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'author' => $validated['author'],
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
            'author' => ['required', 'string', 'max:100'],
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120',
            ],
        ]);

        // 新しいサムネイルがアップロードされた場合
        if ($request->hasFile('thumbnail')) {
            if ($blog->thumbnail) {
                Storage::disk('public')->delete($blog->thumbnail);
            }

            $thumbnailPath = $request->file('thumbnail')
                ->store('blogs', 'public');

            $blog->thumbnail = $thumbnailPath;
        }

        $blog->title = $validated['title'];
        $blog->content = $validated['content'];
        $blog->author = $validated['author'];

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