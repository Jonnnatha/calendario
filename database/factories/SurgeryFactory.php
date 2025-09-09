<?php

namespace Database\Factories;

use App\Models\Surgery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Surgery>
 */
class SurgeryFactory extends Factory
{
    protected $model = Surgery::class;

    public function definition(): array
    {
        $start = Carbon::instance($this->faker->dateTimeBetween('now', '+1 week'));
        $end = (clone $start)->addHour();

        return [
            'doctor_id' => User::factory(),
            'room_number' => $this->faker->numberBetween(1, 9),
            'start_time' => $start,
            'end_time' => $end,
        ];
    }
}

