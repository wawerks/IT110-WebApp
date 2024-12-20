<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;

class SocialiteController extends Controller
{
    public function googleLogin()
    {
        return Socialite::driver('google')
                        ->scopes(['profile', 'email'])
                        ->redirect();
    }
    
    public function googleAuthentication()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user exists
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                // Create new user with only the fields that exist in the database
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => Hash::make(Str::random(16)), // Random password for security
                    'email_verified_at' => now(), // Mark email as verified since it's from Google
                ]);

                \Log::info('New Google user created:', ['email' => $googleUser->email]);
            }

            Auth::login($user);
            \Log::info('Google user logged in:', ['email' => $user->email]);

            return redirect('/dashboard');

        } catch (\Exception $e) {
            \Log::error('Google authentication error:', ['error' => $e->getMessage()]);
            return redirect('/login')->with('error', 'Google authentication failed. Please try again.');
        }
    }
}