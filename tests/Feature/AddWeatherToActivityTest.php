<?php

namespace Tests\Feature;

use App\ProcessedActivity;
use App\SocialToken;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class AddWeatherToActivityTest extends TestCase
{
    use RefreshDatabase;

    private SocialToken $token;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'social_id' => 1337,
        ]);

        $this->token = factory(SocialToken::class)->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_fetches_the_details_of_an_activity_when_a_new_activity_is_created()
    {
        $this->mockHttpClient();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'create',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(fn (Request $request) => $request->url() === 'https://www.strava.com/api/v3/activities/123456789');
    }

    /** @test */
    public function it_fetches_the_details_of_an_activity_when_an_activity_is_updated_if_we_have_not_processed_it_yet()
    {
        $this->mockHttpClient();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'update',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(fn (Request $request) => $request->url() === 'https://www.strava.com/api/v3/activities/123456789');
    }

    /** @test */
    public function it_uses_the_users_social_token_as_a_bearer_token()
    {
        $this->mockHttpClient();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'create',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', "Bearer {$this->token->token}"));
    }

    /** @test */
    public function it_will_refresh_the_social_token_if_it_has_expired()
    {
        $this->mockHttpClient();

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
        $this->mockHttpClient();

        factory(ProcessedActivity::class)->create([
            'user_id' => $this->user->id,
            'activity_id' => 123456789,
        ]);

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
    public function it_stores_the_activity_in_the_processed_activities_table()
    {
        $this->mockHttpClient();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'create',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        $this->assertDatabaseHas('processed_activities', [
            'user_id' => $this->user->id,
            'activity_id' => 123456789
        ]);
    }

    /** @test */
    public function it_fetches_the_weather_for_the_location_of_the_activity()
    {
        now()->setTestNow(now());

        $this->mockHttpClient();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'create',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(fn (Request $request) => $request->url() === sprintf(
            'https://api.darksky.net/forecast/%s/%s,%s,%s?units=ca',
            config('services.darksky.key'),
            27.4698,
            153.0251,
            now()->timestamp,
        ));
    }

    /** @test */
    public function it_updates_the_activity_to_add_the_weather_to_the_description()
    {
        now()->setTestNow(now());

        $this->mockHttpClient();

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'create',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(fn (Request $request) => $request->url() === 'https://www.strava.com/api/v3/activities/123456789' &&
            $request->method() === 'PUT' &&
            $request['description'] === 'Drizzle, 22.3°C, Feels like 23.5°C, Humidity 83%, Wind 8.74km/h from WSW'
        );
    }

    /** @test */
    public function it_appends_the_weather_to_the_end_of_the_description_if_there_is_already_a_description_entered()
    {
        now()->setTestNow(now());

        $this->mockHttpClient([
            '*' => Http::response([
                'start_latitude' => 27.4698,
                'start_longitude' => 153.0251,
                'start_date' => now()->toW3cString(),
                'description' => 'existing description',
            ]),
        ]);

        $this->postJson(route('webhooks.strava'), [
            'aspect_type' => 'create',
            'event_time' => 1549560669,
            'object_id' => 123456789,
            'owner_id' => 1337,
            'subscription_id' => 13579,
            'object_type' => 'activity',
        ])->assertSuccessful();

        Http::assertSent(fn (Request $request) => $request->url() === 'https://www.strava.com/api/v3/activities/123456789' &&
            $request->method() === 'PUT' &&
            Str::contains($request['description'], 'existing description') &&
            Str::contains($request['description'], '-----') &&
            Str::contains($request['description'], 'Drizzle, 22.3°C, Feels like 23.5°C, Humidity 83%, Wind 8.74km/h from WSW')
        );
    }

    private function mockHttpClient($responses = [])
    {
        Http::fake(array_merge([
            'https://www.strava.com/oauth/token' => Http::response([
                "token_type" => "Bearer",
                "access_token" => "abc123",
                "expires_at" => 1568775134,
                "expires_in" => 20566,
                "refresh_token" => "def456"
            ]),
            'api.darksky.net/*' => Http::response([
                'currently' => [
                    'summary' => 'Drizzle',
                    'temperature' => 22.3,
                    'apparentTemperature' => 23.5,
                    'humidity' => 0.83,
                    'windSpeed' => 8.74,
                    'windBearing' => 246,
                ]
            ]),
            '*' => Http::response([
                'start_latitude' => 27.4698,
                'start_longitude' => 153.0251,
                'start_date' => now()->toW3cString(),
                'description' => '',
            ]),
        ], $responses));
    }
}
