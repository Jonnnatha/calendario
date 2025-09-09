<?php

namespace Tests\Feature;

use App\Notifications\UpcomingSurgery;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SurgeryNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_notification_sent_when_surgery_stored(): void
    {
        Notification::fake();

        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $this->actingAs($doctor)->post('/surgeries', [
            'doctor_id' => $doctor->id,
            'room_number' => 1,
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'expected_duration' => 60,
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2),
        ]);

        Notification::assertSentTo($doctor, UpcomingSurgery::class);
    }
}
