<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AutenticacionAccesos;
use App\Support\PermisosAccesos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(PermisosAccesos::rutaInicial());
        }

        return view('auth.login');
    }

    public function store(Request $request, AutenticacionAccesos $accesos): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $data = $accesos->autenticar($credentials['email'], $credentials['password']);
        $externalUser = $data['user'];
        $email = mb_strtolower((string) ($externalUser['email'] ?? $credentials['email']));
        $name = collect([
            $externalUser['name'] ?? null,
            $externalUser['apellido_paterno'] ?? null,
            $externalUser['apellido_materno'] ?? null,
        ])->filter()->implode(' ');

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name !== '' ? $name : $email;
        $user->email_verified_at = now();

        if (! $user->exists) {
            $user->password = Str::random(64);
        }

        $user->save();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->put('accesos', [
            'access_token' => $data['access_token'],
            'token_type' => $data['token_type'] ?? 'Bearer',
            'user_id' => $externalUser['id'] ?? null,
            'departamento' => $externalUser['departamento'] ?? null,
            'system' => $data['system'] ?? null,
            'roles' => array_values($data['roles'] ?? []),
            'permissions' => array_values($data['permissions'] ?? []),
        ]);

        return redirect()->route(PermisosAccesos::rutaInicial());
    }

    public function destroy(Request $request, AutenticacionAccesos $accesos): RedirectResponse
    {
        $token = $request->session()->get('accesos.access_token');
        $accesos->cerrarSesion(is_string($token) ? $token : null);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
