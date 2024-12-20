<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    use HasFactory;
    protected $primaryKey = 'claim_id'; 
    protected $table = 'claims';

    protected $fillable = [
        'item_id',
        'user_id',
        'claim_status',
        'proof_of_ownership',
        'submission_date',
    ];

    /**
     * Get the found item associated with the claim.
     */
    public function foundItem()
    {
        return $this->belongsTo(FoundItem::class, 'item_id');
    }

    /**
     * Get the user associated with the claim.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the formatted proof of ownership URL.
     */
    public function getProofOfOwnershipUrlAttribute()
    {
        return asset('storage/' . $this->proof_of_ownership);
    }

    /**
     * Accessor to format submission date to a readable format.
     */
    public function getSubmissionDateAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    /**
     * Mutator to set submission date in a consistent format.
     */
    public function setSubmissionDateAttribute($value)
    {
        $this->attributes['submission_date'] = \Carbon\Carbon::parse($value)->toDateString();
    }
}