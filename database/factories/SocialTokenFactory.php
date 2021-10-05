<?php

namespace Database\Factories;

use App\SocialToken;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SocialTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SocialToken::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'token' => Str::random(32),
            'refresh_token' => Str::random(32),
            'expires_at' => now()->addSeconds(60),
        ];
    }
}
