<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateSubscriptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
    }

    /** @test */
    public function it_sends_a_request_to_strava_to_create_the_subscription()
    {
        $this->artisan('strava:create-subscription');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.strava.com/api/v3/push_subscriptions' &&
                $request['client_id'] === config('services.strava.client_id') &&
                $request['client_secret'] === config('services.strava.client_secret') &&
                $request['callback_url'] === route('webhooks.strava');
        });
    }

    /** @test */
    public function it_responds_to_the_verification_challenge_from_strava()
    {
        $this->get(route('webhooks.strava', ['hub.challenge' => 'abc123']))
            ->assertSuccessful()
            ->assertJson([
                'hub.challenge' => 'abc123',
            ]);
    }
}
