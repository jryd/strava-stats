<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ProcessActivities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Do we have any processed activities
        $activitiesSince = optional($this->user->activities()->latest()->first())->recorded_at ?: now()->subDays(5);

        $activities = $this->fetchActivitiesSince($activitiesSince);

        dd($activities);
        // No - go back 5 days and process activities
        // Yes - grab any activities since the last recorded activity
    }

    private function fetchActivitiesSince($since)
    {
        $results =  Http::withToken($this->user->socialToken->token)
            ->get(sprintf('%s/athlete/activities', config('services.strava.host')), [
                'page' => 1,
                'after' => $since->timestamp
            ])
            ->json();

        return $results;
    }
}
