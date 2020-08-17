<?php

namespace Tests\Feature;

use App\SocialToken;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AddWeatherToActivityTest extends TestCase
{
    use RefreshDatabase;

    private SocialToken $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = factory(User::class)->create([
            'social_id' => 1337,
        ]);

        $this->token = factory(SocialToken::class)->create([
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_fetches_the_details_of_an_activity_when_a_new_activity_is_created()
    {
        Http::fake();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'create',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://www.strava.com/api/v3/activities/123456789';
        });
    }

    /** @test */
    public function it_fetches_the_details_of_an_activity_when_an_activity_is_updated_if_we_have_not_processed_it_yet()
    {
        Http::fake();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'update',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://www.strava.com/api/v3/activities/123456789';
        });
    }

    /** @test */
    public function it_uses_the_users_social_token_as_a_bearer_token()
    {
        Http::fake();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'create',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', "Bearer {$token->token}"));
    }

    /** @test */
    public function it_will_refresh_the_social_token_if_it_has_expired()
    {
        Http::fake([
            'https://www.strava.com/oauth/token' => Http::response([
                "token_type" => "Bearer",
                "access_token" => "abc123",
                "expires_at" => 1568775134,
                "expires_in" => 20566,
                "refresh_token" => "def456"
            ]),
            '*' => Http::response(),
        ]);

        $this->token->update([
            'expires_at' => now()->subMinute(),
        ]);

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'create',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://www.strava.com/oauth/token';
        });

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', "Bearer abc123"));
    }

    /** @test */
    public function it_ignores_updates_to_activities_if_we_have_already_processed_it()
    {
        Http::fake();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'update',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertNothingSent();
    }

    /** @test */
    public function it_fetches_the_weather_for_the_location_of_the_activity()
    {

    }

    /** @test */
    public function it_updates_the_activity_to_add_the_weather_to_the_description()
    {

    }

    /** @test */
    public function it_appends_the_weather_to_the_end_of_the_description_if_there_is_already_a_description_entered()
    {

    }

    /** @test */
    public function it_ignores_athlete_updates()
    {

    }
}
