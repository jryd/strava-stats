<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CreateSubscription extends Command
{
    protected $signature = 'strava:create-subscription';

    protected $description = 'Creates a webhook subscription with Strava';

    public function handle()
    {
        Http::post('https://www.strava.com/api/v3/push_subscriptions', [
            'client_id' => config('services.strava.client_id'),
            'client_secret' => config('services.strava.client_secret'),
            'callback_url'  => route('webhooks.strava'),
            'verify_token' => 'strava',
        ]);
    }
}
