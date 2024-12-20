<?php

// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Notification extends Model
{
    use HasFactory;

    // The attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at'
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    // Define a relationship with the user who will receive the notification
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->read_at = now();
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            \Log::info('Creating notification:', [
                'user_id' => $notification->user_id,
                'type' => $notification->type,
                'data' => $notification->data
            ]);
        });

        static::created(function ($notification) {
            \Log::info('Notification created:', [
                'id' => $notification->id,
                'user_id' => $notification->user_id,
                'type' => $notification->type,
                'data' => $notification->data
            ]);
        });
    }
}
