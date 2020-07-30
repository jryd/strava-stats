<?php

namespace Tests\Feature;

use App\Jobs\ProcessActivities;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SyncActivitiesTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_dispatches_a_job_for_each_user()
    {
        Bus::fake();

        factory(User::class)->create();

        $this->artisan('activities:sync')
            ->assertExitCode(0);

        Bus::assertDispatched(ProcessActivities::class);
    }
}
