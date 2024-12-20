<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Log user activity.
     *
     * @param  string  $action
     * @return void
     */
    public function logUserActivity($action, $creatorUserID = null)
    {
        if (Auth::check()) {
            // If no creator ID is passed, default to the authenticated user's ID
            $userID = $creatorUserID ?? Auth::id();

            // Log the activity with the user ID
            ActivityLog::create([
                'user_id' => $userID, 
                'action' => $action, 
                'action_time' => now(), 
                'ip_address' => request()->getClientIp(),
                'user_agent' => request()->header('User-Agent'), 
            ]);
        }
    }
}
