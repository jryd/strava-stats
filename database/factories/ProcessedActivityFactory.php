<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ProcessedActivity;
use App\User;
use Faker\Generator as Faker;

$factory->define(ProcessedActivity::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'activity_id' => $faker->randomNumber(),
    ];
});
