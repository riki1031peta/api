<?php

namespace App\Notifications;

use App\Models\Blog;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentReplied extends Notification
{
    use Queueable;

    public function __construct(
        public Blog $blog,
        public User $user,
        public Comment $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'comment_reply',
            'blog_id' => $this->blog->id,
            'blog_title' => $this->blog->title,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'comment_id' => $this->comment->id,
            'message' => "{$this->user->name}さんがあなたのコメントに返信しました。",
        ];
    }
}