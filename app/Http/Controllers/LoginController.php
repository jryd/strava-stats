<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::with('strava')->redirect();
    }

    public function handleCallback()
    {
        dd(Socialite::driver('strava')->user());
    }
}
