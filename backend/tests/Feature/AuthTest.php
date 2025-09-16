<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanRegisterWithValidCredentials() {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'token'
            ])
            ->assertJson([
                'message' => 'User registered successfully.',
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ]
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => 1,
            'tokenable_type' => 'App\\Models\\User',
            'name' => 'auth-token'
        ]);
    }

    public function testUserCannotRegisterWithInvalidData()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                    'email',
                    'password'
                ]
            ]);

        $this->assertDatabaseCount('users', 0);
    }

     public function testUserCannotRegisterWithExistingEmail()
    {
        // Create a user first
        User::factory()->create([
            'email' => 'john@example.com'
        ]);

        $userData = [
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email'
                ]
            ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function testUserCanLoginWithValidCredentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ],
                'token'
            ])
            ->assertJson([
                'message' => 'User authenticated successfully',
                'user' => [
                    'id' => $user->id,
                    'email' => 'john@example.com',
                ]
            ]);

        // Verify token was created
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => 'App\Models\User',
            'name' => 'auth-token'
        ]);
    }

    public function testUserCannotLoginWithInvalidEmailFormat()
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email'
                ]
            ]);
    }

    public function testUserCannotLoginWithMissingPassword()
    {
        $loginData = [
            'email' => 'john@example.com',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email'
                ]
            ]);
    }

    public function testUserCanLogout()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create a token for the user
        $token = $user->createToken('auth-token')->plainTextToken;

        // Verify token exists
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => 'App\Models\User',
            'name' => 'auth-token'
        ]);

        // Logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => 'App\Models\User',
            'name' => 'auth-token'
        ]);
    }
}
