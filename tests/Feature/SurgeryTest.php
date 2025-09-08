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

    public function test_doctor_can_schedule_non_conflicting_surgery(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        Surgery::factory()->create([
            'room_number' => 1,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $start = now()->addDays(2);
        $end = $start->copy()->addHour();

        $response = $this->actingAs($doctor)->post('/surgeries', [
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'start_time' => $start,
            'end_time' => $end,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('surgeries', [
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
        ]);
    }

    public function test_nurse_cannot_schedule_surgery(): void
    {
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');

        $response = $this->actingAs($nurse)->post('/surgeries', [
            'doctor_id' => $nurse->id,
            'room_number' => 1,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('surgeries', 0);
    }

    public function test_admin_cannot_schedule_surgery(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('adm');

        $response = $this->actingAs($admin)->post('/surgeries', [
            'doctor_id' => $admin->id,
            'room_number' => 1,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('surgeries', 0);
    }
}

