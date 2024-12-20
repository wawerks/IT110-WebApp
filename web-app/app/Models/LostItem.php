<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;
use App\Models\Comment;

class LostItem extends Model
{
    use HasFactory;

    protected $table = 'lost_items';

    protected $fillable = [
        'item_name', 'lost_date', 'facebook_link', 'contact_number', 
        'description', 'category', 'location', 'image_url', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function claim()
    {
        return $this->hasOne(Claim::class, 'lost_item_id');
    }
    
    /**
     * Get all of the comments for the LostItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function notifyUser()
    {
        // Create a new notification
        Notification::create([
            'type' => 'lost_item_reported',
            'data' => ['item_name' => $this->item_name],
            'user_id' => $this->user_id,
        ]);
    }
}
