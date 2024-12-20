<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Notification;
use App\Models\Comment;
use App\Models\User;
use App\Models\Claim;


class FoundItem extends Model
{
    use HasFactory;

    protected $table = 'found_items';

    protected $fillable = [
        'item_name', 'found_date', 'facebook_link', 'contact_number', 
        'description', 'category', 'location', 'image_url', 'user_id'
    ];

    protected $with = ['user'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the comments for the FoundItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function notifyUser()
    {
        if ($this->user_id) {
            $notificationData = [
                'title' => 'New Found Item Posted',
                'message' => 'A new found item has been posted: ' . $this->item_name,
                'item_id' => $this->id,
                'item_type' => 'found',
                'item_name' => $this->item_name
            ];

            \Log::info('Creating found item notification:', [
                'user_id' => $this->user_id,
                'item_name' => $this->item_name,
                'data' => $notificationData
            ]);

            Notification::create([
                'user_id' => $this->user_id,
                'type' => 'found_item',
                'data' => $notificationData
            ]);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($item) {
            \Log::info('Retrieved found item:', [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'item_name' => $item->item_name
            ]);
        });
    }

    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }
}
