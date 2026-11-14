<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function redirect(Request $request)
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the OAuth callback from Google.
     *
     * Strategy:
     *   - If the Google account is already linked to a landlord here,
     *     log them in.
     *   - Otherwise, if a user with the same email exists, link the Google
     *     ID and log them in.
     *   - Otherwise, create a fresh landlord account with the data Google
     *     returned, mark the email verified, and log them in.
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->with('error', 'Google sign-in failed. Please try again.');
        }

        if (! $googleUser || empty($googleUser->getEmail())) {
            return redirect()->route('login')
                ->with('error', 'We could not read your email from Google.');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Link the Google account if it isn't already.
            if (! $user->google_id) {
                $user->google_id   = $googleUser->getId();
                $user->avatar      = $googleUser->getAvatar();
                $user->provider    = 'google';
                $user->save();
            }
            // Google already verified the email — reflect that.
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
                $user->save();
            }
        } else {
            // Create a brand-new landlord from the Google profile.
            $user = User::create([
                'name'              => $googleUser->getName() ?: $googleUser->getNickname() ?: explode('@', $googleUser->getEmail())[0],
                'email'             => $googleUser->getEmail(),
                'password'          => bcrypt(Str::random(40)),   // never used; user signs in via Google
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'provider'          => 'google',
                'email_verified_at' => now(),
            ]);
        }

        // Make sure the landlord has a settings row.
        Setting::for($user);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
