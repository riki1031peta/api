<?php

namespace App\Notifications;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BlogFavorited extends Notification
{
    use Queueable;

    public function __construct(
        public Blog $blog,
        public User $user,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'favorite',
            'blog_id' => $this->blog->id,
            'blog_title' => $this->blog->title,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'message' => "{$this->user->name}さんがあなたの記事にいいねしました。",
        ];
    }
}