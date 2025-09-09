<?php

namespace Tests\Feature;

use App\Models\Surgery;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'expected_duration' => 60,
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
                'patient_name' => 'John Doe',
                'surgery_type' => 'Appendectomy',
                'expected_duration' => 60,
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
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'expected_duration' => 60,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('surgeries', [
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'expected_duration' => 60,
            'created_by' => $doctor->id,
            'status' => 'scheduled',
            'confirmed_by' => null,
        ]);
    }

    public function test_can_schedule_surgery_in_occupied_room(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $existing = Surgery::factory()->create([
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'expected_duration' => 60,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $response = $this->actingAs($doctor)->post('/surgeries', [
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'patient_name' => 'Jane Doe',
            'surgery_type' => 'Appendectomy',
            'expected_duration' => 60,
            'start_time' => $existing->start_time->copy()->addMinutes(30),
            'end_time' => $existing->end_time->copy()->addMinutes(30),
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseCount('surgeries', 2);
    }

    public function test_conflicting_surgeries_are_marked_as_conflict_in_index(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $surgeryOne = Surgery::factory()->create([
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $surgeryTwo = Surgery::factory()->create([
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'start_time' => $surgeryOne->start_time->copy()->addMinutes(30),
            'end_time' => $surgeryOne->end_time->copy()->addMinutes(30),
        ]);

        $response = $this->actingAs($doctor)->get('/medico');

        $response->assertInertia(fn (Assert $page) =>
            $page->component('Medico/Calendar')
                ->has('surgeries', 2)
                ->where('surgeries.0.status', 'conflict')
                ->where('surgeries.1.status', 'conflict')
        );
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
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'expected_duration' => 60,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHour(),
        ]);

        $response->assertSessionHasErrors('doctor_id');
    }

    public function test_room_number_must_be_between_one_and_nine(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $this->actingAs($doctor);

        foreach ([0, 10] as $room) {
            $response = $this->post('/surgeries', [
                'doctor_id' => $doctor->id,
                'room_number' => $room,
                'patient_name' => 'John Doe',
                'surgery_type' => 'Appendectomy',
                'expected_duration' => 60,
                'start_time' => now()->addDay(),
                'end_time' => now()->addDay()->addHour(),
            ]);

            $response->assertSessionHasErrors('room_number');
        }
    }
}

