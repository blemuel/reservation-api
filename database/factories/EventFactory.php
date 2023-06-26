<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            "title" => $this->faker->sentence,
            "description" => $this->faker->paragraph,
            "eventDate" => $this->faker->dateTimeBetween("now", "+2 years"),
            "location" => $this->faker->address,
            "price" => $this->faker->randomFloat(2, 0, 1000),
            "attendeesLimit" => $this->faker->numberBetween(1, 500),
            "user_id" => User::inRandomOrder()->first()->id,
        ];
    }
}
