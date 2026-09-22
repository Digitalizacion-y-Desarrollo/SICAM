<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'services.accesos.base_url' => 'https://accesos.test',
            'services.accesos.system_key' => 'system-key-pruebas',
        ]);
    }

    public function test_login_view_is_available(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Inicia sesión')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    public function test_user_can_log_in_with_accesos_credentials(): void
    {
        Http::fake([
            'https://accesos.test/api/auth/login' => Http::response([
                'message' => 'Login correcto.',
                'data' => [
                    'access_token' => 'token-de-prueba',
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => 25,
                        'name' => 'Ana',
                        'apellido_paterno' => 'Pérez',
                        'apellido_materno' => 'López',
                        'email' => 'ana@nezahualcoyotl.gob.mx',
                        'departamento' => [
                            'departamento_padre' => ['id' => 1, 'nombre' => 'Administración'],
                            'departamento_hijo' => ['id' => 2, 'nombre' => 'Sistemas'],
                        ],
                    ],
                    'system' => ['id' => 8, 'nombre' => 'SICAM', 'clave' => 'system-key-pruebas'],
                    'roles' => ['administrador'],
                    'permissions' => ['dashboard-sicam.ver', 'licencias.ver', 'licencias.editar'],
                ],
            ], 200),
        ]);

        $this->post(route('login.store'), [
            'email' => 'ana@nezahualcoyotl.gob.mx',
            'password' => 'contraseña-segura',
            'remember' => '1',
        ])->assertRedirect(route('dashboard'))
            ->assertSessionHas('accesos.access_token', 'token-de-prueba')
            ->assertSessionHas('accesos.roles', ['administrador'])
            ->assertSessionHas('accesos.permissions', ['dashboard-sicam.ver', 'licencias.ver', 'licencias.editar']);

        $user = User::where('email', 'ana@nezahualcoyotl.gob.mx')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Ana Pérez López', $user->name);

        Http::assertSent(fn ($request) => $request->url() === 'https://accesos.test/api/auth/login'
            && $request['email'] === 'ana@nezahualcoyotl.gob.mx'
            && $request['password'] === 'contraseña-segura'
            && $request['system_key'] === 'system-key-pruebas');
    }

    public function test_invalid_accesos_credentials_are_shown_below_email_field(): void
    {
        Http::fake([
            'https://accesos.test/api/auth/login' => Http::response([
                'message' => 'Credenciales inválidas.',
            ], 401),
        ]);

        $this->post(route('login.store'), [
            'email' => 'usuario@example.com',
            'password' => 'incorrecta',
        ])->assertSessionHasErrors([
            'email' => 'El correo electrónico o la contraseña no son correctos.',
        ]);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_without_system_access_cannot_log_in(): void
    {
        Http::fake([
            'https://accesos.test/api/auth/login' => Http::response([], 403),
        ]);

        $this->post(route('login.store'), [
            'email' => 'usuario@example.com',
            'password' => 'contraseña',
        ])->assertSessionHasErrors([
            'email' => 'Tu cuenta no está activa o no tiene acceso a SICAM.',
        ]);

        $this->assertGuest();
    }

    public function test_logout_revokes_remote_token_and_clears_local_session(): void
    {
        Http::fake([
            'https://accesos.test/api/auth/logout' => Http::response([
                'message' => 'Sesión cerrada correctamente.',
            ]),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['accesos.access_token' => 'token-de-prueba'])
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        Http::assertSent(fn ($request) => $request->url() === 'https://accesos.test/api/auth/logout'
            && $request->hasHeader('Authorization', 'Bearer token-de-prueba'));
    }

    public function test_guest_is_redirected_to_login_from_protected_pages(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('licencia.index'))->assertRedirect(route('login'));
        $this->get(route('software.sistemas'))->assertRedirect(route('login'));
    }
}
