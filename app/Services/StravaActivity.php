<?php

namespace App\Services;

use App\DTO\Activity;
use App\User;
use Illuminate\Support\Facades\Http;

class StravaActivity
{
    private User $user;

    public function for(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function get(string $activityId)
    {
        return new Activity(
            Http::withToken($this->user->socialToken->active_token)
                ->get("https://www.strava.com/api/v3/activities/{$activityId}")
                ->json()
        );
    }

    public function update(string $activityId, array $data)
    {
        return Http::withToken($this->user->socialToken->active_token)
            ->put("https://www.strava.com/api/v3/activities/{$activityId}", $data)
            ->json();

    }
}
