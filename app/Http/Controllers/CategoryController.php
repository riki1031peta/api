<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::orderBy('id')->get();
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        return Category::create($validated);
    }

    public function update(Request $request, Category $category)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,' . $category->id,
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category->update($validated);

        return $category;
    }

    public function destroy(Request $request, Category $category)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $category->delete();

        return response()->noContent();
    }
}
