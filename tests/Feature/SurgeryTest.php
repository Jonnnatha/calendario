<?php

namespace Tests\Feature;

use App\Models\Surgery;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurgeryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_cannot_schedule_surgery_in_occupied_room(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $existing = Surgery::factory()->create([
            'room_number' => 1,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $response = $this->actingAs($doctor)->post('/surgeries', [
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'start_time' => $existing->start_time->copy()->addMinutes(30),
            'end_time' => $existing->end_time->copy()->addMinutes(30),
        ]);

        $response->assertSessionHasErrors('room_number');
    }

    public function test_doctor_cannot_schedule_surgery_for_another_user(): void
    {
        $doctor = User::factory()->create();
        $otherDoctor = User::factory()->create();
        $doctor->assignRole('medico');
        $otherDoctor->assignRole('medico');

        $response = $this->actingAs($doctor)->post('/surgeries', [
            'doctor_id' => $otherDoctor->id,
            'room_number' => 1,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $response->assertSessionHasErrors('doctor_id');
    }
}

