<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureSingleAdminTest extends TestCase
{
    use RefreshDatabase;

    // ─── Registro SEMPRE bloqueado ────────────────────────────────

    public function test_registration_page_is_always_blocked(): void
    {
        $response = $this->get('/register');

        // Registro não deve ser acessível — admin é criado via seeder
        $response->assertForbidden();
    }

    public function test_registration_post_is_always_blocked(): void
    {
        $response = $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'intruder@example.com',
        ]);
    }

    public function test_registration_is_blocked_even_when_no_users_exist(): void
    {
        $this->assertDatabaseCount('users', 0);

        $response = $this->post('/register', [
            'name' => 'Sneaky User',
            'email' => 'sneaky@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertForbidden();
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    // ─── Login NÃO é afetado ──────────────────────────────────────

    public function test_login_is_not_affected_by_registration_block(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    // ─── Logout NÃO é afetado ─────────────────────────────────────

    public function test_logout_is_not_affected_by_registration_block(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
