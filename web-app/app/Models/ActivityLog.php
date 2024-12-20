<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_log';
    protected $primaryKey = 'id';
    public $timestamps = false; // Since we're using action_time instead

    protected $fillable = [
        'user_id',
        'action',
        'action_time',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'action_time' => 'datetime',
    ];

    protected $with = ['user']; // Always load the user relationship

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
