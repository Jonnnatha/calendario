<?php

namespace Tests\Feature;

use App\Models\Surgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurgeryAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_entry_created_when_surgery_scheduled(): void
    {
        $surgery = Surgery::factory()->create();

        $this->assertDatabaseHas('surgery_audits', [
            'surgery_id' => $surgery->id,
            'doctor_id' => $surgery->doctor_id,
            'room_number' => $surgery->room_number,
        ]);
    }
}
