<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_name',
        'category',
        'description',
        'facebook_link',
        'contact_number',
        'image_url',
        'isFound',
        'lost_date',
        'found_date'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'isFound' => 'boolean',
        'lost_date' => 'datetime',
        'found_date' => 'datetime',
    ];
}
