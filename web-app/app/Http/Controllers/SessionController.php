<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Get the session details, including the last login time.
     */
    public function show(Request $request)
    {
        $session = Session::where('id', $request->session()->getId())->first();

        if ($session) {
            return response()->json([
                'session_id' => $session->id,
                'user_id' => $session->user_id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity' => $session->last_activity,
                'last_login_at' => $session->last_login_at, // Include the last login time
            ]);
        }

        return response()->json(['message' => 'Session not found'], 404);
    }

    /**
     * Destroy the session and update last login time.
     */
    public function destroy(Request $request)
    {
        $sessionId = $request->session()->getId();

        $session = Session::where('id', $sessionId)->first();

        if ($session) {
            // Optionally update last login time here, if you wish
            $session->last_login_at = now();  // Update the last login time to now
            $session->save();  // Save the updated session
            $session->delete(); // Delete the session record from the database
        }

        $request->session()->invalidate(); // Invalidate the session

        return response()->json(['message' => 'Session ended successfully']);
    }
}
