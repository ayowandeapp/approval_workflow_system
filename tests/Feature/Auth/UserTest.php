<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use WithFaker, RefreshDatabase;
    public function test_user_can_register_with_valid_data(): void
    {
        // $this->withoutExceptionHandling();
        $payload = User::factory()->raw([
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        // dd($payload);
        $response = $this->post('/api/auth/register', $payload);

        $response->assertStatus(201);

        unset($payload['password'], $payload['password_confirmation']);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'username' => $payload['username']
        ]);

        $this->assertArrayHasKey('token', $response->json());
    }

    /** @test */
    public function registration_requires_password_confirmation()
    {
        $payload = User::factory()->raw([
            'password' => 'password',
        ]);
        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {

        $user = User::factory()->create();
        $token = $user->createToken($user->username)->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);

        $this->assertCount(0, $user->tokens); // Ensure tokens are revoked
    }

    /** @test */
    public function can_list_all_users()
    {
        $this->withoutExceptionHandling();
        $this->authenticate();
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    /** @test */
    public function user_can_view_their_own_profile()
    {
        $this->withoutExceptionHandling();
        $user = $this->authenticate();

        $response = $this->getJson("/api/users/$user->id");

        $response->assertStatus(200)
            ->assertSee($user['username']);
    }

    /** @test */
    public function user_can_update_their_own_profile()
    {
        $this->withoutExceptionHandling();
        $user = $this->authenticate();
        $response = $this->patchJson("/api/users/$user->id", [
            'username' => 'My New Name'
        ]);

        $response->assertStatus(200)
            ->assertJson(['user' => ['username' => 'My New Name']]);
    }

    /** @test */
    public function user_can_deactivate()
    {
        $this->withoutExceptionHandling();
        $user = $this->authenticate();

        $response = $this->deleteJson("/api/users/$user->id");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deactivated successfully']);
    }

}
