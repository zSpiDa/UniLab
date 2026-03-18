<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_issue_token_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'api@test.local',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'api@test.local',
            'password' => 'secret123',
            'device_name' => 'phpunit',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function test_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'api@test.local',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/v1/tokens', [
            'email' => 'api@test.local',
            'password' => 'wrong-password',
            'device_name' => 'phpunit',
        ]);

        $response->assertStatus(422);
    }
}