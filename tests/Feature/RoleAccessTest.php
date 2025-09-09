<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_access()
    {
        $user = User::factory()->create();
        $user->assignRole('adm');

        $this->actingAs($user)->get('/admin')->assertOk();
        $this->actingAs($user)->get('/surgeries')->assertForbidden();
    }

    public function test_medico_access()
    {
        $user = User::factory()->create();
        $user->assignRole('medico');

        $this->actingAs($user)->get('/surgeries')->assertOk();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_enfermeiro_access()
    {
        $user = User::factory()->create();
        $user->assignRole('enfermeiro');

        $this->actingAs($user)->get('/surgeries')->assertOk();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }
}
