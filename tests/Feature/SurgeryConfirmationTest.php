<?php

namespace Tests\Feature;

use App\Models\Surgery;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurgeryConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_is_redirected_when_confirming_surgery(): void
    {
        $creator = User::factory()->create();
        $surgery = Surgery::create([
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'room' => 1,
            'starts_at' => now()->addDay(),
            'duration_min' => 60,
            'created_by' => $creator->id,
        ]);

        $response = $this->post("/surgeries/{$surgery->id}/confirm");

        $response->assertRedirect('/login');
    }

    public function test_non_enfermeiro_users_cannot_confirm_surgery(): void
    {
        $creator = User::factory()->create();
        $surgery = Surgery::create([
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'room' => 1,
            'starts_at' => now()->addDay(),
            'duration_min' => 60,
            'created_by' => $creator->id,
        ]);

        foreach ([null, 'adm', 'medico'] as $role) {
            $user = User::factory()->create();
            if ($role) {
                $user->assignRole($role);
            }

            $response = $this->actingAs($user)->post("/surgeries/{$surgery->id}/confirm");

            $response->assertForbidden();
        }
    }

    public function test_enfermeiro_can_confirm_surgery(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $surgery = Surgery::create([
            'patient_name' => 'Jane Doe',
            'surgery_type' => 'Appendectomy',
            'room' => 1,
            'starts_at' => now()->addDay(),
            'duration_min' => 60,
            'created_by' => $doctor->id,
            'is_conflict' => true,
        ]);

        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');

        $response = $this->actingAs($nurse)->post("/surgeries/{$surgery->id}/confirm");

        $response->assertOk();

        $this->assertDatabaseHas('surgeries', [
            'id' => $surgery->id,
            'confirmed_by' => $nurse->id,
            'is_conflict' => true,
        ]);
    }
}
