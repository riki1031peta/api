<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\JsonResponse;

class SitemapController extends Controller
{
    public function index(): JsonResponse
    {
        $blogs = Blog::select('id', 'updated_at')->get();

        $urls = $blogs->map(function ($blog) {
            return [
                'loc' => "/blogs/{$blog->id}",
                'lastmod' => $blog->updated_at->toIso8601String(),
            ];
        });

        return response()->json($urls);
    }
}