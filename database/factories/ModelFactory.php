<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});

$factory->define(App\Book::class, function ($faker) {
    $title = $faker->sentence(rand(3, 10));

    return [
        'title' => substr($title, 0, strlen($title) - 1),
        'description' => $faker->text,
    ];
});

$factory->define(App\Author::class, function ($faker) {
    return [
        'name' => $faker->name,
        'biography' => join(" ", $faker->sentences(rand(3, 5))),
        'gender' => rand(1, 6) % 2 === 0 ? 'male' : 'female'
    ];
});

$factory->define(\App\Bundle::class, function ($faker) {

    $title = $faker->sentence(rand(3, 10));

    return [
        'title' => substr($title, 0, strlen($title) - 1),
        'description' => $faker->text
    ];
});

$factory->define(\App\Rating::class, function ($faker) {
    return [
        'value' => rand(1, 5)
    ];
});
