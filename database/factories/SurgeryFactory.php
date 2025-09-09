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

        return [
            'patient_name' => $this->faker->name(),
            'surgery_type' => $this->faker->word(),
            'room' => $this->faker->numberBetween(1, 9),
            'starts_at' => $start,
            'duration_min' => $this->faker->numberBetween(30, 180),
            'created_by' => User::factory(),
        ];
    }
}

