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
        $response = $this->post('/surgeries', [
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'room' => 1,
            'duration_min' => 60,
            'starts_at' => now()->addDay(),
        ]);

        $response->assertRedirect('/login');
    }

    public function test_non_medico_users_cannot_schedule_surgery(): void
    {
        foreach ([null, 'adm', 'enfermeiro'] as $role) {
            $user = User::factory()->create();
            if ($role) {
                $user->assignRole($role);
            }

            $response = $this->actingAs($user)->post('/surgeries', [
                'patient_name' => 'John Doe',
                'surgery_type' => 'Appendectomy',
                'room' => 1,
                'duration_min' => 60,
                'starts_at' => now()->addDay(),
            ]);

            $response->assertForbidden();
        }
    }

    public function test_medico_can_schedule_surgery(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $response = $this->actingAs($doctor)->post('/surgeries', [
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'room' => 1,
            'duration_min' => 60,
            'starts_at' => now()->addDay(),
        ]);

        $response->assertRedirect('/');

        $this->assertDatabaseHas('surgeries', [
            'created_by' => $doctor->id,
            'room' => 1,
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'duration_min' => 60,
            'confirmed_by' => null,
        ]);
    }

    public function test_can_schedule_surgery_in_occupied_room(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $existing = Surgery::factory()->create([
            'created_by' => $doctor->id,
            'room' => 1,
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'duration_min' => 60,
            'starts_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($doctor)->post('/surgeries', [
            'room' => 1,
            'patient_name' => 'Jane Doe',
            'surgery_type' => 'Appendectomy',
            'duration_min' => 60,
            'starts_at' => $existing->starts_at->copy()->addMinutes(30),
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseCount('surgeries', 2);
    }

    public function test_conflicting_surgeries_are_marked_as_conflict_in_index(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $surgeryOne = Surgery::factory()->create([
            'created_by' => $doctor->id,
            'room' => 1,
            'starts_at' => now()->addDay(),
            'duration_min' => 60,
        ]);

        $surgeryTwo = Surgery::factory()->create([
            'created_by' => $doctor->id,
            'room' => 1,
            'starts_at' => $surgeryOne->starts_at->copy()->addMinutes(30),
            'duration_min' => 60,
        ]);

        $response = $this->actingAs($doctor)->get('/surgeries');

        $response->assertInertia(fn (Assert $page) =>
            $page->component('Medico/Calendar')
                ->has('surgeries', 2)
                ->where('surgeries.0.is_conflict', false)
                ->where('surgeries.1.is_conflict', true)
        );
    }

    public function test_doctor_cannot_schedule_surgery_for_another_user(): void
    {
        $doctor = User::factory()->create();
        $otherDoctor = User::factory()->create();
        $doctor->assignRole('medico');
        $otherDoctor->assignRole('medico');

        $response = $this->actingAs($doctor)->post('/surgeries', [
            'created_by' => $otherDoctor->id,
            'room' => 1,
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'duration_min' => 60,
            'starts_at' => now()->addDay(),
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('surgeries', [
            'created_by' => $doctor->id,
        ]);
    }

    public function test_room_number_must_be_between_one_and_nine(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $this->actingAs($doctor);

        foreach ([0, 10] as $room) {
            $response = $this->post('/surgeries', [
                'patient_name' => 'John Doe',
                'surgery_type' => 'Appendectomy',
                'room' => $room,
                'duration_min' => 60,
                'starts_at' => now()->addDay(),
            ]);

            $response->assertSessionHasErrors('room');
        }
    }
}

