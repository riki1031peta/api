<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\HasFactory;
// use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'content',
        'author',
        'thumbnail',
    ];

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // public function admin()
    // {
    //     return $this->belongsTo(Admin::class);
    // }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
