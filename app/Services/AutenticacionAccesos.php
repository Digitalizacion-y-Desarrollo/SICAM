<?php

namespace App\Services;

use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class AutenticacionAccesos
{
    public function autenticar(string $email, string $password): array
    {
        $systemKey = (string) config('services.accesos.system_key');

        if ($systemKey === '') {
            throw ValidationException::withMessages([
                'email' => 'El acceso a SICAM no está configurado. Comunícate con el área de Sistemas.',
            ]);
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->connectTimeout(5)
                ->timeout(10)
                ->post($this->url('/api/auth/login'), [
                    'email' => $email,
                    'password' => $password,
                    'system_key' => $systemKey,
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('No fue posible conectar con Accesos durante el inicio de sesión.', [
                'exception' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'El servicio de acceso no está disponible. Intenta nuevamente en unos minutos.',
            ]);
        }

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'email' => $this->mensajeDeError($response->status()),
            ]);
        }

        $data = $response->json('data');

        if (! is_array($data) || ! is_string($data['access_token'] ?? null) || ! is_array($data['user'] ?? null)) {
            Log::warning('Accesos devolvió una respuesta de autenticación incompleta.', [
                'status' => $response->status(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'No fue posible confirmar la sesión. Intenta nuevamente.',
            ]);
        }

        return $data;
    }

    public function cerrarSesion(?string $token): void
    {
        if (! $token) {
            return;
        }

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->connectTimeout(3)
                ->timeout(5)
                ->post($this->url('/api/auth/logout'));

            if ($response->failed()) {
                Log::warning('Accesos no pudo revocar el token al cerrar sesión.', [
                    'status' => $response->status(),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('No fue posible conectar con Accesos durante el cierre de sesión.', [
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.accesos.base_url'), '/').$path;
    }

    private function mensajeDeError(int $status): string
    {
        return match ($status) {
            401 => 'El correo electrónico o la contraseña no son correctos.',
            403 => 'Tu cuenta no está activa o no tiene acceso a SICAM.',
            404 => 'SICAM no está registrado en el servicio de Accesos.',
            422 => 'Revisa el correo electrónico y la contraseña capturados.',
            429 => 'Se realizaron demasiados intentos. Espera un momento antes de volver a intentar.',
            default => 'El servicio de acceso no pudo procesar la solicitud. Intenta nuevamente.',
        };
    }
}
