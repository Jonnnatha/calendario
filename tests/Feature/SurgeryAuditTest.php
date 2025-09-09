<?php

namespace Tests\Feature;

use App\Models\Surgery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurgeryAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_entry_created_when_surgery_scheduled(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $surgery = Surgery::factory()->create();

        $this->assertDatabaseHas('surgery_audits', [
            'surgery_id' => $surgery->id,
            'doctor_id' => $surgery->doctor_id,
            'room_number' => $surgery->room_number,
            'created_by' => $user->id,
            'confirmed_by' => null,
        ]);
    }

    public function test_audit_entry_records_confirmed_by_on_update(): void
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);
        $surgery = Surgery::factory()->create();

        $confirmer = User::factory()->create();
        $this->actingAs($confirmer);
        $surgery->update(['room_number' => 9]);

        $this->assertDatabaseHas('surgery_audits', [
            'surgery_id' => $surgery->id,
            'room_number' => 9,
            'confirmed_by' => $confirmer->id,
        ]);
    }

    public function test_audit_entry_records_confirmed_by_on_delete(): void
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);
        $surgery = Surgery::factory()->create();

        $deleter = User::factory()->create();
        $this->actingAs($deleter);
        $surgery->delete();

        $this->assertDatabaseHas('surgery_audits', [
            'surgery_id' => $surgery->id,
            'confirmed_by' => $deleter->id,
        ]);
    }
}
