<?php

namespace App\Services;

use App\DTO\Activity;
use App\DTO\Forecast;
use Illuminate\Support\Facades\Http;

class Weather
{
    public function for(Activity $activity)
    {
        return new Forecast(
            Http::get(sprintf(
                'https://api.darksky.net/forecast/%s/%s,%s,%s?units=ca',
                config('services.darksky.key'),
                $activity->start_latitude,
                $activity->start_longitude,
                now()->parse($activity->start_date)->timestamp,
            ))->json()
        );
    }
}
