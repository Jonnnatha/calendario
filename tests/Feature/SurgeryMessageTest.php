<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurgeryMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_flash_message_after_creating_surgery(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('surgery.store'));

        $response->assertRedirect(route('calendar'));
        $response->assertSessionHas('message', 'Surgery created successfully.');
    }

    public function test_flash_message_after_confirming_surgery(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('surgery.confirm'));

        $response->assertRedirect(route('calendar'));
        $response->assertSessionHas('message', 'Surgery confirmed.');
    }
}
