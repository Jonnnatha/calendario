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
            'patient_name' => 'John Doe',
            'surgery_type' => 'Appendectomy',
            'room' => 1,
            'duration_min' => 60,
            'starts_at' => now()->addHour(),
        ]);

        Notification::assertSentTo($doctor, UpcomingSurgery::class);
    }
}
