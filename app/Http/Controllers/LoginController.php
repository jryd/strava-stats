<?php

namespace App\Http\Controllers;

use App\User;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::with('strava')->redirect();
    }

    public function handleCallback()
    {
        $stravaUser = Socialite::driver('strava')->user();

        auth()->login(User::updateOrCreate([
            'social_id' => $stravaUser->id,
        ], [
            'first_name' => $stravaUser->user['firstname'],
            'last_name' => $stravaUser->user['lastname'],
            'avatar' => "https://strava.com/assets/{$stravaUser->avatar}",
        ]));

        auth()->user()
            ->socialToken()
            ->updateOrCreate([], [
                'token' => $stravaUser->token,
                'refresh_token' => $stravaUser->refreshToken,
                'expires_at' => now()->addSeconds($stravaUser->expiresIn),
            ]);

        return auth()->user();
    }
}
