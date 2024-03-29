<?php

namespace App\Http\Controllers;

use App\Services\StravaActivity;
use App\Services\Weather;
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
    public function __invoke(Request $request, WindDirection $windDirection, StravaActivity $stravaActivity, Weather $weather)
    {
        $user = User::where('social_id', $request->input('owner_id'))->firstOrFail();

        abort_if(
            $user->processedActivities()
                ->where('activity_id', $request->input('object_id'))
                ->exists(),
            200
        );

        $activity = $stravaActivity->for($user)
            ->get($request->input('object_id'));

        $forecast = $weather->for($activity);

        $stravaActivity->update($request->input('object_id'), [
            'description' => sprintf(
                '%s%s, %s°C, Feels like %s°C, Humidity %s%%, Wind %skm/h from %s',
                $activity->description
                    ? "{$activity->description}\n-----\n"
                    : '',
                $forecast->currently->summary,
                $forecast->currently->temperature,
                $forecast->currently->apparentTemperature,
                $forecast->currently->humidity * 100,
                $forecast->currently->windSpeed,
                $windDirection->fromBearing($forecast->currently->windBearing),
            )
        ]);

        $user->processedActivities()
            ->create([
                'activity_id' => $request->input('object_id')
            ]);
    }
}
