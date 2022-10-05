<?php

namespace Tests\Feature;

use App\SocialToken;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth2\User as OAuthUser;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseMigrations;

    private OAuthUser $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new OAuthUser;

        $this->user->id = 1337;
        $this->user->token = 'abc123';
        $this->user->refreshToken = 'def456';
        $this->user->expiresIn = 60;
        $this->user->avatar = 'avatar.png';
        $this->user->user = [
            'firstname' => 'Test',
            'lastname' => 'McTest'
        ];

        Socialite::shouldReceive('with->scopes->redirect')
            ->andReturn(redirect('https://strava.test',301));
        Socialite::shouldReceive('driver->user')
            ->andReturn($this->user);
    }

    /** @test */
    public function it_redirects_you_to_strava_to_login()
    {
        $this->get(route('login'))
            ->assertRedirect('https://strava.test');
    }

    /** @test */
    public function it_creates_a_new_user_from_strava()
    {
        $this->get(route('login.callback'))
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'social_id' => 1337,
            'first_name' => 'Test',
            'last_name' => 'McTest',
            'avatar' => "https://strava.com/assets/avatar.png",
        ]);

        $this->assertDatabaseHas('social_tokens', [
            'token' => 'abc123',
            'refresh_token' => 'def456',
        ]);
    }

    /** @test */
    public function it_updates_an_existing_user_from_strava()
    {
        $user = User::factory()
            ->create([
                'social_id' => 1337,
            ]);

        $token = SocialToken::factory()
            ->create([
                'user_id' => $user->id,
            ]);

        $this->assertNotEquals('Test', $user->first_name);
        $this->assertNotEquals('abc123', $token->token);

        $this->get(route('login.callback'))
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'social_id' => 1337,
            'first_name' => 'Test',
            'last_name' => 'McTest',
            'avatar' => "https://strava.com/assets/avatar.png",
        ]);

        $this->assertDatabaseHas('social_tokens', [
            'token' => 'abc123',
            'refresh_token' => 'def456',
        ]);

        $this->assertEquals('Test', $user->fresh()->first_name);
        $this->assertEquals('abc123', $token->fresh()->token);

        $this->assertEquals(1, SocialToken::count());
    }

    /** @test */
    public function it_sets_the_expires_at_timestamp_as_seconds_from_now()
    {
        $this->freezeTime();

        $this->get(route('login.callback'))
            ->assertSuccessful();

        $token = SocialToken::first();

        $this->assertEquals(now()->addSeconds(60)->toDateTimeString(), $token->expires_at);
    }
}
