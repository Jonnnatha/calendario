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
        $surgery = Surgery::factory()->create([
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('surgery_audits', [
            'surgery_id' => $surgery->id,
            'created_by' => $surgery->created_by,
            'room_number' => $surgery->room,
            'confirmed_by' => null,
        ]);
    }

    public function test_audit_entry_records_confirmed_by_on_update(): void
    {
        $creator = User::factory()->create();
        $surgery = Surgery::factory()->create([
            'created_by' => $creator->id,
        ]);

        $confirmer = User::factory()->create();
        $this->actingAs($confirmer);
        $surgery->update(['room' => 9]);

        $this->assertDatabaseHas('surgery_audits', [
            'surgery_id' => $surgery->id,
            'room_number' => 9,
            'created_by' => $creator->id,
            'confirmed_by' => $confirmer->id,
        ]);
    }

    public function test_audit_entry_records_confirmed_by_on_delete(): void
    {
        $creator = User::factory()->create();
        $surgery = Surgery::factory()->create([
            'created_by' => $creator->id,
        ]);

        $deleter = User::factory()->create();
        $this->actingAs($deleter);
        $surgery->delete();

        $this->assertDatabaseHas('surgery_audits', [
            'surgery_id' => $surgery->id,
            'created_by' => $creator->id,
            'confirmed_by' => $deleter->id,
        ]);
    }
}
