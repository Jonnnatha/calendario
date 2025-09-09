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
        $surgery = Surgery::factory()->create();

        $response = $this->post("/surgeries/{$surgery->id}/confirm");

        $response->assertRedirect('/login');
    }

    public function test_non_enfermeiro_users_cannot_confirm_surgery(): void
    {
        $surgery = Surgery::factory()->create();

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
        $surgery = Surgery::factory()->create([
            'doctor_id' => $doctor->id,
            'created_by' => $doctor->id,
        ]);

        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');

        $response = $this->actingAs($nurse)->from('/surgeries')->post("/surgeries/{$surgery->id}/confirm");

        $response->assertRedirect('/surgeries');

        $this->assertDatabaseHas('surgeries', [
            'id' => $surgery->id,
            'status' => 'confirmed',
            'confirmed_by' => $nurse->id,
            'created_by' => $doctor->id,
        ]);
    }
}
