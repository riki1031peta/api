<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Blog;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Blog::create([
            'title' => '初めての記事',
            'content' => 'これはテスト記事です。'
        ]);
    
        Blog::create([
            'title' => 'Nuxtについて',
            'content' => 'Nuxt3はとても便利です。'
        ]);
    }
}
