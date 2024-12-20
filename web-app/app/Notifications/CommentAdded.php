<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class CommentAdded extends Notification
{
    protected $comment;
    protected $item;

    public function __construct($comment, $item)
    {
        $this->comment = $comment;
        $this->item = $item;
    }

    public function via($notifiable)
    {
        return ['database'];  // Save the notification to the database
    }

    public function toDatabase($notifiable)
    {
        return [
            'comment_id' => $this->comment->id,
            'comment_text' => $this->comment->text,
            'post_id' => $this->item->id,
            'post_title' => $this->item->item_name,  // Assuming the 'item_name' field exists
            'user_id' => $this->comment->user_id,
            'user_name' => $this->comment->user->name,
        ];
    }
}
