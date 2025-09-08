<?php

namespace Tests\Feature;

use App\Models\Surgery;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurgeryAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_audit_fields_are_recorded_and_displayed()
    {
        $doctor = User::factory()->create(['name' => 'Dr. Who']);
        $doctor->assignRole('medico');

        $nurse = User::factory()->create(['name' => 'Nurse Joy']);
        $nurse->assignRole('enfermeiro');

        $this->actingAs($doctor)->post('/surgeries', [
            'title' => 'Appendectomy',
            'scheduled_at' => now()->toDateTimeString(),
        ]);

        $surgery = Surgery::first();
        $this->assertEquals($doctor->id, $surgery->created_by);
        $this->assertNull($surgery->confirmed_by);

        $this->actingAs($nurse)->post("/surgeries/{$surgery->id}/confirm");

        $surgery->refresh();
        $this->assertEquals($nurse->id, $surgery->confirmed_by);

        $this->actingAs($doctor)->get('/dashboard')
            ->assertSee('Dr. Who')
            ->assertSee('Nurse Joy');
    }
}
