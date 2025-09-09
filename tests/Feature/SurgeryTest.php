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

    public function test_guest_is_redirected_when_posting_surgery(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $response = $this->post('/surgeries', [
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $response->assertRedirect('/login');
    }

    public function test_non_medico_users_cannot_schedule_surgery(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        foreach ([null, 'adm', 'enfermeiro'] as $role) {
            $user = User::factory()->create();
            if ($role) {
                $user->assignRole($role);
            }

            $response = $this->actingAs($user)->post('/surgeries', [
                'doctor_id' => $doctor->id,
                'room_number' => 1,
                'start_time' => now()->addDay(),
                'end_time' => now()->addDay()->addHour(),
            ]);

            $response->assertForbidden();
        }
    }

    public function test_medico_can_schedule_surgery(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $response = $this->actingAs($doctor)->post('/surgeries', [
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('surgeries', [
            'doctor_id' => $doctor->id,
            'room_number' => 1,
        ]);
    }

    public function test_medico_can_schedule_conflicting_surgery(): void
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

        $response->assertRedirect('/');
        $this->assertDatabaseCount('surgeries', 2);
    }

    public function test_conflicting_surgeries_are_marked_with_status(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $first = Surgery::factory()->create([
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $second = Surgery::factory()->create([
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'start_time' => $first->start_time->copy()->addMinutes(30),
            'end_time' => $first->end_time->copy()->addMinutes(30),
        ]);

        Surgery::factory()->create([
            'doctor_id' => $doctor->id,
            'room_number' => 2,
            'start_time' => now()->addDays(2),
            'end_time' => now()->addDays(2)->addHour(),
        ]);

        $response = $this->actingAs($doctor)->get('/medico');

        $surgeries = $response->viewData('page')['props']['surgeries'];
        $conflicts = array_filter($surgeries, fn ($s) => ($s['status'] ?? null) === 'conflict');
        $this->assertCount(2, $conflicts);
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

