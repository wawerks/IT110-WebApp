<?php

// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Comment;
use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::whereIn(DB::raw('(data->>\'item_id\')::bigint'), function ($query) {
            $query->select('id')
                ->from('lost_items')
                ->where('user_id', Auth::id())  // Checking if user_id matches the authenticated user's ID
                ->whereNotNull('id');
        })
        ->orWhereIn(DB::raw("(data->>'item_id')::bigint"), function ($query) {
            $query->select('id')
                ->from('found_items')
                ->where('user_id', Auth::id()) // Checking if user_id matches the authenticated user's ID
                ->whereRaw("(data->>'item_type') = 'found'") // Correct condition for JSON field
                ->whereNotNull('id');
        })
        ->orWhereIn(DB::raw("user_id"), function ($query) {
            // Select item_ids from claims where claim_user_id matches user_id
            $query->select('user_id')
            ->from('claims')
            ->whereRaw("(data->>'claim_user_id')::bigint = ?", [Auth::id()]) // Correct parameter binding
            ->whereNotNull('item_id');
        })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                // Get the data - it's already an array due to the cast in the model
                $data = $notification->data;
    
                // Get the comment author's name if this is a comment notification
                $userName = 'Unknown User';
                $claimUserId = null;
                if (isset($data['comment_text'])) {
                    $commentUser = Comment::where('text', $data['comment_text'])
                        ->with('user') // Assuming there's a `user` relationship in Comment
                        ->first();
    
                    // If user exists, retrieve name
                    $userName = $commentUser && $commentUser->user ? $commentUser->user->name : 'Unknown User';
                }
    
                $claimId = null;
                if (isset($data['claim_id'])) {
                    // Find the claim by ID
                    $claim = Claim::find($data['claim_id']);
                    
                    // If the claim exists, retrieve the claim_id and the name of the user who created it
                    $claimId = $claim ? $claim->claim_id : null;
                    $userName = $claim && $claim->user ? $claim->user->name : 'Unknown User';
    
                    // Fetch claim's user_id
                    $claimUserId = $claim ? $claim->user_id : null;  // Getting the user_id of the claim owner
                }
    
                // Format the date using Carbon
                $createdAt = $notification->created_at ? $notification->created_at->format('M d, Y, h:i A') : null;
    
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $data,
                    'read' => (bool) $notification->read_at,
                    'read_at' => $notification->read_at,
                    'created_at' => $createdAt,
                    'userName' => $userName,
                    'claim_id' => $claimId,   // Adding claim_id
                    'claim_user_id' => $claimUserId  // Adding claim_user_id
                ];
            });
    
        return response()->json(['notifications' => $notifications]);
    }
    
    
    
    
    

    public function markAsRead(Request $request, $id)
    {
        try {
            \Log::info('Marking notification as read', ['notification_id' => $id, 'user_id' => Auth::id()]);
    
            // Retrieve the notification by ID and user
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
    
            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }
    
            // If the notification is already marked as read, no further action needed
            if ($notification->read_at) {
                return response()->noContent();
            }
    
            // Mark the notification as read
            $notification->read_at = Carbon::now();
            $notification->save();
    
            return response()->json([
                'message' => 'Notification marked as read',
                'notification' => $notification
            ], 200);
    
        } catch (\Exception $e) {
            \Log::error('Error marking notification as read:', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to mark notification as read'], 500);
        }
    }
    
    

    public function markAllAsRead(Request $request)
    {
        try {
            $notifications = Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->get();

            $now = Carbon::now()->toDateTimeString();
            foreach ($notifications as $notification) {
                $notification->read_at = $now;
                $notification->save();
            }

            return response()->json(['message' => 'All notifications marked as read']);
        } catch (\Exception $e) {
            \Log::error('Error marking all notifications as read:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to mark notifications as read'], 500);
        }
    }

    public function getUnreadCount(Request $request)
    {
        $count = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    // Store a new notification
    public function store(Request $request)
    {
        $notification = new Notification();
        $notification->user_id = $request->user()->id;
        $notification->type = $request->type;  
        $notification->data = $request->data;  
        $notification->notifiable_type = $request->notifiable_type;  
        $notification->notifiable_id = $request->notifiable_id;  
        $notification->save();

        return response()->json(['message' => 'Notification created successfully!']);
    }
}
