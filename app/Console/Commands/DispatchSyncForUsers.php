<?php

namespace App\Console\Commands;

use App\Jobs\ProcessActivities;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class DispatchSyncForUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches a check for each user to, in turn, sync weather to their activities';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::chunk(100, function (Collection $users) {
            $users->each(function (User $user) {
                ProcessActivities::dispatch($user);
            });
        });
    }
}
