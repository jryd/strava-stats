<?php

namespace App\Http\Controllers;

use App\ProcessedActivity;
use App\Services\WindDirection;
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
    public function __invoke(Request $request, WindDirection $windDirection)
    {
        $user = User::where('social_id', $request->input('owner_id'))->firstOrFail();

        abort_if(
            $user->processedActivities()
                ->where('activity_id', $request->input('object_id'))
                ->exists(),
            200
        );

        $activity = Http::withToken($user->socialToken->active_token)
            ->get("https://www.strava.com/api/v3/activities/{$request->input('object_id')}")
            ->json();

        $weather = Http::get(sprintf(
            'https://api.darksky.net/forecast/%s/%s,%s,%s?units=ca',
            config('services.darksky.key'),
            $activity['start_latitude'],
            $activity['start_longitude'],
            now()->parse($activity['start_date'])->timestamp,
        ))->json();

        Http::withToken($user->socialToken->active_token)
            ->put("https://www.strava.com/api/v3/activities/{$request->input('object_id')}", [
                'description' => sprintf(
                    '%s%s, %s°C, Feels like %s°C, Humidity %s%%, Wind %skm/h from %s',
                    $activity['description']
                        ? "{$activity['description']}\n-----\n"
                        : '',
                    $weather['currently']['summary'],
                    $weather['currently']['temperature'],
                    $weather['currently']['apparentTemperature'],
                    floatval($weather['currently']['humidity']) * 100,
                    $weather['currently']['windSpeed'],
                    $windDirection->fromBearing($weather['currently']['windBearing']),
                )
            ]);

        $user->processedActivities()
            ->create([
                'activity_id' => $request->input('object_id')
            ]);
    }
}
