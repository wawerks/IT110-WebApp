<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Claim;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin');
    }

    public function dashboard()
    {
        $totalUsers = User::count();
        $lostItems = LostItem::count();
        $foundItems = FoundItem::count();
        $usersWithClaimsCount = DB::table('users')
            ->select('users.id', 'users.name', DB::raw('get_user_claims_count(users.id) as claim_count'))
            ->get();

        // Extract total claims count from the query result
        $claims = $usersWithClaimsCount->sum('claim_count');

        return response()->json([
            'totalUsers' => $totalUsers,
            'lostItems' => $lostItems,
            'foundItems' => $foundItems,
            'claims' => $claims,
        ]);
    }

    public function users()
    {
        $users = User::all();

        return response()->json([
            'users' => $users
        ]);
    }

    public function usersLog()
    {
        $activityLog = DB::table('User_Activity_Report')
            ->select('name', 'action', 'action_time', 'ip_address', 'id')
            ->orderBy('action_time', 'DESC')
            ->get();

        return response()->json(['logs' => $activityLog]);
    }

    public function reportedItems()
    {
        $lostItems = LostItem::with('user:id,name')->get();
        $foundItems = FoundItem::with('user:id,name', 'claim:claim_id,claim_status')->get();

        return response()->json([
            'lostItems' => $lostItems,
            'foundItems' => $foundItems
        ]);
    }

    public function getID()
    {
        $user = Auth::user();

        return response()->json([
            'id' => $user->id,
            'last_login_at' => $user->last_login_at
        ]);
    }

    public function store(Request $request)
    {
        // Validate Request Data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,user'
        ]);
    
        try {
            // Check if a user is logged in
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthorized. You must be logged in to create a user.'
                ], 401);
            }
    
            // Create New User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role']
            ]);
    
        return response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('User Creation Failed: ' . $e->getMessage());
    
                return response()->json([
                'error' => 'Failed to create user. Please try again.'
            ], 500);
            }
    }
    
}
