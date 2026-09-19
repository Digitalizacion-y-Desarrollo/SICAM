<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    }

    public function test_login_view_is_available(): void
    {
        $this->withoutVite();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Inicia sesión')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'contraseña-segura']);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'contraseña-segura',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_shown_below_email_field(): void
    {
        $this->post(route('login.store'), [
            'email' => 'usuario@example.com',
            'password' => 'incorrecta',
        ])->assertSessionHasErrors('email');
    }
}
