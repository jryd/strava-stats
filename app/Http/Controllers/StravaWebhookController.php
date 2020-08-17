<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StravaWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $user = User::where('social_id', $request->input('owner_id'))->firstOrFail();

        Http::withToken($user->socialToken->active_token)
            ->get("https://www.strava.com/api/v3/activities/{$request->input('object_id')}");
    }
}
