<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\SocialToken;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(SocialToken::class, function (Faker $faker) {
    return array(
        'user_id' => factory(User::class),
        'token' => Str::random(32),
        'refresh_token' => Str::random(32),
        'expires_at' => now()->addSeconds(60),
    );
});
