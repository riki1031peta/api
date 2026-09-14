<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

class BlogImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => [
                'required',
                'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif',
                'max:5120',
            ],
        ]);

        $filename = Str::uuid() . '.webp';
        $path = 'blogs/content/' . $filename;

        $image = Image::decode($request->file('image'));
        $encoded = $image->encodeUsingFileExtension(
            'webp',
            quality: 80
        );

        Storage::disk('public')->put($path, $encoded);

        return response()->json([
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }
}