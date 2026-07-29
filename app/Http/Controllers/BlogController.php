<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{
    public function store(Request $request)
    {
        $blog = Blog::create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json($blog);
    }

    public function index()
    {
        return Blog::latest()->get();
    }

    public function show(Blog $blog)
    {
        return response()->json($blog);
    }
    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $blog->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return response()->json($blog);
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();

        return response()->json([
            'message' => '記事を削除しました。'
        ], 200);
    }
}
