<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\User;
use App\Services\LineMessageService;
use App\Services\GeminiService;
use App\Services\DopaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    use AuthorizesRequests;

    /**
     * ブログ作成
     */
    public function store(
        Request $request,  
        LineMessageService $lineMessageService,
        GeminiService $geminiService,
        DopaService $dopaService,
    )
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
                'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif',
                'max:5120',
            ],
            'category' => 'nullable|string|max:50',
            'seriousness' => 'nullable|integer|min:1|max:5',
            'category_id' => 'nullable|exists:categories,id',
        ]);
    
        $thumbnailPath = null;
    
        if ($request->hasFile('thumbnail')) {
            $filename = Str::uuid() . '.webp';
            $path = 'blogs/' . $filename;
            $image = Image::decode($request->file('thumbnail'));
            $encoded = $image->encodeUsingFileExtension(
                'webp',
                quality: 80
            );
            Storage::disk('public')->put($path, $encoded);
            $thumbnailPath = $path;
        }
    
        $user = $request->user();

        $blog = $user->blogs()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'author' => $validated['author'] ?? null,
            'thumbnail' => $thumbnailPath,
            'category_id' => $validated['category_id'] ?? null,
            'seriousness' => $validated['seriousness'] ?? null,
        ]);

        $users = User::whereNotNull('line_user_id')
            ->where('id', '!=', $user->id)
            ->get();

        foreach ($users as $targetUser) {
            $lineMessageService->send(
                $targetUser->line_user_id,
                "{$user->name}さんがブログを投稿しました！\n"
                . "「{$blog->title}」\n\n"
                . "https://dopa-log.com/blogs/{$blog->id}"
            );
        }

        $blog->load('category');

        try {
            $aiComment = $geminiService->commentOnBlog($blog);

            $aiUser = User::where('email', 'ai@dopa-log.com')->first();

            if ($aiUser && $aiComment) {
                $blog->comments()->create([
                    'user_id' => $aiUser->id,
                    'content' => $aiComment,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('AIコメント生成失敗', [
                'blog_id' => $blog->id,
                'error' => $e->getMessage(),
            ]);
        }

        $dopa = $dopaService->getOrCreate($user);

        $dopaService->reward(
            $dopa,
            'blog_created',
            $blog
        );

        return response()->json($blog, 201);
    }

    public function index(Request $request)
    {
        $query = Blog::with(['user', 'category']);

        if ($request->filled('q')) {
            $keyword = $request->input('q');
    
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('content', 'like', "%{$keyword}%");
            });
        }
    
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
    
        return $query->latest()->get();
    }

    /**
     * ブログ詳細
     */
    public function show(Request $request, Blog $blog)
    {
        $blog->increment('views');
    
        $blog->refresh();
        $blog->load(['user', 'category']);
        $blog->loadCount('favorites');
    
        $isFavorited = false;
    
        if ($request->user()) {
            $isFavorited = $blog->favorites()
                ->where('user_id', $request->user()->id)
                ->exists();
        }
    
        return response()->json([
            ...$blog->toArray(),
            'favorites_count' => $blog->favorites_count,
            'is_favorited' => $isFavorited,
        ]);
    }

    /**
     * ブログ更新
     */
    public function update(Request $request, Blog $blog)
    {
        $this->authorize('update', $blog);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'author' => ['nullable', 'string', 'max:100'],
            // 'thumbnail' => [
            //     'nullable',
            //     'mimes:jpeg,png,jpg,webp,heic,heif',
            //     'max:5120',
            // ],
            'thumbnail' => [
                'nullable',
                'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif',
                'max:5120',
            ],
            'category' => 'nullable|string|max:50',
            'seriousness' => 'nullable|integer|min:1|max:5',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($request->hasFile('thumbnail')) {
            $filename = Str::uuid() . '.webp';
            $path = 'blogs/' . $filename;
            $image = Image::decode($request->file('thumbnail'));

            $encoded = $image->encodeUsingFileExtension(
                'webp',
                quality: 80
            );
            Storage::disk('public')->put($path, $encoded);
            if ($blog->thumbnail) {
                Storage::disk('public')->delete($blog->thumbnail);
            }
            $validated['thumbnail'] = $path;
        }
    

        $blog->update($validated);

        return response()->json($blog);
    }

    /**
     * ブログ削除
     */
    public function destroy(Blog $blog)
    {
        $this->authorize('delete', $blog);

        if ($blog->thumbnail) {
            Storage::disk('public')->delete($blog->thumbnail);
        }

        $blog->delete();

        return response()->json([
            'message' => '記事を削除しました。'
        ], 200);
    }
}