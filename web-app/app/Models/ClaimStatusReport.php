<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaimStatusReport extends Model
{
    protected $table = 'Claim_Status_Report';  // Name of the view
    public $timestamps = false;  // Disable timestamps as views do not have them
}